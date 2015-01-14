[![Build Status](https://travis-ci.org/BlueM/Tree.png?branch=master)](https://travis-ci.org/BlueM/Tree)
[![HHVM Status](http://hhvm.h4cc.de/badge/bluem/tree.svg)](http://hhvm.h4cc.de/package/bluem/tree)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/584d20e2-cd62-4aed-8d09-b87eb72005d2/mini.png)](https://insight.sensiolabs.com/projects/584d20e2-cd62-4aed-8d09-b87eb72005d2)

Tree Overview
=========================

What is it?
--------------
`Tree` and `Tree\Node` are PHP classes for handling data that is structured hierarchically using parent ID references. A typical example is a table in a relational database where each record’s “parent” field references the primary key of another record. Of course, Tree cannot only use data originating from a database, but anything: you supply the data, and Tree uses it, regardless of where the data came from and how it was processed.

It is important to know that the tree structure created by this package is read-only: you can’t use it to perform modifications (add, update, delete, reorder) of the tree nodes.

On the other hand, one nice thing is that it’s pretty fast. This does not only mean the code itself, but also that the constructor takes the input data in a format that is simple to create. For instance, to create a tree from database content, a single `SELECT` is sufficient, regardless of the depth of the tree and even for thousands of nodes.

Installation
-------------
The preferred way to install Tree is through [Composer](https://getcomposer.org). For this, add `"bluem/tree": "~1.0"` to the requirements in your composer.json file. As this library uses [semantic versioning](http://semver.org), you will get fixes and feature additions when running composer update, but not changes which break the API.

Alternatively, you can clone the repository using git or download a tagged release.

Usage
-------

```php
$tree = new BlueM\Tree($data);

// Get the top-level nodes
$rootNodes = $tree->getRootNodes();

// Get all nodes
$allNodes = $tree->getNodes();

// Get a single node by its unique identifier
$node = $tree->getNodeById(12345);

// Get a node's ID
$id = $node->getId();

// Get a node's parent node (will be null for the root node)
$parentNode = $node->getParent();

// Get the node's hierarchical level (1-based)
$level = $node->getLevel();

// Get a node's siblings as an array
$siblings = $node->getSiblings();

// Ditto, but include the node itself (identical to $node->parent->getChildren())
$siblings = $node->getSiblingsAndSelf();

// Get a node's preceding sibling (null, if there is no preceding sibling)
$precedingSibling = $node->getPrecedingSibling();

// Get a node's following sibling (null, if there is no following sibling)
$followingSibling = $node->getFollowingSibling();

// Get a node's ancestors (parent, grandparent, ...)
$ancestors = $node->getAncestors();

// Ditto, but include the node itself
$ancestorsPlusSelf = $node->getAncestorsAndSelf();

// Get a node's child nodes
$children = $node->getChildren();

// Does the node have children?
$bool = $node->hasChildren();

// Get the number of Children
$bool = $node->countChildren();

// Get a node's descendants (children, grandchildren, ...)
$descendants = $node->getDescendants();

// Ditto, but include the node itself
$descendantsPlusSelf = $node->getDescendantsAndSelf();

// Access node properties using get() overloaded getters or __get():
$value = $node->get('myproperty');
$value = $node->myproperty;
$value = $node->getMyProperty();

// Get the node's properties as an associative array
$array = $node->toArray();

// Get a string representation (which will be the node ID)
echo "$node";
```


Example
=======

Using it with a self-joined database table
------------------------------------------

```php
<?php

require '/path/to/vendor/autoload.php';

$db = new PDO(...); // Set up your database connection
$stm = $db->query('SELECT id, parent, title FROM tablename ORDER BY title');
$records = $stm->fetchAll(PDO::FETCH_ASSOC);

$tree = new BlueM\Tree($records);
...
...
```


Version History
=================

1.5
----
* Added `createNode()` method to Tree, which makes it possible to use instances of a Node subclass as nodes

1.4
----
* Added `getSiblingsAndSelf()` method on `Node` class.
* The argument to `getSiblings()` is deprecated and will be removed in version 2

1.3
----
* Added `getNodeByValuePath()` method on `Tree` class, which can be used to find a node deeply nested in the tree based on ancestors’ and the node’s values for an arbitrary property. (See method doc comment for example.)

1.2
----
* Implemented `__isset()` and `__get()` on the `Node` class. This makes it possible to pass nodes to Twig (or other libraries that handle object properties similarly) and to access nodes’ properties intuitively.
* Improved case-insensitive handling of node properties

1.1
-----
* Added `getDescendantsAndSelf()`
* Added `getAncestorsAndSelf()`
* Arguments to `getDescendants()` and `getAncestors()` are deprecated and will be removed with version 2
* Added a check to make sure that nodes don’t use their own ID as parent ID. This throws an exception which would not have been thrown before if this is the case. Hence, it might break backward compatibility, but only if the data data is inconsistent.


Author & License
=================
This code was written by Carsten Blüm (www.bluem.net) and licensed under the BSD 2-Clause license.
