<?php

namespace BlueM;

use BlueM\Tree\Node;
use PHPUnit\Framework\TestCase;

/**
 * Tests for BlueM\Tree.
 *
 * These are not really unit tests, as they test the class including
 * BlueM\Tree\Node as a whole.
 *
 * @covers \BlueM\Tree
 */
class TreeTest extends TestCase
{
    /**
     * @test
     */
    public function getTheRootNodes()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);

        $nodes = $tree->getRootNodes();
        static::assertInternalType('array', $nodes);
        static::assertCount(5, $nodes);

        $expectedOrder = [5, 3, 4, 6, 1];

        for ($i = 0, $ii = \count($nodes); $i < $ii; $i++) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @test
     */
    public function getAllNodes()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);
        $nodes = $tree->getNodes();

        static::assertInternalType('array', $nodes);
        static::assertSame(\count($data), \count($nodes));

        $expectedOrder = [5, 3, 4, 6, 1, 7, 15, 11, 21, 27, 12, 10, 20];

        for ($i = 0, $ii = \count($nodes); $i < $ii; $i++) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
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
        static::assertEquals(20, $node->getId());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
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
        static::assertEquals(
            $tree->getNodeById(11),
            $tree->getNodeByValuePath('name', ['Europe', 'Germany', 'Hamburg'])
        );
    }

    /**
     * @test
     */
    public function tryingToGetANodeByItsValuePathReturnsNullIfNoNodeMatches()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);
        static::assertEquals(
            null,
            $tree->getNodeByValuePath('name', ['Europe', 'Germany', 'Frankfurt'])
        );
    }

    /**
     * @test
     */
    public function theTreeIsReturnedAsStringInScalarContext()
    {
        $data = self::dataWithNumericKeys();
        $tree = new Tree($data);
        $actual = "$tree";
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

        static::assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getTheRootNodesForDataWithStringKeys()
    {
        $data = self::dataWithStringKeys();
        $tree = new Tree($data, ['rootId' => '']);

        $nodes = $tree->getRootNodes();
        static::assertInternalType('array', $nodes);

        $expectedOrder = ['building', 'vehicle'];

        for ($i = 0, $ii = \count($nodes); $i < $ii; $i++) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @test
     */
    public function getAllNodesForDataWithStringKeys()
    {
        $data = self::dataWithStringKeys();
        $tree = new Tree($data, ['rootId' => '']);

        $nodes = $tree->getNodes();
        static::assertInternalType('array', $nodes);
        static::assertSame(\count($data), \count($nodes));

        $expectedOrder = [
            'building', 'library', 'school', 'primary-school', 'vehicle', 'bicycle', 'car',
        ];

        for ($i = 0, $ii = \count($nodes); $i < $ii; $i++) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @test
     */
    public function getANodeByItsIdForDataWithStringKeys()
    {
        $data = self::dataWithStringKeys();
        $tree = new Tree($data, ['rootId' => '']);
        $node = $tree->getNodeById('library');
        static::assertEquals('library', $node->getId());
    }

    /**
     * @test
     * @expectedException \Bluem\Tree\InvalidParentException
     * @expectedExceptionMessage 123 points to non-existent parent with ID 456
     */
    public function anExceptionIsThrownWhenAnInvalidParentIdIsReferenced()
    {
        new Tree(
            [
                ['id' => 123, 'parent' => 456],
            ]
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
            [
                ['id' => 123, 'parent' => 0],
                ['id' => 678, 'parent' => 678],
            ]
        );
    }

    /**
     * @test
     * @ticket                   3
     * @expectedException \Bluem\Tree\InvalidParentException
     * @expectedExceptionMessage references its own ID as parent
     */
    public function anExceptionIsThrownWhenANodeWouldBeItsOwnParentWhenOwnIdAndParentIdHaveDifferentTypes()
    {
        new Tree(
            [
                ['id' => '5', 'parent' => 5],
            ]
        );
    }

    /**
     * @test
     * @ticket 3
     */
    public function whenMixingNumericAndStringIdsNoExceptionIsThrownDueToImplicitTypecasting()
    {
        new Tree([
            ['id' => 'foo', 'parent' => 0],
        ]);
        static::assertTrue(true); // Just to make PHPUnit happy
    }

    /**
     * @param bool $sorted
     *
     * @return array
     */
    private static function dataWithNumericKeys($sorted = true): array
    {
        $data = [
            ['id' => 1, 'parent' => 0, 'name' => 'Europe'],
            ['id' => 3, 'parent' => 0, 'name' => 'America'],
            ['id' => 4, 'parent' => 0, 'name' => 'Asia'],
            ['id' => 5, 'parent' => 0, 'name' => 'Africa'],
            ['id' => 6, 'parent' => 0, 'name' => 'Australia'],
            // --
            ['id' => 7, 'parent' => 1, 'name' => 'Germany'],
            ['id' => 10, 'parent' => 1, 'name' => 'Portugal'],
            // --
            ['id' => 11, 'parent' => 7, 'name' => 'Hamburg'],
            ['id' => 12, 'parent' => 7, 'name' => 'Munich'],
            ['id' => 15, 'parent' => 7, 'name' => 'Berlin'],
            // --
            ['id' => 20, 'parent' => 10, 'name' => 'Lisbon'],
            // --
            ['id' => 27, 'parent' => 11, 'name' => 'Eimsbüttel'],
            ['id' => 21, 'parent' => 11, 'name' => 'Altona'],
        ];

        if ($sorted) {
            usort(
                $data,
                function ($a, $b) {
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
    private static function dataWithStringKeys($sorted = true): array
    {
        $data = [
            ['id' => 'vehicle', 'parent' => ''],
            ['id' => 'bicycle', 'parent' => 'vehicle'],
            ['id' => 'car', 'parent' => 'vehicle'],
            ['id' => 'building', 'parent' => ''],
            ['id' => 'school', 'parent' => 'building'],
            ['id' => 'library', 'parent' => 'building'],
            ['id' => 'primary-school', 'parent' => 'school'],
        ];

        if ($sorted) {
            usort(
                $data,
                function ($a, $b) {
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
}
