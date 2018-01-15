<?php

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
    public function getThePreviousSibling()
    {
        $node = new Node(['id' => 123]);
        $sibling = new Node(['id' => 456]);

        $parent = new Node(['id' => 789]);
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
        $node = new Node(['id' => 123]);
        $parent = new Node(['id' => 789]);

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
    public function getTheNextSibling()
    {
        $node = new Node(['id' => 123]);
        $sibling = new Node(['id' => 456]);

        $parent = new Node(['id' => 789]);
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
    public function getAllSiblings()
    {
        $node = new Node(['id' => 10]);
        $sibling1 = new Node(['id' => 20]);
        $sibling2 = new Node(['id' => 30]);

        $parent = new Node(['id' => 333]);
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
    public function gettingAllSiblingsReturnsTheSiblingsWhenMixedDataTypesAreUsedForTheIds()
    {
        $node = new Node(['id' => 0]);
        $sibling1 = new Node(['id' => 'a']);
        $sibling2 = new Node(['id' => 30]);

        $parent = new Node(['id' => 333]);
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
    public function getTheSiblingsIncludingTheNodeItself()
    {
        $node = new Node(['id' => 10]);
        $sibling1 = new Node(['id' => 20]);
        $sibling2 = new Node(['id' => 30]);

        $parent = new Node(['id' => 333]);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$sibling1, $node, $sibling2]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        static::assertSame(
            [$sibling1, $node, $sibling2],
            $node->getSiblings(true)
        );
    }

    /**
     * @test
     */
    public function getTheSiblingsAndSelf()
    {
        // Note: currently, this test is basically identical to getTheSiblingsIncludingTheNodeItself()
        $node = new Node(['id' => 10]);
        $sibling1 = new Node(['id' => 20]);
        $sibling2 = new Node(['id' => 30]);

        $parent = new Node(['id' => 333]);
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
    public function getTheChildren()
    {
        $node1 = new Node(['id' => 10]);
        $node2 = new Node(['id' => 20]);
        $node3 = new Node(['id' => 30]);

        $parent = new Node(['id' => 333]);
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, [$node1, $node2, $node3]);

        static::assertSame([$node1, $node2, $node3], $parent->getChildren());
    }

    /**
     * @test
     */
    public function getChildrenReturnsEmptyArrayWhenNoChildNodesExist()
    {
        $parent = new Node(['id' => 52]);
        static::assertSame([], $parent->getChildren());
    }

    /**
     * @test
     */
    public function getTheParentNode()
    {
        $node = new Node(['id' => 2]);
        $parent = new Node(['id' => 4]);

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
        $node = new Node(['id' => 0]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        static::assertNull($node->getParent());
    }

    /**
     * @test
     */
    public function getTheId()
    {
        $node = new Node(['id' => 16]);
        static::assertEquals(16, $node->getId());
    }

    /**
     * @test
     */
    public function getAPropertyUsingMethodGet()
    {
        $node = new Node(['id' => 16, 'key' => 'value']);
        static::assertEquals('value', $node->get('key'));
    }

    /**
     * @test
     */
    public function getAPropertyUsingMagicMethod()
    {
        $node = new Node(['id' => 16, 'key' => 'value']);
        static::assertEquals('value', $node->getKey());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Undefined property: key (Node ID: 1)
     */
    public function tryingToGetANonExistentPropertyUsingGetThrowsAnException()
    {
        $node = new Node(['id' => 1]);
        static::assertEquals('value', $node->get('key'));
    }

    /**
     * @test
     * @expectedException \BadFunctionCallException
     * @expectedExceptionMessage Invalid method getKey()
     */
    public function tryingToGetANonExistentPropertyUsingMagicMethodThrowsAnException()
    {
        $node = new Node(['id' => 1]);
        static::assertEquals('value', $node->getKey());
    }

    /**
     * @test
     */
    public function theLevelOfARootNodeIs0()
    {
        $node = new Node(['id' => 0]);

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        static::assertSame(0, $node->getLevel());
    }

    /**
     * @test
     */
    public function getANodesLevel()
    {
        $node = new Node(['id' => 123]);
        $parent = new Node(['id' => 789]);

        $parentProperty = new \ReflectionProperty(__NAMESPACE__.'\Node', 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, null);

        static::assertSame(1, $node->getLevel());
    }

    /**
     * @test
     */
    public function getTheNumberOfChildren()
    {
        $node = new Node(['id' => 10]);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertSame(2, $node->countChildren());
    }

    /**
     * @test
     */
    public function getWhetherTheNodeHasAnyChildren()
    {
        $node = new Node(['id' => 10]);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($node, ['dummy1', 'dummy2']);

        static::assertTrue($node->hasChildren());
    }

    /**
     * @test
     */
    public function getThePropertiesAsAnArray()
    {
        $properties = ['id' => 'xyz', 'foo' => 'bar', 'gggg' => 123];
        $node = new Node($properties);
        static::assertEquals($properties, $node->toArray());
    }

    /**
     * @test
     */
    public function inScalarContextTheNodeIsTypecastedToItsId()
    {
        $properties = ['id' => 123];
        $node = new Node($properties);
        static::assertEquals('123', "$node");
    }

    /**
     * @test
     */
    public function addAChildToANode()
    {
        $node = new Node(['id' => 100]);
        $child = new Node(['id' => 200]);

        $node->addChild($child);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        static::assertSame([$child], $childrenProperty->getValue($node));

        $parentProperty = new \ReflectionProperty($child, 'parent');
        $parentProperty->setAccessible(true);
        static::assertSame($node, $parentProperty->getValue($child));

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
    public function getTheRootNodeAncestors()
    {
        $node = new Node(['id' => 0]);
        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        static::assertSame([], $node->getAncestors());
    }

    /**
     * @test
     */
    public function getTheRootNodeAncestorsIncludingTheNodeItself()
    {
        $node = new Node(['id' => 0]);
        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        static::assertSame([$node], $node->getAncestors(true));
    }

    /**
     * @test
     */
    public function getANodesAncestors()
    {
        $parentProperty = new \ReflectionProperty(__NAMESPACE__.'\Node', 'parent');
        $parentProperty->setAccessible(true);

        $node = new Node(['id' => 1]);
        $parent = new Node(['id' => 2]);
        $grandParent = new Node(['id' => 0]); // Root node

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);

        static::assertSame([$parent, $grandParent], $node->getAncestors());
    }

    /**
     * @test
     */
    public function getANodesAncestorsIncludingTheNodeItself()
    {
        $parentProperty = new \ReflectionProperty(__NAMESPACE__.'\Node', 'parent');
        $parentProperty->setAccessible(true);

        $node = new Node(['id' => 1]);
        $parent = new Node(['id' => 2]);
        $grandParent = new Node(['id' => 0]); // Root node

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);

        static::assertSame([$node, $parent, $grandParent], $node->getAncestors(true));
    }

    /**
     * At the moment, this is an almost exact copy of test method
     * getANodesAncestorsIncludingTheNodeItself(). This will change when the argument
     * to method getAncestors() will be removed from the API.
     *
     * @test
     */
    public function getANodesAncestorsAndSelf()
    {
        $parentProperty = new \ReflectionProperty(__NAMESPACE__.'\Node', 'parent');
        $parentProperty->setAccessible(true);

        $node = new Node(['id' => 1]);
        $parent = new Node(['id' => 2]);
        $grandParent = new Node(['id' => 0]); // Root node

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);

        static::assertSame(
            [$node, $parent, $grandParent],
            $node->getAncestorsAndSelf()
        );
    }

    /**
     * @test
     */
    public function getANodesDescendants()
    {
        $childrenProperty = new \ReflectionProperty(__NAMESPACE__.'\Node', 'children');
        $childrenProperty->setAccessible(true);

        $node = new Node(['id' => 1]);
        $child1 = new Node(['id' => 2]);
        $child2 = new Node(['id' => 3]);
        $grandChild1 = new Node(['id' => 4]);
        $grandChild2 = new Node(['id' => 5]);

        $childrenProperty->setValue($node, [$child1, $child2]);
        $childrenProperty->setValue($child1, [$grandChild1, $grandChild2]);

        static::assertSame(
            [$child1, $grandChild1, $grandChild2, $child2],
            $node->getDescendants()
        );
    }

    /**
     * @test
     */
    public function getANodesDescendantsIncludingTheNodeItself()
    {
        $childrenProperty = new \ReflectionProperty(__NAMESPACE__.'\Node', 'children');
        $childrenProperty->setAccessible(true);

        $node = new Node(['id' => 1]);
        $child1 = new Node(['id' => 2]);
        $child2 = new Node(['id' => 3]);
        $grandChild1 = new Node(['id' => 4]);
        $grandChild2 = new Node(['id' => 5]);

        $childrenProperty->setValue($node, [$child1, $child2]);
        $childrenProperty->setValue($child1, [$grandChild1, $grandChild2]);

        static::assertSame(
            [$node, $child1, $grandChild1, $grandChild2, $child2],
            $node->getDescendants(true)
        );
    }

    /**
     * At the moment, this is an almost exact copy of test method
     * getANodesDescendantsIncludingTheNodeItself(). This will change when the
     * argument to getDescendants() is removed from the API.
     *
     * @test
     */
    public function getANodesDescendantsAndSelf()
    {
        $childrenProperty = new \ReflectionProperty(__NAMESPACE__.'\Node', 'children');
        $childrenProperty->setAccessible(true);

        $node = new Node(['id' => 1]);
        $child1 = new Node(['id' => 2]);
        $child2 = new Node(['id' => 3]);
        $grandChild1 = new Node(['id' => 4]);
        $grandChild2 = new Node(['id' => 5]);

        $childrenProperty->setValue($node, [$child1, $child2]);
        $childrenProperty->setValue($child1, [$grandChild1, $grandChild2]);

        static::assertSame(
            [$node, $child1, $grandChild1, $grandChild2, $child2],
            $node->getDescendantsAndSelf(true)
        );
    }

    /**
     * @test
     */
    public function getReturnsTheExpectedResults()
    {
        $node = new Node([
            'id'  => 1,
            'foo' => 'Foo',
            'BAR' => 'Bar',
        ]);

        static::assertSame([], $node->children);
        static::assertSame('Foo', $node->foo);
        static::assertNull($node->parent);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Undefined property
     */
    public function getThrowsAnExceptionIfThePropertyIsInvalid()
    {
        $node = new Node(['id' => 1]);

        $node->nosuchproperty;
    }

    /**
     * @test
     */
    public function issetReturnsTheExpectedResults()
    {
        $node = new Node([
            'id'  => 1,
            'foo' => 'Foo',
            'BAR' => 'Bar',
        ]);

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
        $node = new Node([
            'id'  => 1,
            'foo' => 'Foo',
            'BAR' => 'Bar',
        ]);

        static::assertSame('Foo', $node->foo);
        static::assertSame('Foo', $node->get('foo'));
        static::assertSame('Foo', $node->getFoo());
        static::assertSame('Bar', $node->bar);
        static::assertSame('Bar', $node->get('bar'));
        static::assertSame('Bar', $node->getBar());
    }
}
