<?php

namespace BlueM\Tree;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    #[Test]
    public function thePreviousSiblingCanBeRetrieved(): void
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
    public function tryingToGetThePreviousSiblingReturnsNullWhenCalledOnTheFirstNode(): void
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
    public function theNextSiblingCanBeRetrieved(): void
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
    public function allSiblingsCanBeRetrieved(): void
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
    public function allSiblingsCanBeRetrievedIncludingTheNodeItself(): void
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
    public function allSiblingsCanBeRetrievedWhenMixedDataTypesAreUsedForTheIds(): void
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
    public function theChildNodesCanBeRetrieved(): void
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
    public function whenTryingToGetTheChildNodesAnEmptyArrayIsReturnedIfThereAreNoChildNodes(): void
    {
        $parent = new Node(52, null);
        static::assertSame([], $parent->getChildren());
    }

    #[Test]
    public function aNodeCanTellHowManyChildrenItHas(): void
    {
        $node = new Node(10, null);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertSame(2, $node->countChildren());
    }

    #[Test]
    public function aNodeCanTellIfItHasAnyChildNodes(): void
    {
        $node = new Node(10, null);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertTrue($node->hasChildren());
    }

    #[Test]
    public function theParentNodeCanBeRetrieved(): void
    {
        $node = new Node(2, null);
        $parent = new Node(4, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, $parent);

        static::assertSame($parent, $node->getParent());
    }

    #[Test]
    public function tryingToGetTheParentReturnsNullForTheRootNode(): void
    {
        $node = new Node(0, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, null);

        static::assertNull($node->getParent());
    }

    #[Test]
    public function aChildCanBeAttachedToANode(): void
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
    public function tryingToGetANonExistentPropertyUsingGetThrowsAnException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined property: foobar (Node ID: 16)');

        $node = new Node(16, null, ['key' => 'value']);
        static::assertEquals('value', $node->get('foobar'));
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
    public function tryingToGetANonExistentPropertyUsingMagicMethodThrowsAnException(): void
    {
        $this->expectException(\BadFunctionCallException::class);
        $this->expectExceptionMessage('Invalid method getFoobar()');

        $node = new Node(16, null, ['key' => 'value']);
        static::assertEquals('value', $node->getFoobar());
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
    public function nodePropertiesAreHandledCaseInsensitively(): void
    {
        $node = new Node(1, null, ['foo' => 'Foo', 'BAR' => 'Bar']);

        static::assertSame('Foo', $node->foo);
        static::assertSame('Foo', $node->get('foo'));
        static::assertSame('Foo', $node->getFoo());
        static::assertSame('Bar', $node->bar);
        static::assertSame('Bar', $node->get('bar'));
        static::assertSame('Bar', $node->getBar());
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
    public function anExceptionIsThrownWhenAccessingAnInexistentMagicProperty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Undefined property');

        $node = new Node(1, null);
        $node->nosuchproperty;
    }

    #[Test]
    public function thePropertiesCanBeFetchedAsAnArray(): void
    {
        $node = new Node('xyz', 456, ['foo' => 'bar', 'gggg' => 123]);
        static::assertEquals(['foo' => 'bar', 'gggg' => 123, 'id' => 'xyz', 'parent' => 456], $node->toArray());
    }

    #[Test]
    public function whenSerializingANodeToJsonItsArrayRepresentationIsUsed(): void
    {
        $node = new Node('xyz', 456, ['foo' => 'bar', 'gggg' => 123]);
        static::assertEquals(
            '{"foo":"bar","gggg":123,"id":"xyz","parent":456}',
            json_encode($node)
        );
    }

    #[Test]
    public function inScalarContextTheNodeIsTypecastedToItsId(): void
    {
        $node = new Node(123, null);
        static::assertEquals('123', (string) $node);
    }

    #[Test]
    public function theLevelOfARootNodeIs0(): void
    {
        $node = new Node(0, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, null);

        static::assertSame(0, $node->getLevel());
    }

    #[Test]
    public function aNode2LevelsBelowTheRootNodeHasLevel2(): void
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
    public function theRootNodesAncestorsIsAnEmptyArray(): void
    {
        $node = new Node(0, null);
        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setValue($node, null);

        static::assertEquals([], $node->getAncestors());
    }

    #[Test]
    public function aNodesAncestorsCanBeRetrieved(): void
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
    public function aNodesAncestorsCanBeRetrievedIncludingTheNodeItself(): void
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
    public function aNodesDescendantsCanBeRetrieved(): void
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
}
