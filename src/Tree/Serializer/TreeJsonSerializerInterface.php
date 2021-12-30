<?php

namespace BlueM\Tree\Serializer;

use BlueM\Tree;

/**
 * Interface for classes which offer tree serialization.
 *
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 3-Clause License
 */
interface TreeJsonSerializerInterface
{
    /**
     * Returns a representation of the tree which is natively encodable in JSON using json_encode().
     *
     * @return mixed
     */
    public function serialize(Tree $tree);
}
