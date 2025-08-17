<?php

namespace BlueM\Tree;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    #[Test]
    #[TestDox('Level: The level of a top-level node is 1')]
    public function level1(): void
    {
        $sut = new Node(1);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, null);

        static::assertSame(1, $sut->getLevel());
    }

    #[Test]
    #[TestDox('Level: A node 2 levels below a top-level node has level 3')]
    public function level2(): void
    {
        $sut = new Node(123);
        $parent = new Node(456);
        $grandParent = new Node(789);

        $parentProperty = new \ReflectionProperty(Node::class, 'parent');

        $parentProperty->setValue($sut, $parent);
        $parentProperty->setValue($parent, $grandParent);
        $parentProperty->setValue($grandParent, null);

        static::assertSame(3, $sut->getLevel());
    }

    #[Test]
    #[TestDox('Ancestors: A top-level node’s ancestors is an empty array')]
    public function rootAncestors(): void
    {
        $sut = new Node(19);
        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, null);

        static::assertEquals([], $sut->getAncestors());
    }

    #[Test]
    #[TestDox('Ancestors: A node’s ancestors is an array of nodes, ordered from nearer to more distant ones')]
    public function nodeAncestors(): void
    {
        $parentProperty = new \ReflectionProperty(Node::class, 'parent');

        $sut = new Node(3);
        $parent = new Node(2);
        $grandParent = new Node(1);

        $parentProperty->setValue($sut, $parent);
        $parentProperty->setValue($parent, $grandParent);
        $parentProperty->setValue($grandParent, null);

        static::assertCount(2, $sut->getAncestors());
        static::assertSame([$parent, $grandParent], $sut->getAncestors());
    }

    #[Test]
    #[TestDox('Ancestors: The node itself can be included in the list of ancestors')]
    public function nodeAncestorsIncludingSelf(): void
    {
        $parentProperty = new \ReflectionProperty(Node::class, 'parent');

        $sut = new Node(3);
        $parent = new Node(2);
        $grandParent = new Node(1);

        $parentProperty->setValue($sut, $parent);
        $parentProperty->setValue($parent, $grandParent);
        $parentProperty->setValue($grandParent, null);

        static::assertSame([$sut, $parent, $grandParent], $sut->getAncestorsAndSelf());
    }

    #[Test]
    #[TestDox('Descendants: A node’s descendants is an array of nodes, with depth-first sorting')]
    public function nodeDescendants(): void
    {
        $childrenProperty = new \ReflectionProperty(Node::class, 'children');

        $sut = new Node(1);
        $child1 = new Node(2);
        $child2 = new Node(3);
        $grandChild1 = new Node(4);
        $grandChild2 = new Node(5);

        $childrenProperty->setValue($sut, [$child1, $child2]);
        $childrenProperty->setValue($child1, [$grandChild1, $grandChild2]);

        static::assertSame(
            [$child1, $grandChild1, $grandChild2, $child2],
            $sut->getDescendants()
        );
    }

    #[Test]
    #[TestDox('Descendants: The node itself can be included in the list of descendants')]
    public function nodeDescendantsIncludingSelf(): void
    {
        $childrenProperty = new \ReflectionProperty(Node::class, 'children');

        $sut = new Node(1);
        $child1 = new Node(2);
        $child2 = new Node(3);
        $grandChild1 = new Node(4);
        $grandChild2 = new Node(5);

        $childrenProperty->setValue($sut, [$child1, $child2]);
        $childrenProperty->setValue($child1, [$grandChild1, $grandChild2]);

        static::assertSame(
            [$sut, $child1, $grandChild1, $grandChild2, $child2],
            $sut->getDescendantsAndSelf()
        );
    }

    #[Test]
    #[TestDox('Parent: For a top-level node, null is returned when calling getParent()')]
    public function tryingToGetTheParentReturnsNullForARootNode(): void
    {
        $sut = new Node(93);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, null);

        static::assertNull($sut->getParent());
    }

    #[Test]
    #[TestDox('Parent: For a non-root node, the parent node is returned when calling getParent()')]
    public function theParentNodeCanBeRetrieved(): void
    {
        $sut = new Node(2);
        $parent = new Node(4);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, $parent);

        static::assertSame($parent, $sut->getParent());
    }

    #[Test]
    #[TestDox('Siblings: The previous sibling can be retrieved')]
    public function siblingGetPrevious(): void
    {
        $sut = new Node(123);
        $sibling = new Node(456);

        $parent = new Node(789);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sibling, $sut]);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, $parent);

        static::assertSame($sibling, $sut->getPrecedingSibling());
    }

    #[Test]
    #[TestDox('Siblings: The previous sibling can be retrieved')]
    public function siblingGetPreviousOnFirstNode(): void
    {
        $sut = new Node(123);
        $parent = new Node(789);

        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sut]);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, $parent);

        static::assertNull($sut->getPrecedingSibling());
    }

    #[Test]
    #[TestDox('Siblings: The next sibling can be retrieved')]
    public function siblingGetNext(): void
    {
        $sut = new Node(123);
        $sibling = new Node(456);

        $parent = new Node(789);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sut, $sibling]);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, $parent);

        static::assertSame($sibling, $sut->getFollowingSibling());
    }

    #[Test]
    #[TestDox('Siblings: All siblings can be retrieved, not including the node itself')]
    public function siblingsGetAll(): void
    {
        $sut = new Node(10);
        $sibling1 = new Node(20);
        $sibling2 = new Node(30);

        $parent = new Node(333);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sut, $sibling1, $sibling2]);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, $parent);

        static::assertSame(
            [$sibling1, $sibling2],
            $sut->getSiblings()
        );
    }

    #[Test]
    #[TestDox('Siblings: All siblings can be retrieved, including the node itself')]
    public function siblingsGetAllAndSelf(): void
    {
        $sut = new Node(10);
        $sibling1 = new Node(20);
        $sibling2 = new Node(30);

        $parent = new Node(333);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sibling1, $sut, $sibling2]);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, $parent);

        static::assertSame(
            [$sibling1, $sut, $sibling2],
            $sut->getSiblingsAndSelf()
        );
    }

    #[Test]
    #[TestDox('Siblings: All siblings can be retrieved, even when node IDs have different types')]
    public function siblingsGetAllAndWithMixedIdsTypes(): void
    {
        $sut = new Node(0);
        $sibling1 = new Node('a');
        $sibling2 = new Node(30);

        $parent = new Node('333');
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setValue($parent, [$sut, $sibling1, $sibling2]);

        $parentProperty = new \ReflectionProperty($sut, 'parent');
        $parentProperty->setValue($sut, $parent);

        static::assertSame(
            [$sibling1, $sibling2],
            $sut->getSiblings()
        );
    }

    #[Test]
    #[TestDox('Children: When calling getChildren(), an empty array is returned if there are no child nodes')]
    public function childrenEmptyArray(): void
    {
        static::assertSame([], (new Node(52))->getChildren());
    }

    #[Test]
    #[TestDox('Children: When calling getChildren(), an array of child nodes is returned')]
    public function childrenGet(): void
    {
        $node1 = new Node(10);
        $node2 = new Node(20);
        $node3 = new Node(30);

        $sut = new Node(333);
        $childrenProperty = new \ReflectionProperty($sut, 'children');
        $childrenProperty->setValue($sut, [$node1, $node2, $node3]);

        static::assertSame([$node1, $node2, $node3], $sut->getChildren());
    }

    #[Test]
    #[TestDox('Children: Public property “children” can be used instead of getChildren()')]
    public function getChildrenViaPublicProperty(): void
    {
        /* @phpstan-ignore-next-line */
        static::assertSame([], (new Node(52))->children);
    }

    #[Test]
    #[TestDox("Children: get('children') can be used instead of getChildren()")]
    public function getChildrenViaGetMethod(): void
    {
        static::assertSame([], (new Node(52))->get('children'));
    }

    #[Test]
    #[TestDox('Children: A node can tell if it has any child nodes')]
    public function childrenHas(): void
    {
        $sut = new Node(10);

        $childrenProperty = new \ReflectionProperty($sut, 'children');
        $childrenProperty->setValue($sut, ['dummy1', 'dummy2']);

        static::assertTrue($sut->hasChildren());
    }

    #[Test]
    #[TestDox('Children: A node knows the number of child nodes it has')]
    public function childrenCount(): void
    {
        $sut = new Node(10);

        $childrenProperty = new \ReflectionProperty($sut, 'children');
        $childrenProperty->setValue($sut, ['dummy1', 'dummy2']);

        static::assertSame(2, $sut->countChildren());
    }

    #[Test]
    #[TestDox('Children: A child node can be attached to a node')]
    public function childAdd(): void
    {
        $sut = new Node(100);
        $child = new Node(200);

        $sut->addChild($child);

        $childrenProperty = new \ReflectionProperty($sut, 'children');
        static::assertSame([$child], $childrenProperty->getValue($sut));

        $parentProperty = new \ReflectionProperty($child, 'parent');
        static::assertSame($sut, $parentProperty->getValue($child));
    }

    #[Test]
    #[TestDox('Properties / Getter: A node’s properties can be fetched case-insensitively, but preferring exact case, if properties differ in case')]
    public function getNodePropertyViaGetter(): void
    {
        $sut = new Node(16, ['foo' => 'foo', 'Foo' => 'Foo', 'BAR' => 'BAR']);

        static::assertEquals(16, $sut->getId());
        static::assertEquals(16, $sut->getID());
        /* @phpstan-ignore-next-line */
        static::assertSame('foo', $sut->getfoo());
        /* @phpstan-ignore-next-line */
        static::assertSame('Foo', $sut->getFoo());
        /* @phpstan-ignore-next-line */
        static::assertSame('BAR', $sut->getBar());
    }

    #[Test]
    #[TestDox('Properties / Getter: An exception is thrown when calling a getter for a non-existent property')]
    public function getNodeInexistentPropertyViaGetter(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Invalid method getNonExistentProperty() called');

        $sut = new Node(1, ['foo' => 'foo']);
        /* @phpstan-ignore-next-line */
        static::assertSame('bar', $sut->getNonExistentProperty());
    }

    #[Test]
    #[TestDox('Properties / get(): A node’s custom properties can be fetched case-sensitively using get()')]
    public function getNodePropertyViaGet(): void
    {
        $sut = new Node(1, ['foo' => 'foo', 'Foo' => 'Foo']);

        static::assertSame('foo', $sut->get('foo'));
        static::assertSame('Foo', $sut->get('Foo'));
    }

    #[Test]
    #[TestDox('Properties / get(): An exception is thrown when calling get() with an inexistent node property as argument')]
    public function getNodeInexistentPropertyViaGet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined property: Key (Node ID: 16)');

        $sut = new Node(16, ['key' => 'value']);
        $sut->get('Key'); // Case must match
    }

    #[Test]
    #[TestDox('Properties / Magic property: A property can be fetched case-sensitively as public property')]
    public function getNodePropertyViaPublicProperty(): void
    {
        $sut = new Node(1, ['foo' => 'foo1', 'Foo' => 'foo2']);

        /* @phpstan-ignore-next-line */
        static::assertSame('foo1', $sut->foo);
        /* @phpstan-ignore-next-line */
        static::assertSame('foo2', $sut->Foo);
    }

    #[Test]
    #[TestDox('Properties / Magic property: An exception is thrown when trying to fetch a non-existent public property')]
    public function getNodeInexistentPropertyViaPublicProperty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Undefined property');

        $sut = new Node(1, ['foo' => 'Foo']);
        /* @phpstan-ignore-next-line */
        $sut->FOO;
    }

    #[Test]
    #[TestDox('Properties / isset(): The existence of a property can be fetched case-sensitively using isset()')]
    public function nodePropertyIsset(): void
    {
        $sut = new Node(1, ['foo' => 'Foo', 'BAR' => null]);

        static::assertTrue(isset($sut->foo));
        static::assertFalse(isset($sut->FOO));
        static::assertTrue(isset($sut->BAR));
        static::assertFalse(isset($sut->bar));
        static::assertTrue(isset($sut->children));
        static::assertTrue(isset($sut->parent));
    }

    #[Test]
    #[TestDox('Properties can be fetched as an array')]
    public function nodePropertiesToArray(): void
    {
        $sut = new Node('xyz', ['foo' => 'bar', 'Number' => 123, 'number' => 456, 'myProperty' => 11.22, 'parent' => 789]);

        $parentNode = new Node(789);
        $parentNode->addChild($sut);

        static::assertEquals(
            ['foo' => 'bar', 'Number' => 123, 'number' => 456, 'id' => 'xyz', 'parent' => 789, 'myProperty' => 11.22],
            $sut->toArray()
        );
    }

    #[Test]
    #[Ticket('https://github.com/BlueM/Tree/issues/48')]
    public function aNodePropertyMayContainNull(): void
    {
        $sut = new Node(2, ['foo' => null]);
        static::assertNull($sut->get('foo'));
        /* @noinspection PhpUndefinedMethodInspection */
        static::assertNull($sut->getFoo());
        static::assertTrue($sut->__isset('foo'));
    }

    #[Test]
    #[TestDox('When serialized to JSON, an object containing all properties is returned')]
    public function nodePropertiesToJson(): void
    {
        $sut = new Node('xyz', ['foo' => 'bar', 'X' => 123, 'parent' => 456]);

        $parentNode = new Node(456);
        $parentNode->addChild($sut);

        static::assertEquals(
            '{"foo":"bar","X":123,"parent":456,"id":"xyz"}',
            json_encode($sut)
        );
    }

    #[Test]
    #[TestDox('When typecasted to string, the string representation of the node’s ID is returned')]
    public function inScalarContextTheNodeIsTypecastedToItsId(): void
    {
        $sut = new Node(123);
        static::assertEquals('123', (string) $sut);
    }
}
