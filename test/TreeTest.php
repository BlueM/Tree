<?php

namespace BlueM;

require_once __DIR__ . '/../lib/BlueM/Tree.php';
require_once __DIR__ . '/../lib/BlueM/Tree/Node.php';
require_once __DIR__ . '/../lib/BlueM/Tree/InvalidParentException.php';

/**
 * Tests for BlueM\Tree. These are not really unit tests, as they test the class
 * including BlueM\Tree\Node as a whole.
 *
 * @covers BlueM\Tree
 */
class TreeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param bool $sorted
     *
     * @return array
     */
    protected static function dataWithNumericKeys($sorted = true)
    {
        $data = array(
            array('id' => 1,  'parent' => 0,  'name' =>'Europe'),
            array('id' => 3,  'parent' => 0,  'name' =>'America'),
            array('id' => 4,  'parent' => 0,  'name' =>'Asia'),
            array('id' => 5,  'parent' => 0,  'name' =>'Africa'),
            array('id' => 6,  'parent' => 0,  'name' =>'Australia'),
            // --
            array('id' => 7,  'parent' => 1,  'name' =>'Germany'),
            array('id' => 10, 'parent' => 1,  'name' =>'Portugal'),
            // --
            array('id' => 11, 'parent' => 7,  'name' =>'Hamburg'),
            array('id' => 12, 'parent' => 7,  'name' =>'Munich'),
            array('id' => 15, 'parent' => 7,  'name' =>'Berlin'),
            // --
            array('id' => 20, 'parent' => 10, 'name' => 'Lisbon'),
            // --
            array('id' => 27, 'parent' => 11, 'name' => 'Eimsbüttel'),
            array('id' => 21, 'parent' => 11, 'name' => 'Altona'),
        );

        if ($sorted) {
            usort(
                $data,
                function($a, $b) {
                    if ($a['name'] < $b['name']) {
                        return -1;
                    }
                    if ($a['name'] > $b['name']) {
                        return 1;
                    }
                    return 0;
                }
            );
        }

        return $data;
    }

    /**
     * @param bool $sorted
     *
     * @return array
     */
    protected static function dataWithStringKeys($sorted = true)
    {
        $data = array(
            array('id' => 'vehicle',        'parent' => ''),
            array('id' => 'bicycle',        'parent' => 'vehicle'),
            array('id' => 'car',            'parent' => 'vehicle'),
            array('id' => 'building',       'parent' => ''),
            array('id' => 'school',         'parent' => 'building'),
            array('id' => 'library',        'parent' => 'building'),
            array('id' => 'primary-school', 'parent' => 'school'),
        );

        if ($sorted) {
            usort(
                $data,
                function($a, $b) {
                    if ($a['id'] < $b['id']) {
                        return -1;
                    }
                    if ($a['id'] > $b['id']) {
                        return 1;
                    }
                    return 0;
                }
            );
        }

        return $data;
    }

    /**
     * @test
     */
    public function getTheRootNodes()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);

        $nodes = $tree->getRootNodes();
        $this->assertInternalType('array', $nodes);
        $this->assertCount(5, $nodes);

        $expectedOrder = array(5, 3, 4, 6, 1);

        for ($i = 0, $ii = count($nodes); $i < $ii; $i++) {
            $this->assertInstanceOf(__NAMESPACE__ . '\Tree\Node', $nodes[$i]);
            $this->assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @test
     */
    public function getAllNodes()
    {
        $data  = self::dataWithNumericKeys();
        $tree  = new Tree($data);
        $nodes = $tree->getNodes();

        $this->assertInternalType('array', $nodes);
        $this->assertSame(count($data), count($nodes));

        $expectedOrder = array(5, 3, 4, 6, 1, 7, 15, 11, 21, 27, 12, 10, 20);

        for ($i = 0, $ii = count($nodes); $i < $ii; $i++) {
            $this->assertInstanceOf(__NAMESPACE__ . '\Tree\Node', $nodes[$i]);
            $this->assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @test
     */
    public function getANodeByItsId()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);
        $node = $tree->getNodeById(20);
        $this->assertEquals(20, $node->getId());
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function tryingToGetANodeByItsIdThrowsAnExceptionIfTheIdIsInvalid()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);
        $tree->getNodeById(999);
    }

    /**
     * @test
     */
    public function getANodeByItsValuePath()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);
        $this->assertEquals(
            $tree->getNodeById(11),
            $tree->getNodeByValuePath('name', array('Europe', 'Germany', 'Hamburg'))
        );
    }

    /**
     * @test
     */
    public function tryingToGetANodeByItsValuePathReturnsNullIfNoNodeMatches()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);
        $this->assertEquals(
            null,
            $tree->getNodeByValuePath('name', array('Europe', 'Germany', 'Frankfurt'))
        );
    }

    /**
     * @test
     */
    public function theTreeIsReturnedAsStringInScalarContext()
    {
        $data     = self::dataWithNumericKeys();
        $tree     = new Tree($data);
        $actual   = "$tree";
        $expected = <<<'EXPECTED'
- 5
- 3
- 4
- 6
- 1
  - 7
    - 15
    - 11
      - 21
      - 27
    - 12
  - 10
    - 20
EXPECTED;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getTheRootNodesForDataWithStringKeys()
    {
        $data = self::dataWithStringKeys();
        $tree = new Tree($data, array('rootId' => ''));

        $nodes = $tree->getRootNodes();
        $this->assertInternalType('array', $nodes);

        $expectedOrder = array('building', 'vehicle');

        for ($i = 0, $ii = count($nodes); $i < $ii; $i++) {
            $this->assertInstanceOf(__NAMESPACE__ . '\Tree\Node', $nodes[$i]);
            $this->assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @test
     */
    public function getAllNodesForDataWithStringKeys()
    {
        $data = self::dataWithStringKeys();
        $tree = new Tree($data, array('rootId' => ''));

        $nodes = $tree->getNodes();
        $this->assertInternalType('array', $nodes);
        $this->assertSame(count($data), count($nodes));

        $expectedOrder = array(
            'building', 'library', 'school', 'primary-school', 'vehicle', 'bicycle', 'car'
        );

        for ($i = 0, $ii = count($nodes); $i < $ii; $i++) {
            $this->assertInstanceOf(__NAMESPACE__ . '\Tree\Node', $nodes[$i]);
            $this->assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @test
     */
    public function getANodeByItsIdForDataWithStringKeys()
    {
        $data = self::dataWithStringKeys();
        $tree = new Tree($data, array('rootId' => ''));
        $node = $tree->getNodeById('library');
        $this->assertEquals('library', $node->getId());
    }

    /**
     * @test
     * @expectedException \Bluem\Tree\InvalidParentException
     * @expectedExceptionMessage 123 points to non-existent parent with ID 456
     */
    public function anExceptionIsThrownWhenAnInvalidParentIdIsReferenced()
    {
        new Tree(
            array(
                array('id' => 123, 'parent' => 456)
            )
        );
    }

    /**
     * @test
     * @expectedException \Bluem\Tree\InvalidParentException
     * @expectedExceptionMessage 678 references its own ID as parent
     */
    public function anExceptionIsThrownWhenANodeWouldBeItsOwnParent()
    {
        new Tree(
            array(
                array('id' => 123, 'parent' => 0),
                array('id' => 678, 'parent' => 678),
            )
        );
    }

    /**
     * @test
     * @ticket 3
     * @expectedException \Bluem\Tree\InvalidParentException
     * @expectedExceptionMessage references its own ID as parent
     */
    public function anExceptionIsThrownWhenANodeWouldBeItsOwnParentWhenOwnIdAndParentIdHaveDifferentTypes()
    {
        new Tree(
            array(
                array('id' => '5', 'parent' => 5),
            )
        );
    }

    /**
     * @test
     * @ticket 3
     */
    public function whenMixingNumericAndStringIdsNoExceptionIsThrownDueToImplicitTypecasting()
    {
        new Tree(
            array(
                array('id' => 'foo', 'parent' => 0)
            )
        );
    }
}
