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
class Database_PostgreSQL_Insert extends Database_Command_Insert_Identity
{
	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	public function __toString()
	{
		$value = parent::__toString();

		if ( ! empty($this->parameters[':returning']))
		{
			$value .= ' RETURNING :returning';
		}

		return $value;
	}

	/**
	 * Return rows as associative arrays when executed.
	 *
	 * @return  $this
	 */
	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	/**
	 * Set the class as which to return rows when executed.
	 *
	 * @param   string|boolean  $class  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @return  $this
	 */
	public function as_object($class = TRUE)
	{
		$this->as_object = $class;

		return $this;
	}

	/**
	 * Execute the INSERT on a Database. Returns an array when identity() is
	 * set. Returns a result set when returning() is set.
	 *
	 * @throws  Database_Exception
	 * @param   Database_PostgreSQL $db Connection on which to execute
	 * @return  integer                     Number of affected rows
	 * @return  array                       List including number of affected rows and identity of first row
	 * @return  Database_PostgreSQL_Result  Result set
	 */
	public function execute($db)
	{
		if (empty($this->parameters[':returning']))
			return $db->execute_command($this);

		if (empty($this->identity))
			return $db->execute_query($this, $this->as_object);

		return $db->execute_insert($this, $this->identity, $this->as_object);
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

	/**
	 * Append values to return when executed
	 *
	 * @param   mixed   $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function returning($columns)
	{
		$this->identity = NULL;

		if (is_array($columns))
		{
			foreach ($columns as $alias => $column)
			{
				if ( ! $column instanceof SQL_Expression
					AND ! $column instanceof SQL_Identifier)
				{
					$column = new SQL_Column($column);
				}

				if (is_string($alias) AND $alias !== '')
				{
					$column = new SQL_Expression('? AS ?', array($column, new SQL_Identifier($alias)));
				}

				$this->parameters[':returning'][] = $column;
			}
		}
		elseif ($columns === NULL)
		{
			unset($this->parameters[':returning']);
		}
		else
		{
			$this->parameters[':returning'] = $columns;
		}

		return $this;
	}
}
