<?php

namespace BlueM\Tree;

/**
 * Represents a node in a tree of nodes.
 *
 * @author  Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 3-Clause License
 */
class Node implements \Stringable, \JsonSerializable
{
    /**
     * Associative array, at least having keys "id" and "parent". Other keys may be added as needed.
     *
     * @var array<string, mixed>
     */
    protected array $properties = [];

    /**
     * Reference to the parent node, in case of the root object: null.
     */
    protected ?Node $parent = null;

    /**
     * Indexed array of child nodes in correct order.
     *
     * @var array<self>
     */
    protected array $children = [];

    /**
     * @param iterable<string, mixed> $properties
     */
    public function __construct(mixed $id, mixed $parent, iterable $properties = [])
    {
        $this->properties = (array) $properties;
        $this->properties['id'] = $id;
        $this->properties['parent'] = $parent;
    }

    /**
     * Adds the given node to this node's children.
     */
    public function addChild(Node $child): void
    {
        $this->children[] = $child;
        $child->parent = $this;
        $child->properties['parent'] = $this->getId();
    }

    /**
     * Returns previous node in the same level, or NULL if there's no previous node.
     */
    public function getPrecedingSibling(): ?Node
    {
        return $this->getSibling(-1);
    }

    /**
     * Returns following node in the same level, or NULL if there's no following node.
     */
    public function getFollowingSibling(): ?Node
    {
        return $this->getSibling(1);
    }

    /**
     * Returns the sibling with the given offset from this node, or NULL if there is no such sibling.
     */
    private function getSibling(int $offset): ?Node
    {
        $siblingsAndSelf = $this->parent->getChildren();
        $pos = array_search($this, $siblingsAndSelf, true);

        return $siblingsAndSelf[$pos + $offset] ?? null;
    }

    /**
     * Returns siblings of the node.
     *
     * @return array<self>
     */
    public function getSiblings(): array
    {
        return $this->getSiblingsGeneric(false);
    }

    /**
     * Returns siblings of the node and the node itself.
     *
     * @return array<self>
     */
    public function getSiblingsAndSelf(): array
    {
        return $this->getSiblingsGeneric(true);
    }

    /**
     * @return array<self>
     */
    protected function getSiblingsGeneric(bool $includeSelf): array
    {
        $siblings = [];
        foreach ($this->parent->getChildren() as $child) {
            if ($includeSelf || (string) $child->getId() !== (string) $this->getId()) {
                $siblings[] = $child;
            }
        }

        return $siblings;
    }

    /**
     * Returns all direct children of this node.
     *
     * @return array<self>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Returns the parent node or null, if the node is the root node.
     */
    public function getParent(): ?Node
    {
        return $this->parent ?? null;
    }

    public function getId(): mixed
    {
        return $this->properties['id'];
    }

    /**
     * Returns a single node property by its name.
     *
     * @throws \InvalidArgumentException
     */
    public function get(string $property): mixed
    {
        if ('parent' === $property || 'children' === $property) {
            return $this->$property;
        }

        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }

        throw new \InvalidArgumentException(
            "Undefined property: $property (Node ID: ".$this->properties['id'].')'
        );
    }

    /**
     * @throws \BadMethodCallException
     */
    public function __call(string $name, mixed $args): mixed
    {
        if (str_starts_with(strtolower($name), 'get')) {
            $property = substr($name, 3);
            if (array_key_exists($property, $this->properties)) {
                return $this->properties[$property];
            }
            $property = strtolower($property);
            $lcProperties = array_change_key_case($this->properties);
            if (array_key_exists($property, $lcProperties)) {
                return $lcProperties[$property];
            }
        }

        throw new \BadMethodCallException("Invalid method $name() called");
    }

    /**
     * @throws \RuntimeException
     */
    public function __get(string $name): mixed
    {
        if ('parent' === $name || 'children' === $name) {
            return $this->$name;
        }

        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        throw new \RuntimeException(
            "Undefined property: $name (Node ID: ".$this->properties['id'].')'
        );
    }

    public function __isset(string $property): bool
    {
        return 'parent' === $property
               || 'children' === $property
               || array_key_exists($property, $this->properties);
    }

    /**
     * Returns the level of this node in the tree.
     *
     * @return int Tree level (1 = top level)
     */
    public function getLevel(): int
    {
        if (null === $this->parent) {
            return 0;
        }

        return $this->parent->getLevel() + 1;
    }

    /**
     * Returns whether or not this node has at least one child node.
     */
    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    /**
     * Returns number of children this node has.
     */
    public function countChildren(): int
    {
        return \count($this->children);
    }

    /**
     * Returns any node below (children, grandchildren, ...) this node.
     *
     * The order is as follows: A, A1, A2, ..., B, B1, B2, ..., where A and B are
     * 1st-level items in correct order, A1/A2 are children of A in correct order,
     * and B1/B2 are children of B in correct order. If the node itself is to be
     * included, it will be the very first item in the array.
     *
     * @return array<self>
     */
    public function getDescendants(): array
    {
        return $this->getDescendantsGeneric(false);
    }

    /**
     * Returns an array containing this node and all nodes below (children,
     * grandchildren, ...) it.
     *
     * For order of nodes, see comments on getDescendants()
     *
     * @return array<self>
     */
    public function getDescendantsAndSelf(): array
    {
        return $this->getDescendantsGeneric(true);
    }

    /**
     * @return array<self>
     */
    protected function getDescendantsGeneric(bool $includeSelf): array
    {
        $descendants = $includeSelf ? [$this] : [];
        foreach ($this->children as $childnode) {
            $descendants[] = $childnode;
            if ($childnode->hasChildren()) {
                $descendants = [...$descendants, ...$childnode->getDescendants()];
            }
        }

        return $descendants;
    }

    /**
     * Returns any node above (parent, grandparent, ...) this node.
     *
     * The array returned from this method will include the root node. If you
     * do not want the root node, you should do an array_pop() on the array.
     *
     * @return array<self> Sorted from the nearest one (or self) to the most remote one
     */
    public function getAncestors(): array
    {
        return $this->getAncestorsGeneric(false);
    }

    /**
     * Returns an array containing this node and all nodes above it (parent, grandparent, ...).
     *
     * Note: The array returned from this method will include the root node. If you
     * do not want the root node, you should do an array_pop() on the array.
     *
     * @return array<self> Sorted array of nodes: self, parent, grandparent, ...
     */
    public function getAncestorsAndSelf(): array
    {
        return $this->getAncestorsGeneric(true);
    }

    /**
     * @return array<self>
     */
    protected function getAncestorsGeneric(bool $includeSelf): array
    {
        if (null === $this->parent) {
            return [];
        }

        if ($includeSelf) {
            return [$this, ...$this->parent->getAncestorsGeneric(true)];
        }

        return $this->parent->getAncestorsGeneric(true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->properties;
    }

    /**
     * Returns a textual representation of this node (string representation of node ID).
     */
    public function __toString(): string
    {
        return (string) $this->properties['id'];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
