<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-index.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createindex.html PostgreSQL
 * @link http://www.sqlite.org/lang_createindex.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms188783.aspx Transact-SQL
 */
class Database_Command_Create_Index extends Database_Command
{
	/**
	 * @uses Database_Command_Create_Index::columns()
	 * @uses Database_Command_Create_Index::name()
	 * @uses Database_Command_Create_Index::on()
	 *
	 * @param   mixed   $name       Converted to Database_Identifier
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns    Each element converted to Database_Column
	 */
	public function __construct($name = NULL, $table = NULL, $columns = array())
	{
		parent::__construct('');

		if ($name !== NULL)
		{
			$this->name($name);
		}

		if ($table !== NULL)
		{
			$this->on($table);
		}

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ( ! empty($this->parameters[':type']))
		{
			$value .= ' :type';
		}

		$value .= ' INDEX :name ON :table (:columns)';

		return $value;
	}

	/**
	 * Set whether or not duplicate values should be prohibited in the index
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function unique($value = TRUE)
	{
		$this->parameters[':type'] = $value ? new Database_Expression('UNIQUE') : FALSE;

		return $this;
	}

	/**
	 * Set the name of the index to be created
	 *
	 * @param   mixed   $value  Converted to Database_Identifier
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof Database_Expression
			AND ! $value instanceof Database_Identifier)
		{
			$value = new Database_Identifier($value);
		}

		$this->parameters[':name'] = $value;

		return $this;
	}

	/**
	 * Set the table to be indexed
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @return  $this
	 */
	public function on($table)
	{
		if ( ! $table instanceof Database_Expression
			AND ! $table instanceof Database_Identifier)
		{
			$table = new Database_Table($table);
		}

		$this->parameters[':table'] = $table;

		return $this;
	}

	/**
	 * Append one column or expression to be included in the index
	 *
	 * @param   mixed   $column     Converted to Database_Column
	 * @param   string  $direction  Direction to sort, ASC or DESC
	 * @return  $this
	 */
	public function column($column, $direction = NULL)
	{
		if ( ! $column instanceof Database_Expression
			AND ! $column instanceof Database_Identifier)
		{
			$column = new Database_Column($column);
		}

		if ($direction)
		{
			$column = new Database_Expression('? '.strtoupper($direction), array($column));
		}

		$this->parameters[':columns'][] = $column;

		return $this;
	}

	/**
	 * Set the columns and/or expressions to be included in the index
	 *
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function columns($columns)
	{
		foreach ($columns as & $column)
		{
			if ( ! $column instanceof Database_Expression
				AND ! $column instanceof Database_Identifier)
			{
				$column = new Database_Column($column);
			}
		}

		$this->parameters[':columns'] = $columns;

		return $this;
	}
}
