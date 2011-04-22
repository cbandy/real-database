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
 * @link http://msdn.microsoft.com/en-us/library/ms174335.aspx Transact-SQL
 */
class SQL_DML_Insert extends SQL_Expression
{
	/**
	 * @uses SQL_DML_Insert::into()
	 * @uses SQL_DML_Insert::columns()
	 *
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $columns    Each element converted to SQL_Column
	 */
	public function __construct($table = NULL, $columns = NULL)
	{
		parent::__construct('');

		$this->into($table)->columns($columns);
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
	 * Set the list of columns to be populated with values
	 *
	 * @param   array|NULL  $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function columns($columns)
	{
		if ($columns !== NULL)
		{
			foreach ($columns as & $column)
			{
				if ( ! $column instanceof SQL_Expression
					AND ! $column instanceof SQL_Identifier)
				{
					$column = new SQL_Column($column);
				}
			}
		}

		$this->parameters[':columns'] = $columns;

		return $this;
	}

	/**
	 * Set the table in which to insert rows
	 *
	 * @param   mixed   $table  Converted to SQL_Table
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
	 * Append multiple columns or expressions to be returned when executed.
	 *
	 * [!!] Not supported by MySQL
	 * [!!] Not supported by SQLite
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 * @return  $this
	 */
	public function returning($columns)
	{
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
					$column = new SQL_Alias($column, $alias);
				}

				$this->parameters[':returning'][] = $column;
			}
		}
		elseif ($columns === NULL)
		{
			$this->parameters[':returning'] = array();
		}
		else
		{
			$this->parameters[':returning'] = $columns;
		}

		return $this;
	}

	/**
	 * Append rows of values to be inserted
	 *
	 * @param   mixed|NULL  $values,... Row of values
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
