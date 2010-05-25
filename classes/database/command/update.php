<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/update.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-update.html PostgreSQL
 * @link http://www.sqlite.org/lang_update.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms177523.aspx Transact-SQL
 */
class Database_Command_Update extends Database_Command
{
	/**
	 * @param   mixed   Converted to Database_Table
	 * @param   string  Table alias
	 * @param   array
	 */
	public function __construct($table = NULL, $alias = NULL, $values = NULL)
	{
		parent::__construct('');

		$this->table($table, $alias)->set($values);
	}

	public function __toString()
	{
		$value = 'UPDATE :table SET :values';

		if ( ! empty($this->parameters[':from']))
		{
			// Not allowed in MySQL
			// Not allowed in SQLite
			$value .= ' FROM :from';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}

	/**
	 * @param   mixed   Converted to Database_Table
	 * @param   string  Table alias
	 * @return  $this
	 */
	public function table($table, $alias = NULL)
	{
		if ( ! $table instanceof Database_Expression
			AND ! $table instanceof Database_Identifier)
		{
			$table = new Database_Table($table);
		}

		$this->parameters[':table'] = empty($alias)
			? $table
			: new Database_Expression('? AS ?', array($table, new Database_Identifier($alias)));

		return $this;
	}

	/**
	 * @param   mixed
	 * @return  $this
	 */
	public function set($values)
	{
		if (is_array($values))
		{
			foreach ($values as $column => $value)
			{
				$column = new Database_Column($column);

				$this->parameters[':values'][] = new Database_Expression('? = ?', array($column, $value));
			}
		}
		elseif ($values === NULL)
		{
			$this->parameters[':values'] = array();
		}
		else
		{
			$this->parameters[':values'] = $values;
		}

		return $this;
	}

	/**
	 * Append a column assignment
	 *
	 * @param   mixed   $column Converted to Database_Column
	 * @param   mixed   $value  Value assigned to the column
	 * @return  $this
	 */
	public function value($column, $value)
	{
		if ( ! $column instanceof Database_Expression
			AND ! $column instanceof Database_Identifier)
		{
			$column = new Database_Column($column);
		}

		$this->parameters[':values'][] = new Database_Expression('? = ?', array($column, $value));

		return $this;
	}

	/**
	 * @param   mixed   $reference      Database_From or converted to Database_Table
	 * @param   string  $table_alias    Table alias when converting to Database_Table
	 * @return  $this
	 */
	public function from($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof Database_From)
		{
			$reference = new Database_From($reference, $table_alias);
		}

		$this->parameters[':from'] = $reference;

		return $this;
	}

	/**
	 * Set the search condition(s). When no operator is specified, the first
	 * argument is used directly.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function where($left_column, $operator = NULL, $right = NULL)
	{
		if ($operator !== NULL)
		{
			if ( ! $left_column instanceof Database_Expression
				AND ! $left_column instanceof Database_Identifier)
			{
				$left_column = new Database_Column($left_column);
			}

			$left_column = new Database_Conditions($left_column, $operator, $right);
		}

		$this->parameters[':where'] = $left_column;

		return $this;
	}
}
