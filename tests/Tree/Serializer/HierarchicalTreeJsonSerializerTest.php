<?php

namespace BlueM\Tree\Serializer;

use BlueM\Tree;
use BlueM\Tree\Node;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HierarchicalTreeJsonSerializerTest extends TestCase
{
    #[Test]
    public function serializesToAHierarchicalArray(): void
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
        $treeStub->expects($this->once())
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

    private function createNodeStub($id, array $childNodes = []): Node
    {
        $nodeStub = $this->createMock(Node::class);
        $nodeStub->expects($this->once())
                 ->method('toArray')
                 ->willReturn(['id' => $id]);
        $nodeStub->expects($this->once())
                 ->method('hasChildren')
                 ->willReturn(\count($childNodes) > 0);

        if (\count($childNodes)) {
            $nodeStub->expects($this->once())
                     ->method('getChildren')
                     ->willReturn($childNodes);
        }

        return $nodeStub;
    }
}
