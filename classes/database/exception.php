<?php

/**
 * Thrown when a PHP driver method fails or the connection raises an error condition.
 *
 * @package     RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://bugs.php.net/39615 Exception code cannot be string
 * @link http://bugs.php.net/51742 Notice when exception code is string
 */
class Database_Exception extends Kohana_Exception {}
