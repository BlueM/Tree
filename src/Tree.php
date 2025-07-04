<?php

namespace BlueM;

use BlueM\Tree\Exception\InvalidParentException;
use BlueM\Tree\Exception\MissingNodeInvalidParentException;
use BlueM\Tree\Exception\SelfReferenceInvalidParentException;
use BlueM\Tree\Node;
use BlueM\Tree\Options;
use BlueM\Tree\Serializer\FlatTreeJsonSerializer;
use BlueM\Tree\Serializer\TreeJsonSerializerInterface;

/**
 * Builds and gives access to a tree of nodes which is constructed thru nodes' parent node ID references.
 *
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 3-Clause License
 */
class Tree implements \JsonSerializable, \Stringable
{
    /**
     * API version (will always be in sync with first digit of release version number).
     */
    public const API = 4;

    protected int|float|string|null $rootId = 0;

    protected string $idKey = 'id';

    protected string $parentKey = 'parent';

    /**
     * @var array<Node>
     */
    protected array $rootNodes;

    /**
     * @var array<int|string|float, Node>
     */
    protected array $nodes;

    protected ?TreeJsonSerializerInterface $jsonSerializer = null;

    /**
     * @var ?callable
     */
    protected $buildWarningCallback;

    /**
     * @param iterable<iterable<string, mixed>> $data The data for the tree (iterable)
     *
     * @throws InvalidParentException
     * @throws \InvalidArgumentException
     */
    public function __construct(iterable $data = [], Options $options = new Options())
    {
        $this->rootId = $options->rootId;
        $this->idKey = $options->idFieldName;
        $this->parentKey = $options->parentIdFieldName;

        if ($options->jsonSerializer) {
            $this->jsonSerializer = $options->jsonSerializer;
        }

        if ($options->buildWarningCallback) {
            $this->buildWarningCallback = $options->buildWarningCallback;
        } else {
            $this->buildWarningCallback = $this->buildWarningHandler(...);
        }

        $this->build($data);
    }

    /**
     * @param iterable<iterable<string, mixed>> $data
     *
     * @throws InvalidParentException
     */
    public function rebuildWithData(iterable $data): void
    {
        $this->build($data);
    }

    /**
     * Returns a flat, sorted array of all node objects in the tree.
     *
     * @return array<Node> Nodes, sorted as if the tree was hierarchical,
     *                     i.e.: the first level 1 item, then the children of
     *                     the first level 1 item (and their children), then
     *                     the second level 1 item and so on.
     */
    public function getNodes(): array
    {
        $nodes = [];
        foreach ($this->rootNodes as $rootNode) {
            foreach ($rootNode->getDescendantsAndSelf() as $node) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Returns a single node from the tree, identified by its ID.
     *
     * @throws \InvalidArgumentException
     */
    public function getNodeById(int|string|float $id): Node
    {
        if (!array_key_exists($id, $this->nodes)) {
            throw new \InvalidArgumentException("Invalid node primary key $id");
        }

        return $this->nodes[$id];
    }

    /**
     * Returns an array of all nodes in the root level.
     *
     * @return array<Node> Nodes in the correct order
     */
    public function getRootNodes(): array
    {
        return $this->rootNodes;
    }

    /**
     * Returns the first node for which a specific property's values of all ancestors
     * and the node are equal to the values in the given argument.
     *
     * Example: If nodes have property "name", and on the root level there is a node with name "A" which has a
     * child with name "B" which has a child which has node "C", you would get the latter one by invoking
     * getNodeByValuePath('name', ['A', 'B', 'C']). Comparison is case-sensitive and type-safe.
     *
     * @param array<mixed> $search
     */
    public function getNodeByValuePath(string $propertyName, array $search): ?Node
    {
        $findNested = function (array $nodes, array $tokens) use ($propertyName, &$findNested) {
            $token = array_shift($tokens);
            foreach ($nodes as $node) {
                $nodeName = $node->get($propertyName);
                if ($nodeName === $token) {
                    // Match
                    if (\count($tokens)) {
                        // Search next level
                        return $findNested($node->getChildren(), $tokens);
                    }

                    // We found the node we were looking for
                    return $node;
                }
            }

            return null;
        };

        return $findNested($this->getRootNodes(), $search);
    }

    /**
     * Core method for creating the tree.
     *
     * @param iterable<iterable<string, mixed>> $data
     *
     * @throws InvalidParentException
     */
    protected function build(iterable $data): void
    {
        $this->nodes = [];
        $this->rootNodes = [];
        $children = [];

        foreach ($data as $nodeData) {
            if ($nodeData instanceof \Iterator) {
                $nodeData = iterator_to_array($nodeData);
            }

            if (!array_key_exists($nodeData[$this->parentKey], $children)) {
                $children[] = [];
            }
            $children[$nodeData[$this->parentKey]][] = $nodeData[$this->idKey];

            $this->nodes[$nodeData[$this->idKey]] = $this->createNode($nodeData[$this->idKey], $nodeData);

            if ($this->rootId === $nodeData[$this->parentKey]) {
                $this->rootNodes[] = $this->nodes[$nodeData[$this->idKey]];
            }
        }

        foreach ($children as $pid => $childIds) {
            foreach ($childIds as $id) {
                if (isset($this->nodes[$pid])) {
                    if ((string) $this->nodes[$pid] === (string) $this->nodes[$id]) {
                        ($this->buildWarningCallback)(
                            new SelfReferenceInvalidParentException("Node with ID {$this->nodes[$id]} references its own ID as parent ID"),
                            $this,
                            $this->nodes[$id],
                            $pid,
                        );
                    } else {
                        $this->nodes[$pid]->addChild($this->nodes[$id]);
                    }
                } elseif (!in_array($this->nodes[$id], $this->rootNodes, true)) {
                    $pidStr = ('' === (string) $pid) ? 'empty parent ID' : "ID $pid";
                    ($this->buildWarningCallback)(
                        new MissingNodeInvalidParentException("Node with ID {$this->nodes[$id]} points to non-existent parent with $pidStr"),
                        $this,
                        $this->nodes[$id],
                        $pid,
                    );
                }
            }
        }
    }

    protected function buildWarningHandler(InvalidParentException $exception): void
    {
        throw $exception;
    }

    public function __toString(): string
    {
        $str = [];
        foreach ($this->getNodes() as $node) {
            $indent1st = str_repeat('  ', $node->getLevel() - 1).'- ';
            $indent = str_repeat('  ', ($node->getLevel() - 1) + 2);
            $str[] = $indent1st.str_replace("\n", "$indent\n  ", $node);
        }

        return implode("\n", $str);
    }

    /**
     * Sets the JSON serializer class to be used, if a different one than the default is required.
     *
     * By passing null, the serializer can be reset to the default one.
     */
    public function setJsonSerializer(?TreeJsonSerializerInterface $serializer = null): void
    {
        $this->jsonSerializer = $serializer;
    }

    public function jsonSerialize(): mixed
    {
        if (!$this->jsonSerializer) {
            $this->jsonSerializer = new FlatTreeJsonSerializer();
        }

        return $this->jsonSerializer->serialize($this);
    }

    /**
     * Creates and returns a node with the given properties.
     *
     * Can be overridden by subclasses to use a Node subclass for nodes.
     *
     * @param iterable<iterable<string, mixed>> $properties
     */
    protected function createNode(mixed $id, iterable $properties = []): Node
    {
        return new Node($id, $properties);
    }
}
