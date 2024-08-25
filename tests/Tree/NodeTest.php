<?php

namespace BlueM\Tree;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    #[Test]
    #[TestDox('Level: The level of the root node is null')]
    public function level0(): void
    {
        $node = new Node(0, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, null);

        static::assertSame(0, $node->getLevel());
    }

    #[Test]
    #[TestDox('Level: A node 2 levels below the root node has level 2')]
    public function level2(): void
    {
        $node = new Node(123, null);
        $parent = new Node(789, null);
        $rootNode = new Node(0, null);

        $parentProperty = new \ReflectionProperty(Node::class, 'parent');
        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $rootNode);

        static::assertSame(2, $node->getLevel());
    }

    #[Test]
    #[TestDox('Ancestors: The root node’s ancestors is an empty array')]
    public function rootAncestors(): void
    {
        $node = new Node(0, null);
        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, null);

        static::assertEquals([], $node->getAncestors());
    }

    #[Test]
    #[TestDox('Ancestors: A node’s ancestors is an array of nodes, ordered from nearer to more distant ones')]
    public function nodeAncestors(): void
    {
        $parentProperty = new \ReflectionProperty(Node::class, 'parent');

        $node = new Node(3, null);
        $parent = new Node(2, null);
        $grandParent = new Node(1, null);
        $rootNode = new Node(0, null);

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);
        $parentProperty->setValue($grandParent, $rootNode);

        static::assertSame([$parent, $grandParent], $node->getAncestors());
    }

    #[Test]
    #[TestDox('Ancestors: The node itself can be included in the list of ancestors')]
    public function nodeAncestorsIncludingSelf(): void
    {
        $parentProperty = new \ReflectionProperty(Node::class, 'parent');

        $node = new Node(3, null);
        $parent = new Node(2, null);
        $grandParent = new Node(1, null);
        $rootNode = new Node(0, null);

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);
        $parentProperty->setValue($grandParent, $rootNode);

        static::assertSame([$node, $parent, $grandParent], $node->getAncestorsAndSelf());
    }

    #[Test]
    #[TestDox('Descendants: A node’s descendants is an array of nodes, with depth-first sorting')]
    public function nodeDescendants(): void
    {
        $childrenProperty = new \ReflectionProperty(Node::class, 'children');

        $node = new Node(1, null);
        $child1 = new Node(2, null);
        $child2 = new Node(3, null);
        $grandChild1 = new Node(4, null);
        $grandChild2 = new Node(5, null);

        $childrenProperty->setValue($node, [$child1, $child2]);
        $childrenProperty->setValue($child1, [$grandChild1, $grandChild2]);

        static::assertSame(
            [$child1, $grandChild1, $grandChild2, $child2],
            $node->getDescendants()
        );
    }

    #[Test]
    #[TestDox('Descendants: The node itself can be included in the list of descendants')]
    public function nodeDescendantsIncludingSelf(): void
    {
        $childrenProperty = new \ReflectionProperty(Node::class, 'children');

        $node = new Node(1, null);
        $child1 = new Node(2, null);
        $child2 = new Node(3, null);
        $grandChild1 = new Node(4, null);
        $grandChild2 = new Node(5, null);

        $childrenProperty->setValue($node, [$child1, $child2]);
        $childrenProperty->setValue($child1, [$grandChild1, $grandChild2]);

        static::assertSame(
            [$node, $child1, $grandChild1, $grandChild2, $child2],
            $node->getDescendantsAndSelf()
        );
    }

    #[Test]
    #[TestDox('Parent: For a root node, null is returned when calling getParent()')]
    public function tryingToGetTheParentReturnsNullForTheRootNode(): void
    {
        $node = new Node(0, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, null);

        static::assertNull($node->getParent());
    }

    #[Test]
    #[TestDox('Parent: For a non-root node, the parent node is returned when calling getParent()')]
    public function theParentNodeCanBeRetrieved(): void
    {
        $node = new Node(2, null);
        $parent = new Node(4, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertSame($parent, $node->getParent());
    }

    #[Test]
    #[TestDox('Siblings: The previous sibling can be retrieved')]
    public function siblingGetPrevious(): void
    {
        $node = new Node(123, null);
        $sibling = new Node(456, null);

        $parent = new Node(789, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sibling, $node]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertSame($sibling, $node->getPrecedingSibling());
    }

    #[Test]
    #[TestDox('Siblings: The previous sibling can be retrieved')]
    public function siblingGetPreviousOnFirstNode(): void
    {
        $node = new Node(123, null);
        $parent = new Node(789, null);

        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$node]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertNull($node->getPrecedingSibling());
    }

    #[Test]
    #[TestDox('Siblings: The next sibling can be retrieved')]
    public function siblingGetNext(): void
    {
        $node = new Node(123, null);
        $sibling = new Node(456, null);

        $parent = new Node(789, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$node, $sibling]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertSame($sibling, $node->getFollowingSibling());
    }

    #[Test]
    #[TestDox('Siblings: All siblings can be retrieved, not including the node itself')]
    public function siblingsGetAll(): void
    {
        $node = new Node(10, null);
        $sibling1 = new Node(20, null);
        $sibling2 = new Node(30, null);

        $parent = new Node(333, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$node, $sibling1, $sibling2]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertSame(
            [$sibling1, $sibling2],
            $node->getSiblings()
        );
    }

    #[Test]
    #[TestDox('Siblings: All siblings can be retrieved, including the node itself')]
    public function siblingsGetAllAndSelf(): void
    {
        $node = new Node(10, null);
        $sibling1 = new Node(20, null);
        $sibling2 = new Node(30, null);

        $parent = new Node(333, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sibling1, $node, $sibling2]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertSame(
            [$sibling1, $node, $sibling2],
            $node->getSiblingsAndSelf()
        );
    }

    #[Test]
    #[TestDox('Siblings: All siblings can be retrieved, even when node IDs have different types')]
    public function siblingsGetAllAndWithMixedIdsTypes(): void
    {
        $node = new Node(0, null);
        $sibling1 = new Node('a', null);
        $sibling2 = new Node(30, null);

        $parent = new Node('333', null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$node, $sibling1, $sibling2]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertSame(
            [$sibling1, $sibling2],
            $node->getSiblings()
        );
    }

    #[Test]
    #[TestDox('Children: When calling getChildren(), an empty array is returned if there are no child nodes')]
    public function childrenEmptyArray(): void
    {
        $parent = new Node(52, null);
        static::assertSame([], $parent->getChildren());
    }

    #[Test]
    #[TestDox('Children: When calling getChildren(), an array of child nodes is returned')]
    public function childrenGet(): void
    {
        $node1 = new Node(10, null);
        $node2 = new Node(20, null);
        $node3 = new Node(30, null);

        $parent = new Node(333, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$node1, $node2, $node3]);

        static::assertSame([$node1, $node2, $node3], $parent->getChildren());
    }

    #[Test]
    #[TestDox('Children: Public property “children” can be used instead of getChildren()')]
    public function getChildrenViaPublicProperty(): void
    {
        $node = new Node(52, null);
        /* @phpstan-ignore-next-line */
        static::assertSame([], $node->children);
    }

    #[Test]
    #[TestDox("Children: get('children') can be used instead of getChildren()")]
    public function getChildrenViaGetMethod(): void
    {
        $node = new Node(52, null);
        static::assertSame([], $node->get('children'));
    }

    #[Test]
    #[TestDox('Children: A node can tell if it has any child nodes')]
    public function childrenHas(): void
    {
        $node = new Node(10, null);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertTrue($node->hasChildren());
    }

    #[Test]
    #[TestDox('Children: A node knows the number of child nodes it has')]
    public function childrenCount(): void
    {
        $node = new Node(10, null);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertSame(2, $node->countChildren());
    }

    #[Test]
    #[TestDox('Children: A child node can be attached to a node')]
    public function childAdd(): void
    {
        $parent = new Node(100, null);
        $child = new Node(200, null);

        $parent->addChild($child);

        $childrenProperty = new \ReflectionProperty($parent, 'children');
        static::assertSame([$child], $childrenProperty->getValue($parent));

        $parentProperty = new \ReflectionProperty($child, 'parent');
        static::assertSame($parent, $parentProperty->getValue($child));

        $propertiesProperty = new \ReflectionProperty($child, 'properties');
        static::assertSame(
            ['id' => 200, 'parent' => 100],
            $propertiesProperty->getValue($child)
        );
    }

    #[Test]
    #[TestDox('Properties / Getter: A node’s properties can be fetched case-insensitively, but preferring exact case, if properties differ in case')]
    public function getNodePropertyViaGetter(): void
    {
        $node = new Node(16, null, ['foo' => 'foo', 'Foo' => 'Foo', 'BAR' => 'BAR']);

        static::assertEquals(16, $node->getId());
        static::assertEquals(16, $node->getID());
        /* @phpstan-ignore-next-line */
        static::assertSame('foo', $node->getfoo());
        /* @phpstan-ignore-next-line */
        static::assertSame('Foo', $node->getFoo());
        /* @phpstan-ignore-next-line */
        static::assertSame('BAR', $node->getBar());
    }

    #[Test]
    #[TestDox('Properties / Getter: An exception is thrown when calling a getter for a non-existent property')]
    public function getNodeInexistentPropertyViaGetter(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Invalid method getNonExistentProperty() called');

        $node = new Node(1, null, ['foo' => 'foo']);
        /* @phpstan-ignore-next-line */
        static::assertSame('bar', $node->getNonExistentProperty());
    }

    #[Test]
    #[TestDox('Properties / get(): A node’s custom properties can be fetched case-sensitively using get()')]
    public function getNodePropertyViaGet(): void
    {
        $node = new Node(1, null, ['foo' => 'foo', 'Foo' => 'Foo']);

        static::assertSame('foo', $node->get('foo'));
        static::assertSame('Foo', $node->get('Foo'));
    }

    #[Test]
    #[TestDox('Properties / get(): An exception is thrown when calling get() with an inexistent node property as argument')]
    public function getNodeInexistentPropertyViaGet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined property: Key (Node ID: 16)');

        $node = new Node(16, null, ['key' => 'value']);
        $node->get('Key'); // Case must match
    }

    #[Test]
    #[TestDox('Properties / Magic property: A property can be fetched case-sensitively as public property')]
    public function getNodePropertyViaPublicProperty(): void
    {
        $node = new Node(1, null, ['foo' => 'foo1', 'Foo' => 'foo2']);

        /* @phpstan-ignore-next-line */
        static::assertSame('foo1', $node->foo);
        /* @phpstan-ignore-next-line */
        static::assertSame('foo2', $node->Foo);
    }

    #[Test]
    #[TestDox('Properties / Magic property: An exception is thrown when trying to fetch a non-existent public property')]
    public function getNodeInexistentPropertyViaPublicProperty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Undefined property');

        $node = new Node(1, null, ['foo' => 'Foo']);
        /* @phpstan-ignore-next-line */
        $node->FOO;
    }

    #[Test]
    #[TestDox('Properties / isset(): The existence of a property can be fetched case-sensitively using isset()')]
    public function nodePropertyIsset(): void
    {
        $node = new Node(1, null, ['foo' => 'Foo', 'BAR' => null]);

        static::assertTrue(isset($node->foo));
        static::assertFalse(isset($node->FOO));
        static::assertTrue(isset($node->BAR));
        static::assertFalse(isset($node->bar));
        static::assertTrue(isset($node->children));
        static::assertTrue(isset($node->parent));
    }

    #[Test]
    #[TestDox('Properties can be fetched as an array')]
    public function nodePropertiesToArray(): void
    {
        $node = new Node('xyz', 789, ['foo' => 'bar', 'Number' => 123, 'number' => 456, 'myProperty' => 11.22]);

        static::assertEquals(
            ['foo' => 'bar', 'Number' => 123, 'number' => 456, 'id' => 'xyz', 'parent' => 789, 'myProperty' => 11.22],
            $node->toArray()
        );
    }

    #[Test]
    #[TestDox('When serialized to JSON, an object containing all properties is returned')]
    public function nodePropertiesToJson(): void
    {
        $node = new Node('xyz', 456, ['foo' => 'bar', 'X' => 123]);
        static::assertEquals(
            '{"foo":"bar","X":123,"id":"xyz","parent":456}',
            json_encode($node)
        );
    }

    #[Test]
    #[TestDox('When typecasted to string, the string representation of the node’s ID is returned')]
    public function inScalarContextTheNodeIsTypecastedToItsId(): void
    {
        $node = new Node(123, null);
        static::assertEquals('123', (string) $node);
    }
}
