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

namespace BlueM;

use BlueM\Tree\InvalidParentException;
use BlueM\Tree\Node;

/**
 * Class for dealing with a tree structure that is constructed by referencing parent IDs
 *
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License
 */
class Tree
{
    /**
     * API version
     *
     * This number will always be in sync with the first digit of the
     * release version number.
     *
     * @var int
     */
    const API = 1;

    /**
     * @var array
     */
    protected $nodes = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param array $data    The data for the tree (array of associative arrays)
     * @param array $options [optional] Currently, the only supported key is "rootId"
     *                       (ID of the root node)
     */
    public function __construct(array $data, array $options = array())
    {
        $this->options = array_change_key_case($options, CASE_LOWER);
        if (!isset($this->options['rootid'])) {
            $this->options['rootid'] = 0;
        }

        $this->build($data, $options);
    }

    /**
     * Returns a flat, sorted array of all node objects in the tree.
     *
     * @return Node[] Nodes, sorted as if the tree was hierarchical,
     *                i.e.: the first level 1 item, then the children of
     *                the first level 1 item (and their children), then
     *                the second level 1 item and so on.
     */
    public function getNodes()
    {
        $nodes = array();
        foreach ($this->nodes[$this->options['rootid']]->getDescendants() as $subnode) {
            $nodes[] = $subnode;
        }
        return $nodes;
    }

    /**
     * Returns a single node from the tree, identified by its ID.
     *
     * @param int $id Node ID
     *
     * @throws \InvalidArgumentException
     *
     * @return Node
     */
    public function getNodeById($id)
    {
        if (empty($this->nodes[$id])) {
            throw new \InvalidArgumentException("Invalid node primary key $id");
        }
        return $this->nodes[$id];
    }

    /**
     * Returns an array of all nodes in the root level
     *
     * @return Node[] Nodes in the correct order
     */
    public function getRootNodes()
    {
        return $this->nodes[$this->options['rootid']]->getChildren();
    }

    /**
     * Returns the first node for which a specific property's values of all ancestors
     * and the node are equal to the values in the given argument.
     *
     * Example: If nodes have property "name", and on the root level there is a node with
     * name "A" which has a child with name "B" which has a child which has node "C", you
     * would get the latter one by invoking getNodeByValuePath('name', ['A', 'B', 'C']).
     * Comparison is case-sensitive and type-safe.
     *
     * @param string $name
     * @param array  $search
     *
     * @return Node|null
     */
    public function getNodeByValuePath($name, array $search)
    {
        $findNested = function (array $nodes, array $tokens) use ($name, &$findNested) {
            $token = array_shift($tokens);
            foreach ($nodes as $node) {
                $nodeName = $node->get($name);
                if ($nodeName === $token) {
                    // Match
                    if (count($tokens)) {
                        // Search next level
                        return $findNested($node->getChildren(), $tokens);
                    } else {
                        // We found the node we were looking for
                        return $node;
                    }
                }
            }
            return null;
        };

        return $findNested($this->getRootNodes(), $search);
    }

    /**
     * Core method for creating the tree
     *
     * @param array $data The data from which to generate the tree
     *
     * @throws InvalidParentException
     */
    private function build(array $data)
    {
        $children = array();

        // Create the root node
        $this->nodes[$this->options['rootid']] = $this->createNode(
            array(
                'id'     => $this->options['rootid'],
                'parent' => null,
            )
        );

        foreach ($data as $row) {
            $this->nodes[$row['id']] = $this->createNode($row);
            if (empty($children[$row['parent']])) {
                $children[$row['parent']] = array($row['id']);
            } else {
                $children[$row['parent']][] = $row['id'];
            }
        }

        foreach ($children as $pid => $childids) {
            foreach ($childids as $id) {
                if ($pid == $id) {
                    throw new InvalidParentException(
                        "Node with ID $id references its own ID as parent ID"
                    );
                }
                if (isset($this->nodes[$pid])) {
                    $this->nodes[$pid]->addChild($this->nodes[$id]);
                } else {
                    throw new InvalidParentException(
                        "Node with ID $id points to non-existent parent with ID $pid"
                    );
                }
            }
        }
    }

    /**
     * Returns a textual representation of the tree
     *
     * @return string
     */
    public function __toString()
    {
        $str = array();
        foreach ($this->getNodes() as $node) {
            $indent1st = str_repeat('  ', $node->getLevel() - 1).'- ';
            $indent    = str_repeat('  ', ($node->getLevel() - 1) + 2);
            $node      = (string) $node;
            $str[]     = "$indent1st" . str_replace("\n", "$indent\n  ", $node);
        }
        return join("\n", $str);
    }

    /**
     * Creates and returns a node with the given properties
     *
     * Can be overridden by subclasses to use a Node subclass for nodes.
     *
     * @param array $properties
     *
     * @return Node
     */
    protected function createNode(array $properties)
    {
        return new Node($properties);
    }
}
