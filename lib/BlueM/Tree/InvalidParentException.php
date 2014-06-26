<?php

namespace BlueM\Tree;

/**
 * Exception which will be thrown if a tree node references a parent
 * node (throught the parent ID) which does not exist.
 *
 * If running in a production environment, if might be desirable to leaves the
 * exception uncatched (dev/test) or to catch it
 */
class InvalidParentException extends \RuntimeException
{

}