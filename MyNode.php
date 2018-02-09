<?php
require_once __DIR__ . '/./lib/BlueM/Tree/Node.php';
require_once __DIR__ . '/./MyNodeInterface.php';
use BlueM\Tree\Node as Node;
//use Knp\Menu\NodeInterface as NodeInterface;

class MyNode extends Node implements MyNodeInterface {

    public function getName() {
        return $this->get('name');
    }

    /**
     * Get the options for the factory to create the item for this node
     *
     * @return array
     */
    public function getOptions() {
        return $this->toArray();
    }

    /**
     * Get the child nodes implementing NodeInterface
     *
     * @return \Traversable
     */
	/*
    public function getChildren() {
        return $parent->getChildren();
    }
	*/
}
