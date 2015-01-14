<?php

/**
 * Copyright (c) 2011, Carsten Blüm <carsten@bluem.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this
 * list of conditions and the following disclaimer in the documentation and/or
 * other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace BlueM\Tree;

use BlueM\Tree;

/**
 * Class representing a node in a Tree
 *
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause License
 */
class Node
{
    /**
     * Indexed array of node properties. Must at least contain key
     * "id" and "parent"; any other keys may be added as needed
     *
     * @var array Associative array
     */
    protected $properties = array('id' => null, 'parent' => 0);

    /**
     * Reference to the parent node, in case of the root object: null
     *
     * @var Node
     */
    protected $parent = null;

    /**
     * Indexed array of child nodes in correct order
     *
     * @var array
     */
    protected $children = array();

    /**
     * @param array $properties Associative array of node properties
     */
    public function __construct(array $properties)
    {
        $this->properties = array_change_key_case($properties, CASE_LOWER);
    }

    /**
     * Adds the given node to this node's children
     *
     * @param Node $child
     */
    public function addChild(Node $child)
    {
        $this->children[]            = $child;
        $child->parent               = $this;
        $child->properties['parent'] = $this->getId();
    }

    /**
     * Returns previous node in the same level, or NULL if there's no previous node
     *
     * @return Node|null
     */
    public function getPrecedingSibling()
    {
        return $this->getSibling(-1);
    }

    /**
     * Returns following node in the same level, or NULL if there's no following node
     *
     * @return Node|null
     */
    public function getFollowingSibling()
    {
        return $this->getSibling(1);
    }

    /**
     * Returns the sibling with the given offset from this node,
     * or NULL if there is no such sibling
     *
     * @param int $offset If 1, the next node is returned, if -1, then
     *                    the previous one. Can be called with arbitrary
     *                    values, too, if desired.
     *
     * @return Node|null
     */
    private function getSibling($offset)
    {
        $siblingsAndSelf = $this->parent->getChildren();
        $pos             = array_search($this, $siblingsAndSelf);
        if (isset($siblingsAndSelf[$pos + $offset])) {
            return $siblingsAndSelf[$pos + $offset]; // Next / prev. node
        }
        return null;
    }

    /**
     * Returns siblings of the node, optionally including the node itself.
     *
     * @param bool $includeSelf If true, the node itself will be included in the resulting
     *                          array. In either case, the sort order will be correct.
     *                          This argument is deprecated and will be removed in v2.0
     *
     * Note: The argument is deprecated and will be removed in version 2; please
     * use getSiblingsAndSelf().
     *
     * @return Node[]
     */
    public function getSiblings($includeSelf = false)
    {
        $siblings = array();
        foreach ($this->parent->getChildren() as $child) {
            if ($includeSelf || $child->getId() != $this->getId()) {
                $siblings[] = $child;
            }
        }
        return $siblings;
    }

    /**
     * Returns siblings of the node, optionally including the node itself.
     *
     * @return Node[]
     */
    public function getSiblingsAndSelf()
    {
        return $this->getSiblings(true);
    }

    /**
     * Returns all direct children of this node
     *
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns the parent object or null, if it has no parent
     *
     * @return Node|null Either parent node or, when called on root node, NULL
     */
    public function getParent()
    {
        if (null === $this->parent) {
            return null;
        }
        return $this->parent;
    }

