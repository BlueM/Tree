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


/**
 * Class for dealing with a tree structure that is constructed by referencing parent IDs
 *
 * @author Carsten Bluem <carsten@bluem.net>
 */
class Tree
{
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
     * @return Tree\Node[] Nodes, sorted as if the tree was hierarchical,
     *                     i.e.: the first level 1 item, then the children of
     *                     the first level 1 item (and their children), then
     *                     the second level 1 item and so on.
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
     * @return Tree\Node
     * @throws \InvalidArgumentException
     */
    public function getNodeById($id)
    {
        if (isset($this->nodes[$id])) {
            return $this->nodes[$id];
        }
        throw new \InvalidArgumentException("Invalid node primary key $id");
    }

    /**
     * Returns an array of all nodes in the root level
     *
     * @return Tree\Node[] Nodes in the correct order
     */
    public function getRootNodes()
    {
        return $this->nodes[$this->options['rootid']]->getChildren();
    }

    /**
     * Core method for creating the tree. Emits an E_USER_WARNING for each
     * child node that references a non-existing parent node.
     *
     * @param array $data The data from which to generate the tree
     */
    private function build(array $data)
    {
        $children = array();

        // Create the root node
        $this->nodes[$this->options['rootid']] = new Tree\Node(
            array(
                'id'     => $this->options['rootid'],
                'title'  => 'ROOT',
                'parent' => null,
            ),
            $this
        );

        foreach ($data as $row) {
            $this->nodes[$row['id']] = new Tree\Node($row, $this);
            if (empty($children[$row['parent']])) {
                $children[$row['parent']] = array($row['id']);
            } else {
                $children[$row['parent']][] = $row['id'];
            }
        }

        foreach ($children as $pid => $childids) {
            foreach ($childids as $id) {
                if (isset($this->nodes[$pid])) {
                    $this->nodes[$pid]->addChild($this->nodes[$id]);
                } else {
                    user_error(
                        "Node with ID $id points to non-existent parent with ID $pid",
                        E_USER_WARNING
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
            $indent1st = str_repeat('  ', $node->getLevel() - 1) . '- ';
            $indent    = str_repeat('  ', ($node->getLevel() - 1) + 2);
            $node      = (string)$node;
            $str[]     = "$indent1st" . str_replace("\n", "$indent\n  ", $node);
        }
        return join("\n", $str);
    }
}
