<?php

/**
 * An UPDATE statement which can return columns from the affected rows when
 * executed.
 *
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_DML_Update extends SQL_DML_Update
	implements Database_iReturning
{
	/**
	 * @var array   Arguments to pass to the class constructor
	 */
	public $arguments;

	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	/**
	 * @var array   Columns to return from the updated rows when executed
	 */
	public $returning;

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

	public function returning($columns)
	{
		parent::returning($columns);

		$this->returning = $this->parameters[':returning'];

		return $this;
	}
}
