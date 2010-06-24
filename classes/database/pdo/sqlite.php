<?php

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_SQLite extends Database_PDO implements Database_iEscape, Database_iInsert
{
	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  Database_Command_Insert_Multiple
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_Command_Insert_Multiple($table, $columns);
	}

	/**
	 * Create a PDO connection for SQLite
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | string  | Character set
	 *  profiling             | boolean | Enable execution profiling
	 *  schema                | string  | Table prefix
	 *  connection.dsn        | string  | Full DSN or a predefined DSN name
	 *  connection.options    | array   | PDO options
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.uri        | string  | URI to a file containing the DSN
	 *
	 * *[DSN]: Data Source Name
	 * *[URI]: Uniform Resource Identifier
	 *
	 * @link http://php.net/manual/ref.pdo-sqlite.connection PDO SQLite DSN
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name   Instance name
	 * @param   array   $config Configuration
	 */
	protected function __construct($name, $config)
	{
		parent::__construct($name, $config);

		$this->_config['connection']['username'] = NULL;
		$this->_config['connection']['password'] = NULL;
	}

	public function charset($charset)
	{
		$this->execute_command('PRAGMA encoding = "'.$charset.'"');
	}

	/**
	 * Quote a literal value for inclusion in a SQL query
	 *
	 * @uses Database_PDO::escape()
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function quote_literal($value)
	{
		if (is_object($value) OR is_string($value))
			return $this->escape($value);

		return parent::quote_literal($value);
	}
}
