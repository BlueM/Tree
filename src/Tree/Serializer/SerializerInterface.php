<?php

namespace BlueM\Tree\Serializer;

use BlueM\Tree;

/**
 * Interface for classes which offer tree serialization
 */
interface SerializerInterface
{
    /**
     * Returns a representation of the tree which is natively encodable in JSON using json_encode()
     *
     * @param Tree $tree
     *
     * @return mixed
     */
    public function serialize(Tree $tree);
}
