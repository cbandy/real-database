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
class Database_Command_Insert extends Database_Command
{
	/**
	 * @uses Database_Command_Insert::into()
	 * @uses Database_Command_Insert::columns()
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns    Each element converted to Database_Column
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

		return $value;
	}

	/**
	 * Set the list of columns to be populated with values
	 *
	 * @param   array|NULL  $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function columns($columns)
	{
		if ($columns !== NULL)
		{
			foreach ($columns as &$column)
			{
				if ( ! $column instanceof Database_Expression
					AND ! $column instanceof Database_Identifier)
				{
					$column = new Database_Column($column);
				}
			}
		}

		$this->parameters[':columns'] = $columns;

		return $this;
	}

	/**
	 * Set the table in which to insert rows
	 *
	 * @param   mixed   $table  Converted to Database_Table
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
				$this->parameters[':values'][] = new Database_Expression('(?)', array($row));
			}
		}
		else
		{
			$this->parameters[':values'] = $values;
		}

		return $this;
	}
}
