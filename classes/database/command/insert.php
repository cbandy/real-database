<?php

/**
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
class Database_Command_Insert extends Database_Command
{
	/**
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns
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

		if ( ! isset($this->parameters[':values']))
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

		return $value;
	}

	/**
	 * @param   array   $columns
	 * @return  $this
	 */
	public function columns($columns)
	{
		if ($columns === NULL)
		{
			unset($this->parameters[':columns']);
		}
		else
		{
			foreach ($columns as &$column)
			{
				if ( ! $column instanceof Database_Expression
					AND ! $column instanceof Database_Identifier)
				{
					$column = new Database_Column($column);
				}
			}

			$this->parameters[':columns'] = $columns;
		}

		return $this;
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

		$this->parameters[':table'] = $table;

		return $this;
	}

	/**
	 * @param   mixed|NULL
	 * @param   ...
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
				$this->parameters[':values'][] = new Database_Expression('(?)', array($row));
			}
		}
		elseif ($values === NULL)
		{
			unset($this->parameters[':values']);
		}
		else
		{
			$this->parameters[':values'] = $values;
		}

		return $this;
	}
}
