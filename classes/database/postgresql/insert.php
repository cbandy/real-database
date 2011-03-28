<?php

/**
 * INSERT statement for PostgreSQL. Allows a result set from the inserted rows to be returned.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-insert.html
 */
class Database_PostgreSQL_Insert extends Database_Insert
	implements Database_PostgreSQL_iReturning
{
	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	public function as_object($class = TRUE)
	{
		$this->as_object = $class;

		return $this;
	}

	/**
	 * Set the name of the column to return from the first row when executed
	 *
	 * @param   mixed   $column Converted to SQL_Column
	 * @return  $this
	 */
	public function identity($column)
	{
		parent::identity($column);

		if (empty($this->identity))
		{
			unset($this->parameters[':returning']);
		}
		else
		{
			$this->parameters[':returning'] = $this->identity;
		}

		return $this;
	}

	public function returning($columns)
	{
		$this->identity = NULL;

		return parent::returning($columns);
	}
}
