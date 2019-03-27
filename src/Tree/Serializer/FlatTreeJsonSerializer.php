<?php

namespace BlueM\Tree\Serializer;

use BlueM\Tree;

/**
 * Serializer which creates a flat, depth-first sorted representation of the tree nodes,
 * which (once JSON-encoded and again JSON-decoded) can be fed again into the Tree constructor.
 *
 * @author  Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 3-Clause License
 */
class FlatTreeJsonSerializer implements TreeJsonSerializerInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array JSON-serializable array of Node instances
     */
    public function serialize(Tree $tree): array
    {
        return $tree->getNodes();
    }
}
