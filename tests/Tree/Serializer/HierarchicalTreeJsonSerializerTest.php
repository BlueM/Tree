<?php

/* @noinspection ReturnTypeCanBeDeclaredInspection */
/* @noinspection PhpParamsInspection */

namespace BlueM\Tree\Serializer;

use BlueM\Tree;
use BlueM\Tree\Node;
use PHPUnit\Framework\TestCase;

/**
 * @group  unit
 * @covers \BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer
 */
class HierarchicalTreeJsonSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function serializesToAHierarchicalArray()
    {
        $rootNodes = [
            $this->createNodeStub(
                '1',
                [
                    $this->createNodeStub(
                        '1.1',
                        [
                            $this->createNodeStub('1.1.1'),
                            $this->createNodeStub('1.1.2'),
                        ]
                    )
                ]
            ),
            $this->createNodeStub('2'),
        ];

        $treeStub = $this->createMock(Tree::class);
        $treeStub->expects(static::once())
                 ->method('getRootNodes')
                 ->willReturn($rootNodes);

        $serializer = new HierarchicalTreeJsonSerializer();
        $actual = $serializer->serialize($treeStub);

        static::assertSame(
            [
                [
                    'id' => '1',
                    'children' =>
                        [
                            [
                                'id' => '1.1',
                                'children' =>
                                    [
                                        ['id' => '1.1.1'],
                                        ['id' => '1.1.2'],
                                    ],
                            ],
                        ],
                    ],
                [
                    'id' => '2',
                ],
            ],
            $actual
        );
    }

    private function createNodeStub($id, array $childNodes = [])
    {
        $nodeStub = $this->createMock(Node::class);
        $nodeStub->expects(static::once())
                 ->method('toArray')
                 ->willReturn(['id' => $id]);
        $nodeStub->expects(static::once())
                 ->method('hasChildren')
                 ->willReturn(\count($childNodes) > 0);

        if (\count($childNodes)) {
            $nodeStub->expects(static::once())
                     ->method('getChildren')
                     ->willReturn($childNodes);
        }

        return $nodeStub;
    }
}
