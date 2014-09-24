[![Build Status](https://travis-ci.org/BlueM/Tree.png?branch=master)](https://travis-ci.org/BlueM/Tree)

Tree Overview
=========================

What is it?
--------------
Tree and Tree\Node are PHP classes for handling data that is structured hierarchically using parent ID references. A typical example is a table in a relational database where each record’s “parent” field references the primary key of another record. Of course, Tree cannot only use data originating from a database, but anything: you supply the data, and Tree uses it, regardless of where the data came from and how it was processed.

Installation
-------------
The preferred way to install Tree is through [Composer](https://getcomposer.org). For this, add `"bluem/tree": "~1.0"` to the requirements in your composer.json file. As this library uses [semantic versioning](http://semver.org), you will get fixes and feature additions when running composer update, but not changes which break the API.

Alternatively, you can clone the repository using git or download a tagged release.

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

    // Get a node's property (other than "id" or "parent")
    $parent = $node->get('propertyname');

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


Version History
=================

1.1
-----
* Added `getDescendantsAndSelf()`
* Added `getAncestorsAndSelf()`
* Arguments to `getDescendants()` and `getAncestors()` are deprecated and will be removed with version 2
* Added a check to make sure that nodes don’t use their own ID as parent ID. This throws an exception which would not have been thrown before if this is the case. Hence, it might break backward compatibility, but only if the data data is inconsistent.


Author & License
=================
This code was written by Carsten Blüm (www.bluem.net) and licensed under the BSD2 license.
