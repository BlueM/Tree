<?php

require_once __DIR__ . '/./lib/BlueM/BaseTree.php';
use BlueM\BaseTree;
use MyNode as Node;

class MyTree extends BaseTree
{
    protected function createNode(array $properties)
    {
        return new Node($properties);
    }
}
