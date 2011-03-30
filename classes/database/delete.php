<?php

/**
 * A DELETE statement which can return columns from the deleted rows when
 * executed.
 *
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Delete extends SQL_DML_Delete
	implements Database_iReturning
{
	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	/**
	 * @var mixed   Columns to return from the deleted rows when executed
	 */
	public $returning;

	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	public function as_object($class = TRUE)
	{
		$this->as_object = $class;

		return $this;
	}

	public function returning($columns)
	{
		parent::returning($columns);

		$this->returning = $this->parameters[':returning'];

		return $this;
	}
}
