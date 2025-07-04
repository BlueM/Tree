![Build status](https://github.com/BlueM/Tree/actions/workflows/php.yml/badge.svg)

Overview
========
This library provides handling of data that is structured hierarchically using parent ID references. A typical example is a table in a relational database where each record’s “parent” field references the primary key of another record. Of course, usage is not limited to data originating from a database, but anything: you supply the data, and the library uses it, regardless of where the data came from and how it was processed.

It is important to know that the tree structure created by this package is *read-only*: you can’t use it to perform modifications of the tree nodes.

On the other hand, one nice thing is that it’s pretty fast. This does not only mean the code itself, but also that the constructor takes the input data in a format that is simple to create. For instance, to create a tree from database content, a single `SELECT` is sufficient, regardless of the depth of the tree and even for thousands of nodes.

Installation
==============
The recommended way to install this library is through [Composer](https://getcomposer.org): `composer require bluem/tree`. As the library uses [semantic versioning](http://semver.org), you can define version a constraint in `composer.json` which allows all non-major updates.

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
    new Bluem\Tree\Options(rootId: -1, idFieldName: 'nodeId', parentIdFieldName: 'parentId'),
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
// Get the top-level nodes (returns array)
$rootNodes = $tree->getRootNodes();

// Get all nodes (returns array)
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

// Ditto, but include the node itself (identical to $node->getParent()->getChildren())
$siblings = $node->getSiblingsAndSelf();

// Get a node's preceding sibling (null, if there is no preceding sibling)
$precedingSibling = $node->getPrecedingSibling();

// Get a node's following sibling (null, if there is no following sibling)
$followingSibling = $node->getFollowingSibling();

// Does the node have children?
$bool = $node->hasChildren();

// Get the number of Children
$integer = $node->countChildren();

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


Example:  Using it with literal data
------------------------------------------
```php
<?php

require 'vendor/autoload.php';

// Create the Tree instance
$tree = new BlueM\Tree([
    ['id' => 1, 'name' => 'Africa'],
    ['id' => 2, 'name' => 'America'],
    ['id' => 3, 'name' => 'Asia'],
    ['id' => 4, 'name' => 'Australia'],
    ['id' => 5, 'name' => 'Europe'],
    ['id' => 6, 'name' => 'Santa Barbara', 'parent' => 8],
    ['id' => 7, 'name' => 'USA', 'parent' => 2],
    ['id' => 8, 'name' => 'California', 'parent' => 7],
    ['id' => 9, 'name' => 'Germany', 'parent' => 5],
    ['id' => 10, 'name' => 'Hamburg', 'parent' => 9],
]);
...
...
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

JSON serialization
===================
As `Tree` implements `JsonSerializable`, a tree can be serialized to JSON. By default, the resulting JSON represents a flat (non-hierarchical) representation of the tree data, which – once decoded from JSON – can be re-fed into a new `Tree` instance. In version before 3.0, you had to subclass the `Tree` and the `Node` class to customize the JSON output. Now, serialization is extracted to an external helper class which can be changed both by setting a constructor argument or at runtime just before serialization. However, the default serialization result is the same as before, so you won’t notice any change in behavior unless you tweaked JSON serialization.

To control the JSON, you can either pass an option `jsonSerializer` to an `\BlueM\Tree\Options` instance you pass the the `Tree` constructor, which must be an object implementing `\BlueM\Tree\Serializer\TreeJsonSerializerInterface`. Or you call method `setJsonSerializer()` on the tree. The latter approach can also be used to re-set serialization behavior to the default by calling it without an argument.

The library comes with two distinct serializers: `\BlueM\Tree\Serializer\FlatTreeJsonSerializer` is the default, which is used if no serializer is set and which results in the “old”, flat JSON output. Plus, there is `\BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer`, which creates a hierarchical, depth-first sorted representation of the tree nodes. If you need something else, feel free to write your own serializer.


Handling inconsistent data
==========================
If a problem is detected while building the tree (such as a parent reference to the node itself or in invalid parent ID), an exception (subclass of `InvalidParentException`) is thrown. Often this makes sense, but it might not always. For those cases, you can pass in a callable by setting `$buildWarningCallback` of a `\BlueM\Tree\Options` instance which can be given as argument 2 to `Tree`’s constructor, and which will be called whenever a problem is seen. The signature of the callable is like this:

```php
function yourCallback(MissingNodeInvalidParentException $exception, Tree $tree, Node $node, mixed $parentId) {
   // Log this, throw your own exception, ignore this, ...
}
```

Running Tests
==============
PHPUnit is configured as a dev dependency, so running tests is a matter of:

* `composer install`
* `composer test`
* `composer test-coverage` (For generating coverage output, requires xDebug.)


Version History
=================

4 (2025-xx-xx)
---------------
* Breaking change: minimum PHP version is 8.2
* Breaking change: Node properties are no longer normalized to lowercase
* Breaking change: The argument to `Node::get($propertyName)` is now treated case-sensitively
* Breaking change: `$node->propertyName` is now case-sensitive
* No breaking change: Using as getter for accessing a property’s value is still case-insensitive, as functions are case-insensitive in PHP. So if you have a property `foo` on a node, you can call `$node->getFoo()` or `$node->getFOO()`. However, if you have property names which differ *only* in case (admittedly, an extreme edge case), the property with the exactly matching name is preferred over the case-insensitive one.
* Breaking change: The signature for `Node::__construct()` has changed. The parent ID is no longer passed as 2nd argument – this was redundant, as the parent is available in `$node->parent`.
* Breaking change: argument 2 for `Tree`’s constructor is no longer an associative array, but a DTO, which ensures correct naming and type-safety.
* Breaking change (if you subclassed it): The signature for `Tree::createNode()` has changed, as the iterable containing the node properties is now optional.
* Breaking change: Calling the getter for a non-existent property now throws a `BadMethodCallException` (previously `BadFunctionCallException`)
* Breaking change: there is no longer a “root” node. (Which was confusing anyway, as there is also a method `Tree::getRootNodes()`, which returns something completely different from the “root” node.) Instead, now there are only nodes for the data which is passed to the constructor – no longer any “helper” nodes.
* Breaking change (at least theoretically): class `InvalidDatatypeException` was removed, which is no longer needed due to a typehint
* It’s now possible to call get('parent') or get('children') on a node
* Internal changes: added typehints; cleaned up and improved, and simplified unit tests (coverage is now 100%); added code quality tools (PHPStan, php-cs-fixer)

3.2 (2021-12-30)
-----
* Slight modernizations regarding typehints, but apart from that no changes in API or behavior
* Supresses warnings on PHP 8.1
* Minimum PHP version is now 7.3 (previously: 7.0)

3.1 (2019-09-15)
-----
* Building the tree is now more flexible and more extendable:
   * `null` can be used as root ID
   * Method `Tree::build()`, which was `private` before, is now `protected`, in order to hook into the building process
   * Until version 3.0, in case of a data inconsistency, an `InvalidParentException` was thrown. Now, the behavior is swappable through a new constructor option “buildWarningCallback” or through subclassing and overwriting method `buildWarningHandler()`

3.0 (2019-03-28)
-----
* JSON serialization is easily customizable by setting a custom serializer. (See section “JSON serialization” in this Readme.) *Potential* BC break: if in your own code, you subclassed `Tree` or `Tree\Node` and added an own implementation of `jsonSerialize()`, your current code *might* break. This is the only reason for the major version number bump, as everything else is unchanged. It is highly likely that you don’t have to change anything to be compatible with v3.
* License change: BSD-2 to BSD-3
 
 
2.0 (2018-02-04)
-----
* BC break: `getAncestors()` or `getAncestorsAndSelf()` no longer include the root node as last item of the returned array. *Solution:* add it yourself, if you need it. 
* BC break: Removed argument to `getAncestors()`. *Solution:* If you passed `true` as argument before, change this to `getAncestorsAndSelf()`.
* BC break: Removed argument to `getDescendants()`. *Solution:* If you passed `true` as argument before, change this to `getDescendantsAndSelf()`.
* BC break: Removed argument to `getSiblings()`. *Solution:* If you passed `true` as argument before, change this to `getSiblingsAndSelf()`.
* BC break: Moved `BlueM\Tree\InvalidParentException` to `BlueM\Tree\Exception\InvalidParentException`. *Solution:* Update namespace imports.
* BC break: when a property contains `null`, and `$node->get('the-property')` is invoked, `null` is simply returned instead of throwing an exception, which was the previous behavior.
* New: Added method `Tree::rebuildWithData()` to rebuild the tree with new data.
* New: `Tree` and `Tree\Node` implement `JsonSerializable` and provide default implementations, which means that you can easily serialize the whole tree oder nodes to JSON.
* New: The tree data no longer has to be an `array`, but instead it must be an `iterable`, which means that you can either pass in an `array` or an object implementing the `Traversable` interface. Also, the data for a node no longer has to be an array, but can also be an object implementing the `Iterator` interface. These changes should make working with the library more flexible.
* New: a custom build warning callback/callable is passed one of the `InvalidParentException` subclasses, plus the `Tree` instance, `Node` instance and parent ID.
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
This code was written by Carsten Blüm (www.bluem.net) and contributors and licensed under the BSD 3-Clause license.
