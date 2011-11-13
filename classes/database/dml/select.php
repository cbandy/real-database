<?php

/**
 * @package     RealDatabase
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_DML_Select extends SQL_DML_Select
	implements Database_iQuery
{
	/**
	 * @var array   Arguments to pass to the class constructor
	 */
	public $arguments;

	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	public function as_object($class = TRUE, $arguments = array())
	{
		$this->as_object = $class;
		$this->arguments = $arguments;

		return $this;
	}
}
