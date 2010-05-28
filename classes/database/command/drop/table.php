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
 * @link http://msdn.microsoft.com/en-us/library/ms173790.aspx Transact-SQL
 */
class Database_Command_Drop_Table extends Database_Command_Drop
{
	/**
	 * @param   mixed   $name       Converted to Database_Table
	 * @param   boolean $cascade    Whether or not dependent objects should be dropped
	 */
	public function __construct($name = NULL, $cascade = NULL)
	{
		parent::__construct('TABLE', $name, $cascade);
	}

	/**
	 * Set the name of the table to be dropped
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @return  $this
	 */
	public function name($table)
	{
		if ( ! $table instanceof Database_Expression
			AND ! $table instanceof Database_Identifier)
		{
			$table = new Database_Table($table);
		}

		$this->parameters[':name'] = $table;

		return $this;
	}

	/**
	 * Set the names of multiple tables to be dropped
	 *
	 * @param   mixed|NULL  $tables Each element converted to Database_Table
	 * @return  $this
	 */
	public function names($tables)
	{
		if (is_array($tables))
		{
			// SQLite allows only one
			foreach ($tables as &$table)
			{
				if ( ! $table instanceof Database_Expression
					AND ! $table instanceof Database_Identifier)
				{
					$table = new Database_Table($table);
				}
			}
		}

		$this->parameters[':name'] = $tables;

		return $this;
	}
}
