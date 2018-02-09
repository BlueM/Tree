<?php
require_once __DIR__ . '/./MyNode.php';
require_once __DIR__ . '/./MyTree.php';

use \MyTree;
use \MyNode;
$data = array(
    array('id' => 1,  'parent' => 0,  'name' =>'Europe'),
    array('id' => 3,  'parent' => 0,  'name' =>'America'),
    array('id' => 4,  'parent' => 0,  'name' =>'Asia'),
    array('id' => 5,  'parent' => 0,  'name' =>'Africa'),
    array('id' => 6,  'parent' => 0,  'name' =>'Australia'),
    // --
    array('id' => 7,  'parent' => 1,  'name' =>'Germany'),
    array('id' => 10, 'parent' => 1,  'name' =>'Portugal'),
    // --
    array('id' => 11, 'parent' => 7,  'name' =>'Hamburg'),
    array('id' => 12, 'parent' => 7,  'name' =>'Munich'),
    array('id' => 15, 'parent' => 7,  'name' =>'Berlin'),
    // --
    array('id' => 20, 'parent' => 10, 'name' => 'Lisbon'),
    // --
    array('id' => 27, 'parent' => 11, 'name' => 'Eimsbüttel'),
    array('id' => 21, 'parent' => 11, 'name' => 'Altona'),
);

$m = new MyTree($data);
echo $m;