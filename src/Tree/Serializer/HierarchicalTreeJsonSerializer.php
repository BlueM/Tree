<?php

namespace BlueM\Tree\Serializer;

use BlueM\Tree;

/**
 * Serializer which creates a hierarchical, depth-first sorted representation of the tree nodes.
 *
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 3-Clause License
 */
class HierarchicalTreeJsonSerializer implements TreeJsonSerializerInterface
{
    /**
     * @var string
     */
    private $childNodesArrayKey;

    public function __construct(string $childNodesArrayKey = 'children')
    {
        $this->childNodesArrayKey = $childNodesArrayKey;
    }

    /**
     * @return array Multi-dimensional array of node data arrays, where a node's children are
     *               included in array key "children" of a node
     */
    public function serialize(Tree $tree): array
    {
        $data = [];
        foreach ($tree->getRootNodes() as $node) {
            $data[] = $this->serializeNode($node);
        }

        return $data;
    }

    private function serializeNode(Tree\Node $node): array
    {
        $nodeData = $node->toArray();
        if ($node->hasChildren()) {
            $nodeData[$this->childNodesArrayKey] = [];
            foreach ($node->getChildren() as $child) {
                $nodeData[$this->childNodesArrayKey][] = $this->serializeNode($child);
            }
        }

        return $nodeData;
    }
}
