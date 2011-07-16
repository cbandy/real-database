<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/drop-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-droptable.html PostgreSQL
 * @link http://www.sqlite.org/lang_droptable.html SQLite
 * @link http://msdn.microsoft.com/library/ms173790.aspx Transact-SQL
 */
class SQL_DDL_Drop_Table extends SQL_DDL_Drop
{
	/**
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name       Converted to SQL_Table
	 * @param   boolean                                     $cascade    Whether or not dependent objects should be dropped
	 */
	public function __construct($name = NULL, $cascade = NULL)
	{
		parent::__construct('TABLE', $name, $cascade);
	}

	/**
	 * Append the name of a table to be dropped.
	 *
	 * [!!] SQLite allows only one table per statement
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table or NULL to reset
	 * @return  $this
	 */
	public function name($table)
	{
		if ($table === NULL)
		{
			$this->parameters[':names'] = array();
		}
		else
		{
			if ( ! $table instanceof SQL_Expression
				AND ! $table instanceof SQL_Identifier)
			{
				$table = new SQL_Table($table);
			}

			$this->parameters[':names'][] = $table;
		}

		return $this;
	}

	/**
	 * Append the names of multiple tables to be dropped.
	 *
	 * [!!] SQLite allows only one table per statement
	 *
	 * @param   array   $tables List of names, each converted to SQL_Table, or NULL to reset
	 * @return  $this
	 */
	public function names($tables)
	{
		if ($tables === NULL)
		{
			$this->parameters[':names'] = array();
		}
		else
		{
			foreach ($tables as $table)
			{
				if ( ! $table instanceof SQL_Expression
					AND ! $table instanceof SQL_Identifier)
				{
					$table = new SQL_Table($table);
				}

				$this->parameters[':names'][] = $table;
			}
		}

		return $this;
	}
}