    /**
     * Returns a node's ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->properties['id'];
    }

    /**
     * Returns a single node property by its name.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function get($name)
    {
        $lowerName = strtolower($name);
        if (isset($this->properties[$lowerName])) {
            return $this->properties[$lowerName];
        }
        throw new \InvalidArgumentException(
            "Undefined property: $name (Node ID: ".$this->properties['id'].')'
        );
    }

    /**
     * @param string $name
     * @param mixed  $args
     *
     * @throws \BadFunctionCallException
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        $lowerName = strtolower($name);
        if ('get' === substr($lowerName, 0, 3)) {
            $property = substr($lowerName, 3);
            if (array_key_exists($property, $this->properties)) {
                return $this->properties[$property];
            }
        }
        throw new \BadFunctionCallException("Invalid method $name() called");
    }

    /**
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ('parent' == $name || 'children' == $name) {
            return $this->$name;
        }
        $lowerName = strtolower($name);
        if (array_key_exists($lowerName, $this->properties)) {
            return $this->properties[$lowerName];
        }
        throw new \RuntimeException(
            "Undefined property: $name (Node ID: ".$this->properties['id'].')'
        );
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return 'parent' == $name ||
               'children' == $name ||
               in_array(strtolower($name), array_keys($this->properties));
    }

    /**
     * Returns the level of this node in the tree
     *
     * @return int Tree level (1 = top level)
     */
    public function getLevel()
    {
        if ($this->parent === null) {
            return 0;
        }
        return $this->parent->getLevel() + 1;
    }

    /**
     * Returns whether or not this node has at least one child node
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children) ? true : false;
    }

    /**
     * Returns number of children this node has
     *
     * @return int
     */
    public function countChildren()
    {
        return count($this->children);
    }

    /**
     * Returns any node below (children, grandchildren, ...) this node.
     *
     * The order is as follows: A, A1, A2, ..., B, B1, B2, ..., where A and B are
     * 1st-level items in correct order, A1/A2 are children of A in correct order,
     * and B1/B2 are children of B in correct order. If the node itself is to be
     * included, it will be the very first item in the array.
     *
     * Note: The argument is deprecated and will be removed in version 2; please
     * use getDescendantsAndSelf().
     *
     * @param bool $includeSelf [optional] Include the node itself? Default: false.
     *
     * @return Node[]
     */
    public function getDescendants($includeSelf = false)
    {
        $descendants = $includeSelf ? array($this) : array();
        foreach ($this->children as $childnode) {
            $descendants[] = $childnode;
            if ($childnode->hasChildren()) {
                $descendants = array_merge($descendants, $childnode->getDescendants());
            }
        }
        return $descendants;
    }

    /**
     * Returns an array containing this node and all nodes below (children,
     * grandchildren, ...) it.
     *
     * For order of nodes, see comments on getDescendants()
     *
     * @return Node[]
     */
    public function getDescendantsAndSelf()
    {
        return $this->getDescendants(true);
    }

    /**
     * Returns any node above (parent, grandparent, ...) this node
     *
     * The array returned from this method will include the root node. If you
     * do not want the root node, you should do an array_pop() on the array.
     *
     * Note: The argument is deprecated and will be removed in version 2; please use
     * getAncestorsAndSelf() instead. Also, in version 2 the root node will not be
     * included, as this is hardly ever necessary; you can prepare your code already
     * now by using constant Tree::API to check whether removing the root node is
     * necessary.
     *
     * @param bool $includeSelf [optional] Whether to include the node itself.
     *
     * @return Node[] Indexed array of nodes, sorted from the nearest
     *                one (or self) to the most remote one
     */
    public function getAncestors($includeSelf = false)
    {
        $ancestors = $includeSelf ? array($this) : array();

        if (null === $this->parent) {
            return $ancestors;
        }

        return array_merge($ancestors, $this->parent->getAncestors(true));
    }

    /**
     * Returns an array containing this node and all nodes above (parent, grandparent,
     * ...) it.
     *
     * Note: The array returned from this method will include the root node. If you
     * do not want the root node, you should do an array_pop() on the array.
     *
     * @return Node[] Indexed, sorted array of nodes: self, parent, grandparent, ...
     */
    public function getAncestorsAndSelf()
    {
        return $this->getAncestors(true);
    }

    /**
     * Returns the node's properties as an array
     *
     * @return array Associative array
     */
    public function toArray()
    {
        return $this->properties;
    }

    /**
     * Returns a textual representation of this node
     *
     * @return string The node's ID
     */
    public function __toString()
    {
        return (string)$this->properties['id'];
    }
}
