<?php

/**
 * @package RealDatabase
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
	 * @param   mixed   Converted to Database_Table
	 * @param   string  Table alias
	 */
	public function __construct($table = NULL, $alias = NULL)
	{
		parent::__construct('');

		$this->from($table, $alias);
	}

	protected function _build()
	{
		$value = 'DELETE FROM :table';

		if ( ! empty($this->_parameters[':from']))
		{
			// Not allowed in SQLite
			// Should be 'FROM' in MSSQL
			$value .= ' USING :from';
		}

		if ( ! empty($this->_parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}

	public function compile(Database $db)
	{
		$this->_value = $this->_build();

		return parent::compile($db);
	}

	/**
	 * @param   mixed   Converted to Database_Table
	 * @param   string  Table alias
	 * @return  $this
	 */
	public function from($table, $alias = NULL)
	{
		return $this->param(':table', new Database_From($table, $alias));
	}

	/**
	 * @param   Database_From   $reference
	 * @return  $this
	 */
	public function using($reference)
	{
		return $this->param(':from', $reference);
	}

	/**
	 * @param   Database_Conditions $conditions
	 * @return  $this
	 */
	public function where($conditions)
	{
		return $this->param(':where', $conditions);
	}
}
