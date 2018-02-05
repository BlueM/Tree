[![Build Status](https://travis-ci.org/BlueM/Tree.png?branch=master)](https://travis-ci.org/BlueM/Tree)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/584d20e2-cd62-4aed-8d09-b87eb72005d2/mini.png)](https://insight.sensiolabs.com/projects/584d20e2-cd62-4aed-8d09-b87eb72005d2)

Overview
========
This library provides handling of data that is structured hierarchically using parent ID references. A typical example is a table in a relational database where each record’s “parent” field references the primary key of another record. Of course, usage is not limited to data originating from a database, but anything: you supply the data, and the library uses it, regardless of where the data came from and how it was processed.

It is important to know that the tree structure created by this package is *read-only*: you can’t use it to perform modifications of the tree nodes. If you need a library for that, you might want to take a look at [nicmart/tree](https://github.com/nicmart/Tree).

On the other hand, one nice thing is that it’s pretty fast. This does not only mean the code itself, but also that the constructor takes the input data in a format that is simple to create. For instance, to create a tree from database content, a single `SELECT` is sufficient, regardless of the depth of the tree and even for thousands of nodes.

Installation
==============
The preferred way to install Tree is through [Composer](https://getcomposer.org). For this, add `"bluem/tree": "~2.0"` to the requirements in your composer.json file. As this library uses [semantic versioning](http://semver.org), you will get fixes and feature additions when running composer update, but not changes which break the API.

Alternatively, you can clone the repository using git or download a tagged release.

Usage
========

Creating a tree
---------------
```php
// Create the tree with an array of arrays (or use an array of Iterators,
// Traversable of arrays or Traversable of Iterators):
$data = [
    ['id' => 1, 'parent' => 0, 'title' => 'Node 1'],
    ['id' => 2, 'parent' => 1, 'title' => 'Node 1.1'],
    ['id' => 3, 'parent' => 0, 'title' => 'Node 3'],
    ['id' => 4, 'parent' => 1, 'title' => 'Node 1.2'],
];
$tree = new BlueM\Tree($data);

// When using a data source that uses different keys for "id" and "parent",
// or if the root node ID is not 0 (in this example: -1), use the options
// array you can pass to the constructor:
$data = [
    ['nodeId' => 1, 'parentId' => -1, 'title' => 'Node 1'],
    ['nodeId' => 2, 'parentId' => 1, 'title' => 'Node 1.1'],
    ['nodeId' => 3, 'parentId' => -1, 'title' => 'Node 3'],
    ['nodeId' => 4, 'parentId' => 1, 'title' => 'Node 1.2'],
];
$tree = new BlueM\Tree(
    $data,
    ['rootId' => -1, 'id' => 'nodeId', 'parent' => 'parentId']
);
```

Updating the tree with new data
-------------------------------
```php
// Rebuild the tree from new data
$tree->rebuildWithData($newData);
```

Retrieving nodes
---------------
```php
// Get the top-level nodes
$rootNodes = $tree->getRootNodes();

// Get all nodes
$allNodes = $tree->getNodes();

// Get a single node by its unique identifier
$node = $tree->getNodeById(12345);
```

Getting a node’s parent, siblings, children, ancestors and descendants 
-------------------------------------------------
```php
// Get a node's parent node (will be null for the root node)
$parentNode = $node->getParent();

// Get a node's siblings as an array
$siblings = $node->getSiblings();

// Ditto, but include the node itself (identical to $node->parent->getChildren())
$siblings = $node->getSiblingsAndSelf();

// Get a node's preceding sibling (null, if there is no preceding sibling)
$precedingSibling = $node->getPrecedingSibling();

// Get a node's following sibling (null, if there is no following sibling)
$followingSibling = $node->getFollowingSibling();

// Does the node have children?
$bool = $node->hasChildren();

// Get the number of Children
$bool = $node->countChildren();

// Get a node's child nodes
$children = $node->getChildren();

// Get a node's ancestors (parent, grandparent, ...)
$ancestors = $node->getAncestors();

// Ditto, but include the node itself
$ancestorsPlusSelf = $node->getAncestorsAndSelf();

// Get a node's descendants (children, grandchildren, ...)
$descendants = $node->getDescendants();

// Ditto, but include the node itself
$descendantsPlusSelf = $node->getDescendantsAndSelf();
```

Accessing a node’s properties 
---------------------------
```php
// Get a node's ID
$id = $node->getId();

// Get the node's hierarchical level (1-based)
$level = $node->getLevel();

// Access node properties using get() overloaded getters or __get():
$value = $node->get('myproperty');
$value = $node->myproperty;
$value = $node->getMyProperty();

// Get the node's properties as an associative array
$array = $node->toArray();

// Get a string representation (which will be the node ID)
echo "$node";
```


Example:  Using it with a self-joined database table
------------------------------------------
```php
<?php

require 'vendor/autoload.php';

// Database setup (or use Doctrine or whatever ...)
$db = new PDO(...);

// SELECT the records in the sort order you need
$stm = $db->query('SELECT id, parent, title FROM tablename ORDER BY title');
$records = $stm->fetchAll(PDO::FETCH_ASSOC);

// Create the Tree instance
$tree = new BlueM\Tree($records);
...
...
```


Running Tests
==============
PHPUnit is configured as a dev dependency, so running tests is a matter of:

* `composer install`
* `./vendor/bin/phpunit`

If you want to see TestDox output or coverage data, you can comment in the commented lines in the `<log>` section of `phpunit.xml.dist`.


Version History
=================

2.0 (2018-02-04)
-----
* BC break: `getAncestors()` or `getAncestorsAndSelf()` no longer include the root node as last item of the returned array. *Solution:* add it yourself, if you need it. 
* BC break: Removed argument to `getAncestors()`. *Solution:* If you passed `true` as argument before, change this to `getAncestorsAndSelf()`.
* BC break: Removed argument to `getDescendants()`. *Solution:* If you passed `true` as argument before, change this to `getDescendantsAndSelf()`.
* BC break: Removed argument to `getSiblings()`. *Solution:* If you passed `true` as argument before, change this to `getSiblingsAndSelf()`.
* BC break: Moved `BlueM\Tree\InvalidParentException` to `BlueM\Tree\Exception\InvalidParentException`. *Solution:* Update namespace imports.
* New: Added method `Tree::rebuildWithData()` to rebuild the tree with new data.
* New: `Tree` and `Tree\Node` implement `JsonSerializable` and provide default implementations, which means that you can easily serialize the whole tree oder nodes to JSON.
* New: The tree data no longer has to be an `array`, but instead it must be an `iterable`, which means that you can either pass in an `array` or an object implementing the `Traversable` interface. Also, the data for a node no longer has to be an array, but can also be an object implementing the `Iterator` interface. These changes should make working with the library more flexible. 
* Internal change: Changed autoloading from PSR-0 to PSR-4, renamed sources’ directory from `lib/` to `src/` and tests’ directory from `test/` to `tests/`.
* Internal change: Code modernization, which now requires PHP >= 7.0


1.5.3 (2016-05-20)
-----
* Handle IDs of mixed type (strings and integers)

1.5.2 (2016-05-10)
-----
* Add info on JSON serialization in Readme. No code changes.

1.5.1 (2016-01-16)
----
* Remove superfluous 2nd argument to `build()` in constructor

1.5 (2015-01-14)
----
* Added `createNode()` method to Tree, which makes it possible to use instances of a Node subclass as nodes

1.4 (2015-01-07)
----
* Added `getSiblingsAndSelf()` method on `Node` class.
* The argument to `getSiblings()` is deprecated and will be removed in version 2

1.3 (2014-11-07)
----
* Added `getNodeByValuePath()` method on `Tree` class, which can be used to find a node deeply nested in the tree based on ancestors’ and the node’s values for an arbitrary property. (See method doc comment for example.)

1.2 (2014-10-14)
----
* Implemented `__isset()` and `__get()` on the `Node` class. This makes it possible to pass nodes to Twig (or other libraries that handle object properties similarly) and to access nodes’ properties intuitively.
* Improved case-insensitive handling of node properties

1.1 (2014-09-24)
-----
* Added `getDescendantsAndSelf()`
* Added `getAncestorsAndSelf()`
* Arguments to `getDescendants()` and `getAncestors()` are deprecated and will be removed with version 2
* Added a check to make sure that nodes don’t use their own ID as parent ID. This throws an exception which would not have been thrown before if this is the case. Hence, it might break backward compatibility, but only if the data data is inconsistent.

1.0 (2014-06-26)
---------------
* First public release


Author & License
=================
This code was written by Carsten Blüm (www.bluem.net) and licensed under the BSD 2-Clause license.
