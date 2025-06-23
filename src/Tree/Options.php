<?php

namespace BlueM\Tree;

use BlueM\Tree\Serializer\TreeJsonSerializerInterface;

final readonly class Options
{
    public function __construct(
        public string|int|float|null $rootId = 0,
        public string $idFieldName = 'id',
        public string $parentIdFieldName = 'parent',
        public ?TreeJsonSerializerInterface $jsonSerializer = null,
        public mixed $buildWarningCallback = null,
    ) {
        if ($buildWarningCallback
            && !is_callable($buildWarningCallback)) {
            throw new \InvalidArgumentException('$buildWarningCallback must be a callable');
        }
    }
}
