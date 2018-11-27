<?php

/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection PhpParamsInspection */

namespace BlueM\Tree\Serializer;

use BlueM\Tree;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BlueM\Tree\Serializer\FlatSerializer
 */
class FlatSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function serializationHappensByCallingGetNodesMethodOnTheTree()
    {
        $treeMock = $this->createMock(Tree::class);
        $treeMock->expects(static::once())
                 ->method('getNodes')
                 ->willReturn(['Dummy return value']);

        $subject = new FlatSerializer();
        static::assertSame(['Dummy return value'], $subject->serialize($treeMock));
    }
}
