<?php

namespace BlueM\Tree\Serializer;

use BlueM\Tree;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FlatTreeJsonSerializerTest extends TestCase
{
    #[Test]
    public function serializationHappensByCallingGetNodesMethodOnTheTree(): void
    {
        $treeMock = $this->createMock(Tree::class);
        $treeMock->expects($this->once())
                 ->method('getNodes')
                 ->willReturn(['Dummy return value']);

        $subject = new FlatTreeJsonSerializer();
        static::assertSame(['Dummy return value'], $subject->serialize($treeMock));
    }
}
