[![Build Status](https://travis-ci.org/BlueM/Tree.png?branch=master)](https://travis-ci.org/BlueM/Tree)

Tree Overview
=========================

What is it?
--------------
Tree and Tree\Node are PHP classes for handling data that is structured hierarchically using parent ID references. A typical example is a table in a relational database where each record’s “parent” field references the primary key of another record. Of course, Tree cannot only use data originating from a database, but anything: you supply the data, and Tree uses it, regardless of where the data came from and how it was processed.


Usage
-------

    $tree = new BlueM\Tree($data);

    // Get the top-level nodes
    $rootNodes = $tree->getRootNodes();

    // Get all nodes
    $allNodes = $tree->getNodes();

    // Get a single node by its unique identifier
    $node = $tree->getNodeById(12345);

    // Get a node's ID
    $parent = $node->getId();

    // Get the node's hierarchical level (1-based)
    $level = $node->getLevel();

    // Get a node's preceding sibling
    $precedingSibling = $node->getPrecedingSibling();

    // Get a node's following sibling
    $followingSibling = $node->getFollowingSibling();

    // Get a node's parent node
    $parent = $node->getParent();

    // Get a node's ancestors (parent, grandparent, ...)
    $ancestors = $node->getAncestors();

    // Ditto, but include the node itself
    $ancestorsPlusSelf = $node->getAncestors(true);

    // Get a node's child nodes
    $children = $node->getChildren();

    // Does the node have children?
    $bool = $node->hasChildren();

    // Get the number of Children
    $bool = $node->countChildren();

    // Get a node's descendants (children, grandchildren, ...)
    $descendants = $node->getDescendants();

    // Ditto, but include the node itself
    $descendantsPlusSelf = $node->getDescendants(true);

    // Get the node's properties as an associative array
    $array = $node->toArray();

    // Get a string representation (which will be the node ID)
    echo "$node";


Example
=======

Using it with a self-joined database table
------------------------------------------

    <?php

    require '/path/to/vendor/autoload.php';

    $db = new PDO(...); // Set up your database connection
    $stm = $db->query('SELECT id, parent, title FROM tablename ORDER BY title');
    $records = $stm->fetchAll(PDO::FETCH_ASSOC);

    $tree = new BlueM\Tree($records);
    ...
    ...
