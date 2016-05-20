<?php

namespace BlueM\Tree;

require_once __DIR__ . '/../../lib/BlueM/Tree.php';
require_once __DIR__ . '/../../lib/BlueM/Tree/Node.php';

/**
 * @covers BlueM\Tree\Node
 */
class NodeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function getThePreviousSibling()
    {
        $node    = new Node(array('id' => 123));
        $sibling = new Node(array('id' => 456));

        $parent = new Node(array('id' => 789));
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($sibling, $node));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertSame($sibling, $node->getPrecedingSibling());
    }

    /**
     * @test
     */
    public function tryingToGetThePreviousSiblingReturnsNullWhenCalledOnTheFirstNode()
    {
        $node    = new Node(array('id' => 123));
        $parent  = new Node(array('id' => 789));

        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($node));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertNull($node->getPrecedingSibling());
    }

    /**
     * @test
     */
    public function getTheNextSibling()
    {
        $node    = new Node(array('id' => 123));
        $sibling = new Node(array('id' => 456));

        $parent           = new Node(array('id' => 789));
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($node, $sibling));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertSame($sibling, $node->getFollowingSibling());
    }

    /**
     * @test
     */
    public function getAllSiblings()
    {
        $node     = new Node(array('id' => 10));
        $sibling1 = new Node(array('id' => 20));
        $sibling2 = new Node(array('id' => 30));

        $parent           = new Node(array('id' => 333));
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($node, $sibling1, $sibling2));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertSame(
            array($sibling1, $sibling2),
            $node->getSiblings()
        );
    }

    /**
     * @test
     */
    public function gettingAllSiblingsReturnsTheSiblingsWhenMixedDataTypesAreUsedForTheIds()
    {
        $node     = new Node(array('id' => 0));
        $sibling1 = new Node(array('id' => 'a'));
        $sibling2 = new Node(array('id' => 30));

        $parent           = new Node(array('id' => 333));
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($node, $sibling1, $sibling2));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertSame(
            array($sibling1, $sibling2),
            $node->getSiblings()
        );
    }

    /**
     * @test
     */
    public function getTheSiblingsIncludingTheNodeItself()
    {
        $node     = new Node(array('id' => 10));
        $sibling1 = new Node(array('id' => 20));
        $sibling2 = new Node(array('id' => 30));

        $parent           = new Node(array('id' => 333));
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($sibling1, $node, $sibling2));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertSame(
            array($sibling1, $node, $sibling2),
            $node->getSiblings(true)
        );
    }

    /**
     * @test
     */
    public function getTheSiblingsAndSelf()
    {
        // Note: currently, this test is basically identical to getTheSiblingsIncludingTheNodeItself()
        $node     = new Node(array('id' => 10));
        $sibling1 = new Node(array('id' => 20));
        $sibling2 = new Node(array('id' => 30));

        $parent           = new Node(array('id' => 333));
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($sibling1, $node, $sibling2));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertSame(
            array($sibling1, $node, $sibling2),
            $node->getSiblingsAndSelf()
        );
    }

    /**
     * @test
     */
    public function getTheChildren()
    {
        $node1 = new Node(array('id' => 10));
        $node2 = new Node(array('id' => 20));
        $node3 = new Node(array('id' => 30));

        $parent           = new Node(array('id' => 333));
        $childrenProperty = new \ReflectionProperty($parent, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($parent, array($node1, $node2, $node3));

        $this->assertSame(array($node1, $node2, $node3), $parent->getChildren());
    }

    /**
     * @test
     */
    public function getChildrenReturnsEmptyArrayWhenNoChildNodesExist()
    {
        $parent = new Node(array('id' => 52));
        $this->assertSame(array(), $parent->getChildren());
    }

    /**
     * @test
     */
    public function getTheParentNode()
    {
        $node   = new Node(array('id' => 2));
        $parent = new Node(array('id' => 4));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);

        $this->assertSame($parent, $node->getParent());
    }

    /**
     * @test
     */
    public function tryingToGetTheParentReturnsNullForTheRootNode()
    {
        $node   = new Node(array('id' => 0));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        $this->assertNull($node->getParent());
    }

    /**
     * @test
     */
    public function getTheId()
    {
        $node = new Node(array('id' => 16));
        $this->assertEquals(16, $node->getId());
    }

    /**
     * @test
     */
    public function getAPropertyUsingMethodGet()
    {
        $node = new Node(array('id' => 16, 'key' => 'value'));
        $this->assertEquals('value', $node->get('key'));
    }

    /**
     * @test
     */
    public function getAPropertyUsingMagicMethod()
    {
        $node = new Node(array('id' => 16, 'key' => 'value'));
        $this->assertEquals('value', $node->getKey());
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Undefined property: key (Node ID: 1)
     */
    public function tryingToGetANonExistentPropertyUsingGetThrowsAnException()
    {
        $node = new Node(array('id' => 1));
        $this->assertEquals('value', $node->get('key'));
    }

    /**
     * @test
     * @expectedException BadFunctionCallException
     * @expectedExceptionMessage Invalid method getKey()
     */
    public function tryingToGetANonExistentPropertyUsingMagicMethodThrowsAnException()
    {
        $node = new Node(array('id' => 1));
        $this->assertEquals('value', $node->getKey());
    }

    /**
     * @test
     */
    public function theLevelOfARootNodeIs0()
    {
        $node = new Node(array('id' => 0));

        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        $this->assertSame(0, $node->getLevel());
    }

    /**
     * @test
     */
    public function getANodesLevel()
    {
        $node    = new Node(array('id' => 123));
        $parent  = new Node(array('id' => 789));

        $parentProperty = new \ReflectionProperty(__NAMESPACE__ . '\Node', 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, null);

        $this->assertSame(1, $node->getLevel());
    }

    /**
     * @test
     */
    public function getTheNumberOfChildren()
    {
        $node = new Node(array('id' => 10));

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($node, array('dummy1', 'dummy2'));

        $this->assertSame(2, $node->countChildren());
    }

    /**
     * @test
     */
    public function getWhetherTheNodeHasAnyChildren()
    {
        $node = new Node(array('id' => 10));

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        $childrenProperty->setValue($node, array('dummy1', 'dummy2'));

        $this->assertTrue($node->hasChildren());
    }

    /**
     * @test
     */
    public function getThePropertiesAsAnArray()
    {
        $properties = array('id' => 'xyz', 'foo' => 'bar', 'gggg' => 123);
        $node = new Node($properties);
        $this->assertEquals($properties, $node->toArray());
    }

    /**
     * @test
     */
    public function inScalarContextTheNodeIsTypecastedToItsId()
    {
        $properties = array('id' => 123);
        $node       = new Node($properties);
        $this->assertEquals('123', "$node");
    }

    /**
     * @test
     */
    public function addAChildToANode()
    {
        $node  = new Node(array('id' => 100));
        $child = new Node(array('id' => 200));

        $node->addChild($child);

        $childrenProperty = new \ReflectionProperty($node, 'children');
        $childrenProperty->setAccessible(true);
        $this->assertSame(array($child), $childrenProperty->getValue($node));

        $parentProperty = new \ReflectionProperty($child, 'parent');
        $parentProperty->setAccessible(true);
        $this->assertSame($node, $parentProperty->getValue($child));

        $propertiesProperty = new \ReflectionProperty($child, 'properties');
        $propertiesProperty->setAccessible(true);
        $this->assertSame(
            array('id' => 200, 'parent' => 100),
            $propertiesProperty->getValue($child)
        );
    }

    /**
     * @test
     */
    public function getTheRootNodeAncestors()
    {
        $node = new Node(array('id' => 0));
        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        $this->assertSame(array(), $node->getAncestors());
    }

    /**
     * @test
     */
    public function getTheRootNodeAncestorsIncludingTheNodeItself()
    {
        $node = new Node(array('id' => 0));
        $parentProperty = new \ReflectionProperty($node, 'parent');
        $parentProperty->setAccessible(true);
        $parentProperty->setValue($node, null);

        $this->assertSame(array($node), $node->getAncestors(true));
    }

    /**
     * @test
     */
    public function getANodesAncestors()
    {
        $parentProperty = new \ReflectionProperty(__NAMESPACE__ . '\Node', 'parent');
        $parentProperty->setAccessible(true);

        $node        = new Node(array('id' => 1));
        $parent      = new Node(array('id' => 2));
        $grandParent = new Node(array('id' => 0)); // Root node

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);

        $this->assertSame(array($parent, $grandParent), $node->getAncestors());
    }

    /**
     * @test
     */
    public function getANodesAncestorsIncludingTheNodeItself()
    {
        $parentProperty = new \ReflectionProperty(__NAMESPACE__ . '\Node', 'parent');
        $parentProperty->setAccessible(true);

        $node        = new Node(array('id' => 1));
        $parent      = new Node(array('id' => 2));
        $grandParent = new Node(array('id' => 0)); // Root node

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);

        $this->assertSame(array($node, $parent, $grandParent), $node->getAncestors(true));
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
        $parentProperty = new \ReflectionProperty(__NAMESPACE__ . '\Node', 'parent');
        $parentProperty->setAccessible(true);

        $node        = new Node(array('id' => 1));
        $parent      = new Node(array('id' => 2));
        $grandParent = new Node(array('id' => 0)); // Root node

        $parentProperty->setValue($node, $parent);
        $parentProperty->setValue($parent, $grandParent);

        $this->assertSame(
            array($node, $parent, $grandParent),
            $node->getAncestorsAndSelf()
        );
    }

    /**
     * @test
     */
    public function getANodesDescendants()
    {
        $childrenProperty = new \ReflectionProperty(__NAMESPACE__ . '\Node', 'children');
        $childrenProperty->setAccessible(true);

        $node        = new Node(array('id' => 1));
        $child1      = new Node(array('id' => 2));
        $child2      = new Node(array('id' => 3));
        $grandChild1 = new Node(array('id' => 4));
        $grandChild2 = new Node(array('id' => 5));

        $childrenProperty->setValue($node, array($child1, $child2));
        $childrenProperty->setValue($child1, array($grandChild1, $grandChild2));

        $this->assertSame(
            array($child1, $grandChild1, $grandChild2, $child2),
            $node->getDescendants()
        );
    }

    /**
     * @test
     */
    public function getANodesDescendantsIncludingTheNodeItself()
    {
        $childrenProperty = new \ReflectionProperty(__NAMESPACE__ . '\Node', 'children');
        $childrenProperty->setAccessible(true);

        $node        = new Node(array('id' => 1));
        $child1      = new Node(array('id' => 2));
        $child2      = new Node(array('id' => 3));
        $grandChild1 = new Node(array('id' => 4));
        $grandChild2 = new Node(array('id' => 5));

        $childrenProperty->setValue($node, array($child1, $child2));
        $childrenProperty->setValue($child1, array($grandChild1, $grandChild2));

        $this->assertSame(
            array($node, $child1, $grandChild1, $grandChild2, $child2),
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
        $childrenProperty = new \ReflectionProperty(__NAMESPACE__ . '\Node', 'children');
        $childrenProperty->setAccessible(true);

        $node        = new Node(array('id' => 1));
        $child1      = new Node(array('id' => 2));
        $child2      = new Node(array('id' => 3));
        $grandChild1 = new Node(array('id' => 4));
        $grandChild2 = new Node(array('id' => 5));

        $childrenProperty->setValue($node, array($child1, $child2));
        $childrenProperty->setValue($child1, array($grandChild1, $grandChild2));

        $this->assertSame(
            array($node, $child1, $grandChild1, $grandChild2, $child2),
            $node->getDescendantsAndSelf(true)
        );
    }

    /**
     * @test
     */
    public function getReturnsTheExpectedResults()
    {
        $node = new Node(
            array(
                'id'  => 1,
                'foo' => 'Foo',
                'BAR' => 'Bar',
            )
        );

        $this->assertSame(array(), $node->children);
        $this->assertSame(null,    $node->parent);
        $this->assertSame('Foo',   $node->foo);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Undefined property
     */
    public function getThrowsAnExceptionIfThePropertyIsInvalid()
    {
        $node = new Node(array('id'  => 1));

        $node->nosuchproperty;
    }

    /**
     * @test
     */
    public function issetReturnsTheExpectedResults()
    {
        $node = new Node(
            array(
                'id'  => 1,
                'foo' => 'Foo',
                'BAR' => 'Bar',
            )
        );

        $this->assertTrue(isset($node->foo));
        $this->assertTrue(isset($node->FOO));
        $this->assertTrue(isset($node->bar));
        $this->assertTrue(isset($node->BAR));
        $this->assertTrue(isset($node->children));
        $this->assertTrue(isset($node->parent));
    }

    /**
     * @test
     */
    public function nodePropertiesAreHandledCaseInsensitively()
    {
        $node = new Node(
            array(
                'id'  => 1,
                'foo' => 'Foo',
                'BAR' => 'Bar',
            )
        );

        $this->assertSame('Foo', $node->foo);
        $this->assertSame('Foo', $node->get('foo'));
        $this->assertSame('Foo', $node->getFoo());
        $this->assertSame('Bar', $node->bar);
        $this->assertSame('Bar', $node->get('bar'));
        $this->assertSame('Bar', $node->getBar());
    }
}
