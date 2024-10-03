<?php

namespace BlueM;

use BlueM\Helper\IterableObjectFactory;
use BlueM\Tree\Exception\InvalidParentException;
use BlueM\Tree\Exception\MissingNodeInvalidParentException;
use BlueM\Tree\Node;
use BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;

class TreeTest extends TestCase
{
    #[Test]
    public function anExceptionIsThrownIfANonScalarValueShouldBeUsedAsRootId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “rootid” must be scalar or null');

        new Tree([], ['rootId' => []]);
    }

    #[Test]
    public function anExceptionIsThrownIfANonStringValueShouldBeUsedAsIdFieldName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “id” must be a string');

        new Tree([], ['id' => 123]);
    }

    #[Test]
    public function anExceptionIsThrownIfANonStringValueShouldBeUsedAsParentIdFieldName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “parent” must be a string');

        new Tree([], ['parent' => $this]);
    }

    #[Test]
    public function anExceptionIsThrownIfANonObjectShouldBeUsedAsSerializer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “jsonSerializer” must be an object');

        new Tree([], ['jsonSerializer' => 'not an object']);
    }

    #[Test]
    public function theSerializerCanBeSetToAnObjectImplementingSerializerinterface(): void
    {
        $serializer = new HierarchicalTreeJsonSerializer();

        $subject = new Tree([], ['jsonSerializer' => $serializer]);

        $serializerProperty = new \ReflectionProperty($subject, 'jsonSerializer');

        static::assertSame($serializer, $serializerProperty->getValue($subject));
    }

    #[Test]
    public function nullCanBeUsedAsParentId(): void
    {
        $data = [
            ['id' => 1, 'parent' => null, 'name' => 'Root'],
            ['id' => 2, 'parent' => 1, 'name' => 'Child'],
            ['id' => 3, 'parent' => 2, 'name' => 'Grandchild'],
            ['id' => 4, 'parent' => null, 'name' => 'Root'],
        ];

        $tree = new Tree($data, ['rootId' => null]);

        $nodes = $tree->getNodes();
        static::assertCount(4, $nodes);
        static::assertSame(1, $nodes[0]->getId());
        static::assertSame(2, $nodes[1]->getId());
        static::assertSame(3, $nodes[2]->getId());
        static::assertSame(4, $nodes[3]->getId());

        static::assertSame($nodes[0], $nodes[1]->getParent());
        static::assertNull($nodes[0]->getParent()->getId());
    }

    #[Test]
    public function theRootNodesCanBeRetrieved(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);

        $nodes = $tree->getRootNodes();
        static::assertCount(5, $nodes);

        $expectedOrder = [5, 3, 4, 6, 1];

        for ($i = 0, $ii = \count($nodes); $i < $ii; ++$i) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    #[Test]
    public function theRootNodesCanBeRetrievedWhenTheIdsAreStrings(): void
    {
        $data = self::dummyDataWithStringKeys();
        $tree = new Tree($data, ['rootId' => '']);

        $nodes = $tree->getRootNodes();

        $expectedOrder = ['building', 'vehicle'];

        for ($i = 0, $ii = \count($nodes); $i < $ii; ++$i) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    #[Test]
    public function theTreeCanBeRebuiltFromNewData(): void
    {
        $data = self::dummyDataWithNumericKeys();

        $tree = new Tree($data);
        $originalData = json_encode($tree);

        for ($i = 0; $i < 3; ++$i) {
            $tree->rebuildWithData($data);
            static::assertSame($originalData, json_encode($tree));
        }
    }

    #[Test]
    public function aTreeCanBeCreatedFromAnIterable(): void
    {
        $tree = new Tree(
            IterableObjectFactory::makeIterableInstance(
                [
                    ['id' => 1, 'parent' => 0],
                    ['id' => 2, 'parent' => 0],
                    ['id' => 3, 'parent' => 2],
                    ['id' => 4, 'parent' => 0],
                ]
            )
        );
        static::assertSame('[{"id":1,"parent":0},{"id":2,"parent":0},{"id":3,"parent":2},{"id":4,"parent":0}]', json_encode($tree));
    }

    #[Test]
    public function aTreeCanBeCreatedFromAnArrayOfObjectsImplementingIterator(): void
    {
        $tree = new Tree([
            IterableObjectFactory::makeIterableInstance(['id' => 1, 'parent' => 0, 'title' => 'A']),
            IterableObjectFactory::makeIterableInstance(['id' => 2, 'parent' => 0, 'title' => 'B']),
            IterableObjectFactory::makeIterableInstance(['id' => 3, 'parent' => 2, 'title' => 'B-1']),
            IterableObjectFactory::makeIterableInstance(['id' => 4, 'parent' => 0, 'title' => 'D']),
        ]);

        static::assertSame(
            '[{"id":1,"parent":0,"title":"A"},{"id":2,"parent":0,"title":"B"},{"id":3,"parent":2,"title":"B-1"},{"id":4,"parent":0,"title":"D"}]',
            json_encode($tree)
        );
    }

    #[Test]
    public function theTreeCanBeSerializedToAJsonRepresentationFromWhichATreeWithTheSameDataCanBeBuiltWhenDecoded(): void
    {
        $data = self::dummyDataWithNumericKeys();

        $tree1 = new Tree($data);
        $tree1Json = json_encode($tree1);
        $tree1JsonDecoded = json_decode($tree1Json, true);

        static::assertCount(\count($data), $tree1JsonDecoded);
        foreach ($data as $nodeData) {
            ksort($nodeData);
            // Note: static::assertContains() fails
            /* @noinspection PhpUnitTestsInspection */
            static::assertTrue(in_array($nodeData, $data));
        }

        $tree2 = new Tree($tree1JsonDecoded);
        $tree2Json = json_encode($tree2);

        static::assertSame($tree1Json, $tree2Json);
    }

    #[Test]
    public function allNodesCanBeRetrieved(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);

        $nodes = $tree->getNodes();
        static::assertCount(\count($data), $nodes);

        $expectedOrder = [5, 3, 4, 6, 1, 7, 15, 11, 21, 27, 12, 10, 20];

        for ($i = 0, $ii = \count($nodes); $i < $ii; ++$i) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    #[Test]
    public function allNodesCanBeRetrievedWhenNodeIdsAreStrings(): void
    {
        $data = self::dummyDataWithStringKeys();
        $tree = new Tree($data, ['rootId' => '']);

        $nodes = $tree->getNodes();
        static::assertCount(\count($data), $nodes);

        $expectedOrder = [
            'building', 'library', 'school', 'primary-school', 'vehicle', 'bicycle', 'car',
        ];

        for ($i = 0, $ii = \count($nodes); $i < $ii; ++$i) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    #[Test]
    public function aNodeCanBeAccessedByItsIntegerId(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        $node = $tree->getNodeById(20);
        static::assertEquals(20, $node->getId());
    }

    #[Test]
    public function aNodeCanBeAccessedByItsStringId(): void
    {
        $data = self::dummyDataWithStringKeys();
        $tree = new Tree($data, ['rootId' => '']);
        $node = $tree->getNodeById('library');
        static::assertEquals('library', $node->getId());
    }

    #[Test]
    public function tryingToGetANodeByItsIdThrowsAnExceptionIfTheIdIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid node primary key 999');

        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        $tree->getNodeById(999);
    }

    #[Test]
    public function aNodeCanBeAccessedByItsValuePath(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        static::assertEquals(
            $tree->getNodeById(11),
            $tree->getNodeByValuePath('name', ['Europe', 'Germany', 'Hamburg'])
        );
    }

    #[Test]
    public function tryingToGetANodeByItsValuePathReturnsNullIfNoNodeMatches(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        static::assertEquals(
            null,
            $tree->getNodeByValuePath('name', ['Europe', 'Germany', 'Frankfurt'])
        );
    }

    #[Test]
    public function inScalarContextTheTreeIsReturnedAsAString(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        $actual = (string) $tree;
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

    #[Test]
    public function anExceptionIsThrownWhenAnInvalidParentIdIsReferenced(): void
    {
        $this->expectException(InvalidParentException::class);
        $this->expectExceptionMessage('123 points to non-existent parent with ID 456');

        new Tree([
            ['id' => 123, 'parent' => 456],
        ]);
    }

    #[Test]
    #[TestDox('Build warning callback: a custom callback can be used, which is called with an exception and the tree instance as arguments')]
    #[Ticket('https://github.com/BlueM/Tree/issues/26')]
    public function aCustomBuildWarningCallbackCanBeSpecifiedWhichIsCalledWithNodeAndParentIdAsArgument(): void
    {
        $invocationCount = 0;
        $treeArg = null;
        $buildwarningcallback = function (MissingNodeInvalidParentException $exception, Tree $tree, Node $node, mixed $parentId) use (&$invocationCount, &$treeArg) {
            ++$invocationCount;
            static::assertSame('Node with ID 2 points to non-existent parent with empty parent ID', $exception->getMessage());
            $treeArg = $tree;
            static::assertSame(2, $node->getId());
            static::assertSame('', $parentId);
        };

        $tree = new Tree(
            [
                ['id' => 1, 'parent' => 0],
                ['id' => 2, 'parent' => ''],
            ],
            [
                'buildwarningcallback' => $buildwarningcallback,
            ]
        );

        static::assertSame(1, $invocationCount);
        static::assertSame($tree, $treeArg);
    }

    #[Test]
    public function anExceptionIsThrownIfTheBuildWarningCallbackOptionIsNotACallable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “buildWarningCallback” must be a callable');

        new Tree(
            [
                ['id' => 1, 'parent' => 0],
                ['id' => 2, 'parent' => ''],
            ],
            [
                'buildwarningcallback' => 'Must be a callable',
            ]
        );
    }

    #[Test]
    public function anExceptionIsThrownWhenANodeWouldBeItsOwnParent(): void
    {
        $this->expectException(InvalidParentException::class);
        $this->expectExceptionMessage('678 references its own ID as parent');

        new Tree([
            ['id' => 123, 'parent' => 0],
            ['id' => 678, 'parent' => 678],
        ]);
    }

    #[Test]
    public function anExceptionIsThrownWhenANodeWouldBeItsOwnParentWhenOwnIdAndParentIdHaveDifferentTypes(): void
    {
        $this->expectException(InvalidParentException::class);
        $this->expectExceptionMessage('references its own ID as parent');

        new Tree([
            ['id' => '5', 'parent' => 5],
        ]);
    }

    #[Test]
    public function whenMixingNumericAndStringIdsNoExceptionIsThrownDueToImplicitTypecasting(): void
    {
        new Tree([
            ['id' => 'foo', 'parent' => 0],
        ]);
        static::assertTrue(true); // Just to make PHPUnit happy
    }

    #[Test]
    public function clientsCanSupplyDifferingNamesForIdAndParentIdInInputData(): void
    {
        $data = self::dummyDataWithStringKeys('id_node', 'id_parent');

        $tree = new Tree($data, ['rootId' => '', 'id' => 'id_node', 'parent' => 'id_parent']);

        $nodes = $tree->getRootNodes();

        $expectedOrder = ['building', 'vehicle'];

        for ($i = 0, $ii = \count($nodes); $i < $ii; ++$i) {
            static::assertInstanceOf(Node::class, $nodes[$i]);
            static::assertSame($expectedOrder[$i], $nodes[$i]->getId());
        }
    }

    /**
     * @return array<array<string, string>>
     */
    private static function dummyDataWithNumericKeys(): array
    {
        return [
            ['id' => 21, 'name' => 'Altona', 'parent' => 11],
            ['id' => 5, 'name' => 'Africa', 'parent' => 0],
            ['id' => 3, 'name' => 'America', 'parent' => 0],
            ['id' => 4, 'name' => 'Asia', 'parent' => 0],
            ['id' => 6, 'name' => 'Australia', 'parent' => 0],
            ['id' => 15, 'name' => 'Berlin', 'parent' => 7],
            ['id' => 27, 'name' => 'Eimsbüttel', 'parent' => 11],
            ['id' => 1, 'name' => 'Europe', 'parent' => 0],
            ['id' => 7, 'name' => 'Germany', 'parent' => 1],
            ['id' => 11, 'name' => 'Hamburg', 'parent' => 7],
            ['id' => 20, 'name' => 'Lisbon', 'parent' => 10],
            ['id' => 12, 'name' => 'Munich', 'parent' => 7],
            ['id' => 10, 'name' => 'Portugal', 'parent' => 1],
        ];
    }

    /**
     * @return array<array<string, string>>
     */
    private static function dummyDataWithStringKeys(string $nodeIdPropertyName = 'id', string $parentIdPropertyName = 'parent'): array
    {
        return [
            [$nodeIdPropertyName => 'bicycle', $parentIdPropertyName => 'vehicle'],
            [$nodeIdPropertyName => 'building', $parentIdPropertyName => ''],
            [$nodeIdPropertyName => 'car', $parentIdPropertyName => 'vehicle'],
            [$nodeIdPropertyName => 'library', $parentIdPropertyName => 'building'],
            [$nodeIdPropertyName => 'primary-school', $parentIdPropertyName => 'school'],
            [$nodeIdPropertyName => 'school', $parentIdPropertyName => 'building'],
            [$nodeIdPropertyName => 'vehicle', $parentIdPropertyName => ''],
        ];
    }
}
