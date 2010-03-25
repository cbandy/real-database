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

	protected function _build()
	{
		$value = 'UPDATE :table SET :values';

		if ( ! empty($this->_parameters[':from']))
		{
			// Not allowed in MySQL
			// Not allowed in SQLite
			$value .= ' FROM :from';
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
	public function table($table, $alias = NULL)
	{
		return $this->param(':table', new Database_From($table, $alias));
	}

	/**
	 * @param   mixed
	 * @return  $this
	 */
	public function set($values)
	{
		if ($values === NULL)
		{
			$this->param(':values', array());
		}
		elseif (is_array($values))
		{
			foreach ($values as $column => $value)
			{
				$column = new Database_Column($column);

				$this->_parameters[':values'][] = new Database_Expression('? = ?', array($column, $value));
			}
		}
		else
		{
			$this->param(':values', $values);
		}

		return $this;
	}

	/**
	 * @param   mixed   Converted to Database_Column
	 * @param   mixed
	 * @return  $this
	 */
	public function value($column, $value = NULL)
	{
		if ( ! $column instanceof Database_Expression
			AND ! $column instanceof Database_Identifier)
		{
			$column = new Database_Column($column);
		}

		$this->_parameters[':values'][] = new Database_Expression('? = ?', array($column, $value));

		return $this;
	}

	/**
	 * @param   Database_From   $reference
	 * @return  $this
	 */
	public function from($reference)
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