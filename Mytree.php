<?php
require __DIR__ . '/vendor/autoload.php';

use BlueM\BaseTree;
use MyNode as Node;

class MyTree extends BaseTree
{
    protected function createNode(array $properties)
    {
        return new Node($properties);
    }
}
