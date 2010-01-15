<?php

/**
 * @package RealDatabase
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
class Database_Query_Insert extends Database_Query
{
	/**
	 * @param   mixed   Converted to Database_Table
	 * @param   array
	 */
	public function __construct($table = NULL, $columns = array())
	{
		parent::__construct('');

		$this->into($table)->columns($columns);
	}

	/**
	 * @param   array
	 * @return  $this
	 */
	public function columns(array $columns)
	{
		foreach ($columns as &$column)
		{
			if ( ! $column instanceof Database_Expression
				AND ! $column instanceof Database_Identifier)
			{
				$column = new Database_Column($column);
			}
		}

		return $this->param(':columns', $columns);
	}

	/**
	 * @param   mixed   Converted to Database_Table
	 * @return  $this
	 */
	public function into($table)
	{
		if ( ! $table instanceof Database_Expression
			AND ! $table instanceof Database_Identifier)
		{
			$table = new Database_Table($table);
		}

		return $this->param(':table', $table);
	}

	/**
	 * @param   mixed|NULL
	 * @param   ...
	 * @return  $this
	 */
	public function values($values)
	{
		if ($values === NULL)
		{
			unset($this->_parameters[':values']);
		}
		elseif (is_array($values))
		{
			$values = func_get_args();

			// SQLite allows only one row
			foreach ($values as $row)
			{
				// Wrap each row in parentheses
				$this->_parameters[':values'][] = new Database_Expression('(?)', array($row));
			}
		}
		else
		{
			$this->param(':values', $values);
		}

		return $this;
	}

	public function compile(Database $db)
	{
		$this->_value = 'INSERT INTO :table ';

		if (count($this->_parameters[':columns']))
		{
			$this->_value .= '(:columns) ';
		}

		if ( ! isset($this->_parameters[':values']))
		{
			// Not allowed by MySQL
			$this->_value .= 'DEFAULT VALUES';
		}
		elseif (is_array($this->_parameters[':values']))
		{
			$this->_value .= 'VALUES :values';
		}
		else
		{
			$this->_value .= ':values';
		}

		return parent::compile($db);
	}
}
