<?php

/**
 * Command for building UPDATE statements.
 *
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/update.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-update.html PostgreSQL
 * @link http://www.sqlite.org/lang_update.html SQLite
 * @link http://msdn.microsoft.com/library/ms177523.aspx Transact-SQL
 */
class SQL_DML_Update extends SQL_Expression
{
	/**
	 * @uses SQL_DML_Update::table()
	 * @uses SQL_DML_Update::set()
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @param   array   $values Hash of (column => value) assignments
	 */
	public function __construct($table = NULL, $alias = NULL, $values = NULL)
	{
		parent::__construct('');

		$this->table($table, $alias);
		$this->set($values);
	}

	public function __toString()
	{
		$value = 'UPDATE :table SET :values';

		if ( ! empty($this->parameters[':from']))
		{
			// Not allowed by MySQL
			// Not allowed by SQLite
			$value .= ' FROM :from';
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
	 * Set the table in which to update rows
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function table($table, $alias = NULL)
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
	 * Append multiple column assignments.
	 *
	 * @param   array   $values Hash of (column => value) assignments or NULL to reset
	 * @return  $this
	 */
	public function set($values)
	{
		if ($values === NULL)
		{
			$this->parameters[':values'] = array();
		}
		else
		{
			foreach ($values as $column => $value)
			{
				$column = new SQL_Column($column);

				$this->parameters[':values'][] = new SQL_Expression(
					'? = ?', array($column, $value)
				);
			}
		}

		return $this;
	}

	/**
	 * Append a column assignment.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $column Converted to SQL_Column or NULL to reset
	 * @param   mixed                                       $value  Value assigned to the column
	 * @return  $this
	 */
	public function value($column, $value)
	{
		if ($column === NULL)
		{
			$this->parameters[':values'] = array();
		}
		else
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}

			$this->parameters[':values'][] = new SQL_Expression(
				'? = ?', array($column, $value)
			);
		}

		return $this;
	}

	/**
	 * Set the table(s) referenced in the search conditions.
	 *
	 * [!!] Not supported by MySQL or SQLite
	 *
	 * @param   mixed   $reference      SQL_Table_Reference or converted to SQL_Table
	 * @param   string  $table_alias    Table alias when converting to SQL_Table
	 * @return  $this
	 */
	public function from($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof SQL_Table_Reference)
		{
			$reference = new SQL_Table_Reference($reference, $table_alias);
		}

		$this->parameters[':from'] = $reference;

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

	/**
	 * Set the maximum number of rows to be updated.
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
}
