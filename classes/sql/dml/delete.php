<?php

/**
 * Command for building DELETE statements.
 *
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/delete.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-delete.html PostgreSQL
 * @link http://www.sqlite.org/lang_delete.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms189835.aspx Transact-SQL
 */
class SQL_DML_Delete extends SQL_Expression
{
	/**
	 * @uses SQL_DML_Delete::from()
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 */
	public function __construct($table = NULL, $alias = NULL)
	{
		parent::__construct('');

		$this->from($table, $alias);
	}

	public function __toString()
	{
		$value = 'DELETE FROM :table';

		if ( ! empty($this->parameters[':using']))
		{
			// Not allowed by MSSQL
			// Not allowed by SQLite
			$value .= ' USING :using';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		if (isset($this->parameters[':limit']))
		{
			// Not allowed by MSSQL
			// Not allowed by PostgreSQL
			$value .= ' LIMIT :limit';
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
	 * Set the table from which to delete rows
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function from($table, $alias = NULL)
	{
		if ( ! $table instanceof SQL_Expression
			AND ! $table instanceof SQL_Identifier)
		{
			$table = new SQL_Table($table);
		}

		if ($alias)
		{
			$table = new SQL_Alias($table, $alias);
		}

		$this->parameters[':table'] = $table;

		return $this;
	}

	/**
	 * Set the maximum number of rows to be deleted.
	 *
	 * @param   integer $count  Number of rows
	 * @return  $this
	 */
	public function limit($count)
	{
		$this->parameters[':limit'] = $count;

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
	 * Set the table(s) referenced in the search conditions.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   mixed   $reference      SQL_Table_Reference or converted to SQL_Table
	 * @param   string  $table_alias    Table alias when converting to SQL_Table
	 * @return  $this
	 */
	public function using($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof SQL_Table_Reference)
		{
			$reference = new SQL_Table_Reference($reference, $table_alias);
		}

		$this->parameters[':using'] = $reference;

		return $this;
	}

	/**
	 * Set the search condition(s). When no operator is specified, the first
	 * argument is used directly.
	 *
	 * @param   mixed   $left_column    Left operand, converted to SQL_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function where($left_column, $operator = NULL, $right = NULL)
	{
		if ($operator !== NULL)
		{
			if ( ! $left_column instanceof SQL_Expression
				AND ! $left_column instanceof SQL_Identifier)
			{
				$left_column = new SQL_Column($left_column);
			}

			$left_column = new SQL_Conditions($left_column, $operator, $right);
		}

		$this->parameters[':where'] = $left_column;

		return $this;
	}
}
