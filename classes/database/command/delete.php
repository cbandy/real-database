<?php

/**
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
class Database_Command_Delete extends Database_Command
{
	/**
	 * @uses Database_Command_Delete::from()
	 *
	 * @param   mixed   $table  Converted to Database_Table
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
			// Not allowed in SQLite
			// Should be 'FROM' in MSSQL
			$value .= ' USING :using';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}

	/**
	 * Set the table from which to delete rows
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function from($table, $alias = NULL)
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
	 * Set the table(s) referenced in the search conditions
	 *
	 * @param   mixed   $reference      Database_From or converted to Database_Table
	 * @param   string  $table_alias    Table alias when converting to Database_Table
	 * @return  $this
	 */
	public function using($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof Database_From)
		{
			$reference = new Database_From($reference, $table_alias);
		}

		$this->parameters[':using'] = $reference;

		return $this;
	}

	/**
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function where($left, $operator = NULL, $right = NULL)
	{
		if ($operator !== NULL)
		{
			$left = new Database_Conditions($left, $operator, $right);
		}

		$this->parameters[':where'] = $left;

		return $this;
	}
}
