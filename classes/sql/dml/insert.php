<?php

/**
 * Command for building INSERT statements.
 *
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/insert.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-insert.html PostgreSQL
 * @link http://www.sqlite.org/lang_insert.html SQLite
 * @link http://msdn.microsoft.com/library/ms174335.aspx Transact-SQL
 */
class SQL_DML_Insert extends SQL_Expression
{
	/**
	 * @uses SQL_DML_Insert::into()
	 * @uses SQL_DML_Insert::columns()
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns, each converted to SQL_Column
	 */
	public function __construct($table = NULL, $columns = NULL)
	{
		parent::__construct('');

		$this->into($table);
		$this->columns($columns);
	}

	public function __toString()
	{
		$value = 'INSERT INTO :table ';

		if ( ! empty($this->parameters[':columns']))
		{
			$value .= '(:columns) ';
		}

		if (empty($this->parameters[':values']))
		{
			// Not allowed by MySQL
			$value .= 'DEFAULT VALUES';
		}
		elseif (is_array($this->parameters[':values']))
		{
			$value .= 'VALUES :values';
		}
		else
		{
			$value .= ':values';
		}

		if ( ! empty($this->parameters[':returning']))
		{
			// Not allowed by MSSQL
			// Not allowed by MySQL
			// Not allowed by SQLite
			$value .= ' RETURNING :returning';
		}

		return $value;
	}

	/**
	 * Append multiple columns to be populated with values.
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

	/**
	 * Set the table in which to insert rows.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @return  $this
	 */
	public function into($table)
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
	 * Append multiple columns and/or expressions to be returned when executed.
	 *
	 * [!!] Not supported by MySQL or SQLite
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs or NULL to reset
	 * @return  $this
	 */
	public function returning($columns)
	{
		if ($columns === NULL)
		{
			$this->parameters[':returning'] = array();
		}
		else
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
					$column = new SQL_Alias($column, $alias);
				}

				$this->parameters[':returning'][] = $column;
			}
		}

		return $this;
	}

	/**
	 * Append rows of values to be inserted.
	 *
	 * @param   array   $values,... Row of values or NULL to reset
	 * @return  $this
	 */
	public function values($values)
	{
		if (is_array($values))
		{
			$values = func_get_args();

			// SQLite allows only one row
			foreach ($values as $row)
			{
				// Wrap each row in parentheses
				$this->parameters[':values'][] = new SQL_Expression('(?)', array($row));
			}
		}
		else
		{
			$this->parameters[':values'] = $values;
		}

		return $this;
	}
}
