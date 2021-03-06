<?php

/**
 * Generic CREATE INDEX statement.
 *
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
 * @link http://msdn.microsoft.com/library/ms188783.aspx Transact-SQL
 */
class SQL_DDL_Create_Index extends SQL_Expression
{
	/**
	 * @uses SQL_DDL_Create_Index::columns()
	 * @uses SQL_DDL_Create_Index::name()
	 * @uses SQL_DDL_Create_Index::on()
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Identifier
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array $columns                                      List of columns, each converted to SQL_Column
	 */
	public function __construct($name = NULL, $table = NULL, $columns = NULL)
	{
		parent::__construct('');

		$this->name($name);
		$this->on($table);
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
		$this->parameters[':type'] = $value ? new SQL_Expression('UNIQUE') : FALSE;

		return $this;
	}

	/**
	 * Set the name of the index to be created.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $value  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof SQL_Expression
			AND ! $value instanceof SQL_Identifier)
		{
			$value = new SQL_Identifier($value);
		}

		$this->parameters[':name'] = $value;

		return $this;
	}

	/**
	 * Set the table to be indexed.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @return  $this
	 */
	public function on($table)
	{
		if ( ! $table instanceof SQL_Expression
			AND ! $table instanceof SQL_Identifier)
		{
			$table = new SQL_Table($table);
		}

		$this->parameters[':table'] = $table;

		return $this;
	}

	/**
	 * Append one column or expression to be included in the index.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $column     Converted to SQL_Column or NULL to reset
	 * @param   string                                      $direction  Direction to sort, ASC or DESC
	 * @return  $this
	 */
	public function column($column, $direction = NULL)
	{
		if ($column === NULL)
		{
			$this->parameters[':columns'] = array();
		}
		else
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}

			if ($direction)
			{
				$column = new SQL_Expression(
					'? '.strtoupper($direction),
					array($column)
				);
			}

			$this->parameters[':columns'][] = $column;
		}

		return $this;
	}

	/**
	 * Append columns and/or expressions to be included in the index.
	 *
	 * @param   array   $columns    List of columns, each converted to SQL_Column, or NULL to reset
	 * @return  $this
	 */
	public function columns($columns)
	{
		if ($columns === NULL)
		{
			$this->parameters[':columns'] = array();
		}
		else
		{
			foreach ($columns as $column)
			{
				if ( ! $column instanceof SQL_Expression
					AND ! $column instanceof SQL_Identifier)
				{
					$column = new SQL_Column($column);
				}

				$this->parameters[':columns'][] = $column;
			}
		}

		return $this;
	}
}
