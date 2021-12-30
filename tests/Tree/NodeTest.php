<?php

/* @noinspection PhpUndefinedFieldInspection */
/* @noinspection PhpUndefinedMethodInspection */
/* @noinspection ReturnTypeCanBeDeclaredInspection */

namespace BlueM\Tree;

use PHPUnit\Framework\TestCase;

/**
 * @covers \BlueM\Tree\Node
 */
class NodeTest extends TestCase
{
    /**
     * @test
     */
    public function thePreviousSiblingCanBeRetrieved()
    {
        $node = new Node(123, null);
        $sibling = new Node(456, null);

        $parent = new Node(789, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$sibling, $node]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertSame($sibling, $node->getPrecedingSibling());
    }

    /**
     * @test
     */
    public function tryingToGetThePreviousSiblingReturnsNullWhenCalledOnTheFirstNode()
    {
        $node = new Node(123, null);
        $parent = new Node(789, null);

        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$node]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertNull($node->getPrecedingSibling());
    }

    /**
     * @test
     */
    public function theNextSiblingCanBeRetrieved()
    {
        $node = new Node(123, null);
        $sibling = new Node(456, null);

        $parent = new Node(789, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$node, $sibling]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertSame($sibling, $node->getFollowingSibling());
    }

    /**
     * @test
     */
    public function allSiblingsCanBeRetrieved()
    {
        $node = new Node(10, null);
        $sibling1 = new Node(20, null);
        $sibling2 = new Node(30, null);

        $parent = new Node(333, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$node, $sibling1, $sibling2]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertSame(
            [$sibling1, $sibling2],
            $node->getSiblings()
        );
    }

    /**
     * @test
     */
    public function allSiblingsCanBeRetrievedIncludingTheNodeItself()
    {
        $node = new Node(10, null);
        $sibling1 = new Node(20, null);
        $sibling2 = new Node(30, null);

        $parent = new Node(333, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$sibling1, $node, $sibling2]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertSame(
            [$sibling1, $node, $sibling2],
            $node->getSiblingsAndSelf()
        );
    }

    /**
     * @test
     */
    public function allSiblingsCanBeRetrievedWhenMixedDataTypesAreUsedForTheIds()
    {
        $node = new Node(0, null);
        $sibling1 = new Node('a', null);
        $sibling2 = new Node(30, null);

        $parent = new Node('333', null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$node, $sibling1, $sibling2]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertSame(
            [$sibling1, $sibling2],
            $node->getSiblings()
        );
    }

    /**
     * @test
     */
    public function theChildNodesCanBeRetrieved()
    {
        $node1 = new Node(10, null);
        $node2 = new Node(20, null);
        $node3 = new Node(30, null);

        $parent = new Node(333, null);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$node1, $node2, $node3]);

        static::assertSame([$node1, $node2, $node3], $parent->getChildren());
    }

    /**
     * @test
     */
    public function whenTryingToGetTheChildNodesAnEmptyArrayIsReturnedIfThereAreNoChildNodes()
    {
        $parent = new Node(52, null);
        static::assertSame([], $parent->getChildren());
    }

    /**
     * @test
     */
    public function aNodeCanTellHowManyChildrenItHas()
    {
        $node = new Node(10, null);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertSame(2, $node->countChildren());
    }

    /**
     * @test
     */
    public function aNodeCanTellIfItHasAnyChildNodes()
    {
        $node = new Node(10, null);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertTrue($node->hasChildren());
    }

    /**
     * @test
     */
    public function theParentNodeCanBeRetrieved()
    {
        $node = new Node(2, null);
        $parent = new Node(4, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertSame($parent, $node->getParent());
    }

    /**
     * @test
     */
    public function tryingToGetTheParentReturnsNullForTheRootNode()
    {
        $node = new Node(0, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        static::assertNull($node->getParent());
    }

    /**
     * @test
     */
    public function aChildCanBeAttachedToANode()
    {
        $parent = new Node(100, null);
        $child = new Node(200, null);

        $parent->addChild($child);

        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        static::assertSame([$child], $childrenProperty->getValue($parent));

        $parentProperty = new \ReflectionProperty($child, 'parent');
        $parentProperty->setAccessible(true);
        static::assertSame($parent, $parentProperty->getValue($child));

        $propertiesProperty = new \ReflectionProperty($child, 'properties');
        $propertiesProperty->setAccessible(true);
        static::assertSame(
            ['id' => 200, 'parent' => 100],
            $propertiesProperty->getValue($child)
        );
    }

    /**
     * @test
     */
    public function theNodeIdCanBeRetrieved()
    {
        $node = new Node(16, null);
        static::assertEquals(16, $node->getId());
    }

    /**
     * @test
     */
    public function aNodePropertyCanBeFetchedUsingMethodGet()
    {
        $node = new Node(16, null, ['key' => 'value']);
        static::assertEquals('value', $node->get('key'));
    }

    /**
     * @test
     */
    public function tryingToGetANonExistentPropertyUsingGetThrowsAnException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined property: foobar (Node ID: 16)');

        $node = new Node(16, null, ['key' => 'value']);
        static::assertEquals('value', $node->get('foobar'));
    }

    /**
     * @test
     */
    public function aNodePropertyCanBeFetchedUsingMagicMethod()
    {
        $node = new Node(16, null, ['key' => 'value']);
        static::assertEquals('value', $node->getKey());
    }

    /**
     * @test
     */
    public function tryingToGetANonExistentPropertyUsingMagicMethodThrowsAnException()
    {
        $this->expectException(\BadFunctionCallException::class);
        $this->expectExceptionMessage('Invalid method getFoobar()');

        $node = new Node(16, null, ['key' => 'value']);
        static::assertEquals('value', $node->getFoobar());
    }

    /**
     * @test
     */
    public function theExistenceOfAPropertyCanBeCheckedUsingIssetFunction()
    {
        $node = new Node(1, null, ['foo' => 'Foo', 'BAR' => 'Bar']);

        static::assertTrue(isset($node->foo));
        static::assertTrue(isset($node->FOO));
        static::assertTrue(isset($node->bar));
        static::assertTrue(isset($node->BAR));
        static::assertTrue(isset($node->children));
        static::assertTrue(isset($node->parent));
    }

    /**
     * @test
     */
    public function nodePropertiesAreHandledCaseInsensitively()
    {
        $node = new Node(1, null, ['foo' => 'Foo', 'BAR' => 'Bar']);

        static::assertSame('Foo', $node->foo);
        static::assertSame('Foo', $node->get('foo'));
        static::assertSame('Foo', $node->getFoo());
        static::assertSame('Bar', $node->bar);
        static::assertSame('Bar', $node->get('bar'));
        static::assertSame('Bar', $node->getBar());
    }

    /**
     * @test
     */
    public function thePropertiesCanBeAccessUsingMagicProperties()
    {
        $node = new Node(1, null, ['foo' => 'Foo', 'BAR' => 'Bar']);

        static::assertSame([], $node->children);
        static::assertSame('Foo', $node->foo);
        static::assertNull($node->parent);
    }

    /**
     * @test
     */
    public function anExceptionIsThrownWhenAccessingAnInexistentMagicProperty()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Undefined property');

        $node = new Node(1, null);
        $node->nosuchproperty;
    }

    /**
     * @test
     */
    public function thePropertiesCanBeFetchedAsAnArray()
    {
        $node = new Node('xyz', 456, ['foo' => 'bar', 'gggg' => 123]);
        static::assertEquals(['foo' => 'bar', 'gggg' => 123, 'id' => 'xyz', 'parent' => 456], $node->toArray());
    }

    /**
     * @test
     */
    public function whenSerializingANodeToJsonItsArrayRepresentationIsUsed()
    {
        $node = new Node('xyz', 456, ['foo' => 'bar', 'gggg' => 123]);
        static::assertEquals(
            '{"foo":"bar","gggg":123,"id":"xyz","parent":456}',
            json_encode($node)
        );
    }

    /**
     * @test
     */
    public function inScalarContextTheNodeIsTypecastedToItsId()
    {
        $node = new Node(123, null);
        static::assertEquals('123', (string) $node);
    }

    /**
     * @test
     */
    public function theLevelOfARootNodeIs0()
    {
        $node = new Node(0, null);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        static::assertSame(0, $node->getLevel());
    }

    /**
     * @test
     */
    public function aNode2LevelsBelowTheRootNodeHasLevel2()
    {
        $node = new Node(123, null);
        $parent = new Node(789, null);
        $rootNode = new Node(0, null);

        $parentProperty = new \ReflectionProperty(Node::class, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $rootNode);

        static::assertSame(2, $node->getLevel());
    }

    /**
     * @test
     */
    public function theRootNodesAncestorsIsAnEmptyArray()
    {
        $node = new Node(0, null);
        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        static::assertEquals([], $node->getAncestors());
    }

    /**
     * @test
     */
    public function aNodesAncestorsCanBeRetrieved()
    {
        $parentProperty = new \ReflectionProperty(Node::class, 'parent');
        $parentProperty->setAccessible(true);

        $node = new Node(3, null);
        $parent = new Node(2, null);
        $grandParent = new Node(1, null);
        $rootNode = new Node(0, null);

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);
        $parentProperty->setValue($grandParent, $rootNode);

        static::assertSame([$parent, $grandParent], $node->getAncestors());
    }

    /**
     * @test
     */
    public function aNodesAncestorsCanBeRetrievedIncludingTheNodeItself()
    {
        $parentProperty = new \ReflectionProperty(Node::class, 'parent');
        $parentProperty->setAccessible(true);

        $node = new Node(3, null);
        $parent = new Node(2, null);
        $grandParent = new Node(1, null);
        $rootNode = new Node(0, null);

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);
        $parentProperty->setValue($grandParent, $rootNode);

        static::assertSame([$node, $parent, $grandParent], $node->getAncestorsAndSelf());
    }

    /**
     * @test
     */
    public function aNodesDescendantsCanBeRetrieved()
    {
        $childrenProperty = new \ReflectionProperty(Node::class, 'children');
        $childrenProperty->setAccessible(true);

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

    /**
     * At the moment, this is an almost exact copy of test method
     * getANodesDescendantsIncludingTheNodeItself(). This will change when the
     * argument to getDescendants() is removed from the API.
     *
     * @test
     */
    public function aNodesDescendantsCanBeRetrievedIncludingTheNodeItself()
    {
        $childrenProperty = new \ReflectionProperty(Node::class, 'children');
        $childrenProperty->setAccessible(true);

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
}
