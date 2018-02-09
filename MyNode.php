<?php
require __DIR__ . '/vendor/autoload.php';

//require_once __DIR__ . '/./MyNodeInterface.php';

use BlueM\Tree\Node as Node;

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
}
