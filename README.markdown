[![Build Status](https://travis-ci.org/BlueM/Tree.png?branch=master)](https://travis-ci.org/BlueM/Tree)
[![HHVM Status](http://hhvm.h4cc.de/badge/bluem/tree.svg)](http://hhvm.h4cc.de/package/bluem/tree)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/584d20e2-cd62-4aed-8d09-b87eb72005d2/mini.png)](https://insight.sensiolabs.com/projects/584d20e2-cd62-4aed-8d09-b87eb72005d2)

Tree Overview
=========================

What is it?
--------------
`Tree` and `Tree\Node` are PHP classes for handling data that is structured hierarchically using parent ID references. A typical example is a table in a relational database where each record’s “parent” field references the primary key of another record. Of course, Tree cannot only use data originating from a database, but anything: you supply the data, and Tree uses it, regardless of where the data came from and how it was processed.

It is important to know that the tree structure created by this package is *read-only*: you can’t use it to perform modifications of the tree nodes. If you need a library for that, you might want to take a look at [nicmart/tree](https://github.com/nicmart/Tree).

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

Advanced topics
===============

Controlling JSON serialization
------------------------------
In case you want to serialize a tree of nodes managed by this class to JSON using `json_encode()`, you will probably find that you don’t get the data you expect or even are confronted with a recursion-related warning.

The code in this package cannot anticipate in what way you will use it and what you would like the JSON representation to contain. Luckily, you can fully control the generated JSON. Starting with PHP 5.4, there is the `JsonSerializable` interface which includes a single method `jsonSerialize()`, which may return *anything*.

An example implementation which returns the node’s properties plus children as an array might look like this:

```php

class YourNodeClass extends \BlueM\Tree\Node implements \JsonSerializable
{
    /**
     * @return array Or whatever datatype you like
     */
    public function jsonSerialize()
    {
        return array_merge($this->properties, ['children' => $this->getChildren()]);
    }
}
```

The result is recursive, which is probably too much when using `getNodes()` to get all nodes, but can be handy when invoking `getRootNodes()`. When using `getNodes()`, you might therefore – again: depending on your needs – just skip including the children or include only children IDs.

To be able to use a custom node class you must extend `BlueM\Tree` and overwrite the `createNode()` method (requires version 1.5 or later), but that’s *really* easy to do.


Version History
=================

1.5.3
-----
* Handle IDs of mixed type (strings and integers)

1.5.2
-----
* Add info on JSON serialization in Readme. No code changes.

1.5.1
----
* Remove superfluous 2nd argument to `build()` in constructor

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
