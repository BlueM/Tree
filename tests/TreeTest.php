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
    #[TestDox('Constructor args: An exception is thrown if a non scalar value should be used as root id')]
    public function constructorArgsNonScalarRootId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “rootid” must be scalar or null');

        new Tree([], ['rootId' => []]);
    }

    #[Test]
    #[TestDox('Constructor args: An exception is thrown if a non string value should be used as id field name')]
    public function constructorArgsNonStringIDFieldName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “id” must be a string');

        new Tree([], ['id' => 123]);
    }

    #[Test]
    #[TestDox('Constructor args: An exception is thrown if a non string value should be used as parent id field name')]
    public function constructorArgsNonStringParentIDFieldName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “parent” must be a string');

        new Tree([], ['parent' => $this]);
    }

    #[Test]
    #[TestDox('Constructor args: An exception is thrown if a non object should be used as serializer')]
    public function constructorArgsNonObjectSerializer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option “jsonSerializer” must be an object');

        new Tree([], ['jsonSerializer' => 'not an object']);
    }

    #[Test]
    #[TestDox('Constructor args: The serializer can be set to an object implementing Serializerinterface')]
    public function constructorArgsValidSerializer(): void
    {
        $serializer = new HierarchicalTreeJsonSerializer();

        $subject = new Tree([], ['jsonSerializer' => $serializer]);

        $serializerProperty = new \ReflectionProperty($subject, 'jsonSerializer');

        static::assertSame($serializer, $serializerProperty->getValue($subject));
    }

    #[Test]
    #[TestDox('Constructor args: The root node’s ID can be defined as null')]
    public function constructorArgsNullAsRootNodeId(): void
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
    #[TestDox('Constructor args: The root node’s ID can be defined as null while there is a node with ID 0')]
    public function constructorArgsNullAsRootNodeIdPlusNodeWithId0(): void
    {
        $data = [
            ['id' => 1, 'parent' => null, 'name' => 'Root'],
            ['id' => 0, 'parent' => 1, 'name' => 'Child'],
            ['id' => 3, 'parent' => 0, 'name' => 'Grandchild'],
        ];

        $tree = new Tree($data, ['rootId' => null]);

        $nodes = $tree->getNodes();
        static::assertCount(3, $nodes);
        static::assertSame(1, $nodes[0]->getId());
        static::assertSame(0, $nodes[1]->getId());
        static::assertSame(3, $nodes[2]->getId());

        static::assertSame($nodes[0], $nodes[1]->getParent());
        static::assertNull($nodes[0]->getParent()->getId());
    }

    #[Test]
    #[TestDox('Constructor args: Name of fields for id and parent id in the input data can be changed')]
    public function constructorArgsChangeFieldNames(): void
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

    #[Test]
    #[TestDox('Build: The tree can be rebuilt from new data')]
    public function buildRebuildWithNewData(): void
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
    #[TestDox('Build: A tree can be created from an Iterable')]
    public function buildFromFromAnIterable(): void
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
    #[TestDox('Build: A tree can be created from an array of objects implementing Iterator')]
    public function buildFromAnArrayOfIterators(): void
    {
        $tree = new Tree([
            IterableObjectFactory::makeIterableInstance(['id' => 1, 'parent' => 0, 'title' => 'A']),
            IterableObjectFactory::makeIterableInstance(['id' => 2, 'parent' => 0, 'title' => 'B']),
            IterableObjectFactory::makeIterableInstance(['id' => 3, 'parent' => 2, 'title' => 'B-1']),
            IterableObjectFactory::makeIterableInstance(['id' => 4, 'parent' => 0, 'title' => 'D']),
        ]);

        static::assertSame(
            '[{"id":1,"title":"A","parent":0},{"id":2,"title":"B","parent":0},{"id":3,"title":"B-1","parent":2},{"id":4,"title":"D","parent":0}]',
            json_encode($tree)
        );
    }

    #[Test]
    #[TestDox('Build: The tree can be serialized to a json representation from which a tree with the same data can be built when decoded')]
    public function rebuildFromJSONSerialization(): void
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
    #[TestDox('Build: a custom warning callback can be used, which is called with an exception and the tree instance as arguments')]
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
    #[TestDox('Nodes: The root nodes can be retrieved')]
    public function nodesGetRootNodes(): void
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
    #[TestDox('Nodes: All nodes can be retrieved')]
    public function nodesGetAllNodes(): void
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
    #[TestDox('Nodes: A node can be accessed by its integer id')]
    public function nodeGetByIntId(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        $node = $tree->getNodeById(20);
        static::assertEquals(20, $node->getId());
        static::assertEquals($node, $tree->getNodeById('20'));
    }

    #[Test]
    #[TestDox('Nodes: A node can be accessed by its string id')]
    public function nodeGetByStringId(): void
    {
        $data = self::dummyDataWithStringKeys();
        $tree = new Tree($data, ['rootId' => '']);
        $node = $tree->getNodeById('library');
        static::assertEquals('library', $node->getId());
    }

    #[Test]
    #[TestDox('Nodes: Trying to get a node by its id throws an exception if the id is invalid')]
    public function nodeGetByInvalidId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid node primary key 999');

        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        $tree->getNodeById(999);
    }

    #[Test]
    #[TestDox('Nodes: A node can be accessed by its value path')]
    public function nodeGetByValuePath(): void
    {
        $data = self::dummyDataWithNumericKeys();
        $tree = new Tree($data);
        static::assertEquals(
            $tree->getNodeById(11),
            $tree->getNodeByValuePath('name', ['Europe', 'Germany', 'Hamburg'])
        );
    }

    #[Test]
    #[TestDox('Nodes: Trying to get a node by its value path returns null if no node matches')]
    public function nodeGetByUnresolvableValuePath(): void
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
