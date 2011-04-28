<?php

/**
 * [MySQL](http://www.mysql.com/) connection and expression factory.
 *
 * [!!] Requires MySQL >= 5.0.7
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/book.mysql
 */
class Database_MySQL extends Database
	implements Database_iEscape, Database_iIntrospect
{
	/**
	 * @see Database_MySQL::_select_database()
	 *
	 * @var array   Active databases
	 */
	protected static $_databases;

	public static function alter_table($name = NULL)
	{
		return new Database_MySQL_Alter_Table($name);
	}

	public static function create($type, $name = NULL)
	{
		$type = strtoupper($type);

		if ($type === 'INDEX')
			return new Database_MySQL_Create_Index($name);

		if ($type === 'TABLE')
			return new Database_MySQL_Create_Table($name);

		if ($type === 'VIEW')
			return new Database_MySQL_Create_View($name);

		// @codeCoverageIgnoreStart
		return parent::create($type, $name);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Create a column expression
	 *
	 * @param   mixed   $name   Converted to SQL_Column
	 * @param   mixed   $type   Converted to SQL_Expression
	 * @return  Database_MySQL_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_MySQL_DDL_Column($name, $type);
	}

	/**
	 * @var resource    Link identifier
	 */
	protected $_connection;

	/**
	 * @var string  Persistent connection hash according to PHP driver
	 */
	protected $_connection_id;

	protected $_quote_left = '`';

	protected $_quote_right = '`';

	/**
	 * Create a MySQL connection
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | string  | Character set
	 *  profiling             | boolean | Enable execution profiling
	 *  table_prefix          | string  | Table prefix
	 *  variables             | array   | [System variables][] as "key => value" pairs
	 *  connection.database   | string  |
	 *  connection.flags      | integer | Combination of [client constants][], e.g. MYSQL_CLIENT_SSL
	 *  connection.hostname   | string  | Server address or path to a local socket. Use `'127.0.0.1'` to [connect locally using TCP/IP][loopback]
	 *  connection.password   | string  |
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.port       | integer | Server port
	 *  connection.username   | string  |
	 *
	 * [Client constants]: http://php.net/manual/mysql.constants
	 * [Loopback]:         http://dev.mysql.com/doc/en/can-not-connect-to-server.html
	 * [System variables]: http://dev.mysql.com/doc/en/dynamic-system-variables.html
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct($name, $config);

		if ( ! isset($this->_config['connection']['flags']))
		{
			$this->_config['connection']['flags'] = 0;
		}

		if ( ! empty($this->_config['connection']['port']))
		{
			$this->_config['connection']['hostname'] .=
				':'.$this->_config['connection']['port'];
		}

		if ( ! isset($this->_config['table_prefix']))
		{
			$this->_config['table_prefix'] = '';
		}

		$this->_connection_id = $this->_config['connection']['hostname']
			.'_'.$this->_config['connection']['username']
			.'_'.$this->_config['connection']['password']
			.'_'.$this->_config['connection']['flags'];
	}

	/**
	 * Execute a statement after connecting
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL statement
	 * @return  resource|TRUE   Result resource for a query or TRUE for a command
	 */
	protected function _execute($statement)
	{
		if ( ! $this->_connection)
		{
			$this->connect();
		}
		elseif ( ! empty($this->_config['connection']['persistent'])
			AND $this->_config['connection']['database']
				!== Database_MySQL::$_databases[$this->_connection_id])
		{
			// Select database on persistent connections
			$this->_select_database($this->_config['connection']['database']);
		}

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_name)", $statement);
		}

		try
		{
			// Raises E_WARNING upon error
			$result = mysql_query($statement, $this->_connection);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => $e->getMessage()),
				$e->getCode()
			);
		}

		if ($result === FALSE)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection)
			);
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Set and track the active database
	 *
	 * @throws  Database_Exception
	 * @param   string  $database   Database
	 * @return  void
	 */
	protected function _select_database($database)
	{
		if ( ! mysql_select_db($database, $this->_connection))
			throw new Database_Exception(
				':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection)
			);

		Database_MySQL::$_databases[$this->_connection_id] = $database;
	}

	public function begin()
	{
		$this->_execute('START TRANSACTION');
	}

	public function charset($charset)
	{
		$this->_connection or $this->connect();

		if ( ! mysql_set_charset($charset, $this->_connection))
			throw new Database_Exception(
				':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection)
			);
	}

	public function commit()
	{
		$this->_execute('COMMIT');
	}

	/**
	 * Connect
	 *
	 * @link http://php.net/manual/function.mysql-connect
	 * @link http://php.net/manual/ini.core#ini.sql.safe-mode
	 *
	 * @todo SQL Safe Mode can be supported, but only for _one_ MySQL instance
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	public function connect()
	{
		extract($this->_config['connection']);

		try
		{
			// Raises E_NOTICE when sql.safe_mode is set
			// Raises E_WARNING upon error
			$this->_connection = empty($persistent)
				? mysql_connect($hostname, $username, $password, TRUE, $flags)
				: mysql_pconnect($hostname, $username, $password, $flags);
		}
		catch (Exception $e)
		{
			// @codeCoverageIgnoreStart
			throw new Database_Exception(
				':error',
				array(':error' => $e->getMessage()),
				$e->getCode()
			);
			// @codeCoverageIgnoreEnd
		}

		if ( ! is_resource($this->_connection))
			throw new Database_Exception(
				'Unable to connect to MySQL ":name"',
				array(':name' => $this->_name)
			);

		$this->_select_database($database);

		if ( ! empty($this->_config['charset']))
		{
			$this->charset($this->_config['charset']);
		}

		if ( ! empty($this->_config['variables']))
		{
			foreach ($this->_config['variables'] as $variable => $value)
			{
				$this->_execute(
					'SET SESSION '.$variable.' = '.$this->quote_literal($value)
				);
			}
		}
	}

	public function disconnect()
	{
		if (is_resource($this->_connection))
		{
			mysql_close($this->_connection);

			$this->_connection = NULL;
		}
	}

	/**
	 * Return information about a MySQL data type
	 *
	 * @link http://dev.mysql.com/doc/en/data-types.html
	 *
	 * @param   string  $type       SQL data type
	 * @param   string  $attribute  Attribute to return
	 * @return  array|mixed Array of attributes or an attribute value
	 */
	public function datatype($type, $attribute = NULL)
	{
		static $types = array
		(
			'blob'                      => array('type' => 'binary'),
			'bool'                      => array('type' => 'boolean'),
			'bigint unsigned'           => array('type' => 'integer', 'min' => '0', 'max' => '18446744073709551615'),
			'datetime'                  => array('type' => 'string'),
			'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'double'                    => array('type' => 'float'),
			'double precision unsigned' => array('type' => 'float', 'min' => '0'),
			'double unsigned'           => array('type' => 'float', 'min' => '0'),
			'enum'                      => array('type' => 'string'),
			'fixed'                     => array('type' => 'float', 'exact' => TRUE),
			'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'float unsigned'            => array('type' => 'float', 'min' => '0'),
			'int unsigned'              => array('type' => 'integer', 'min' => '0', 'max' => '4294967295'),
			'integer unsigned'          => array('type' => 'integer', 'min' => '0', 'max' => '4294967295'),
			'longblob'                  => array('type' => 'binary'),
			'longtext'                  => array('type' => 'string'),
			'mediumblob'                => array('type' => 'binary'),
			'mediumint'                 => array('type' => 'integer', 'min' => '-8388608', 'max' => '8388607'),
			'mediumint unsigned'        => array('type' => 'integer', 'min' => '0', 'max' => '16777215'),
			'mediumtext'                => array('type' => 'string'),
			'national varchar'          => array('type' => 'string'),
			'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'nvarchar'                  => array('type' => 'string'),
			'point'                     => array('type' => 'binary'),
			'real unsigned'             => array('type' => 'float', 'min' => '0'),
			'set'                       => array('type' => 'string'),
			'smallint unsigned'         => array('type' => 'integer', 'min' => '0', 'max' => '65535'),
			'text'                      => array('type' => 'string'),
			'tinyblob'                  => array('type' => 'binary'),
			'tinyint'                   => array('type' => 'integer', 'min' => '-128', 'max' => '127'),
			'tinyint unsigned'          => array('type' => 'integer', 'min' => '0', 'max' => '255'),
			'tinytext'                  => array('type' => 'string'),
			'year'                      => array('type' => 'string'),
		);

		// Strip ZEROFILL attribute
		$type = str_replace(' zerofill', '', $type);

		if ( ! isset($types[$type]))
			return parent::datatype($type, $attribute);

		if ($attribute !== NULL)
			return @$types[$type][$attribute];

		return $types[$type];
	}

	public function escape($value)
	{
		$this->_connection or $this->connect();

		$value = mysql_real_escape_string( (string) $value, $this->_connection);

		return "'$value'";
	}

	public function execute_command($statement)
	{
		if ( ! is_string($statement))
		{
			$statement = $this->quote($statement);
		}

		if (empty($statement))
			return 0;

		$result = $this->_execute($statement);

		if (is_resource($result))
		{
			mysql_free_result($result);
		}

		return mysql_affected_rows($this->_connection);
	}

	/**
	 * Execute an INSERT statement, returning the number of affected rows and
	 * the AUTO_INCREMENT of the first row.
	 *
	 * @throws  Database_Exception
	 * @param   string|SQL_Expression   $statement  SQL insert
	 * @param   mixed                   $identity   Ignored
	 * @return  array   List including number of affected rows and the AUTO_INCREMENT of the first row
	 */
	public function execute_insert($statement, $identity)
	{
		$rows = $this->execute_command($statement);
		$result = $this->_connection ? mysql_insert_id($this->_connection) : 0;

		return array($rows, $result);
	}

	public function execute_query($statement, $as_object = FALSE, $arguments = array())
	{
		if ( ! is_string($statement))
		{
			$statement = $this->quote($statement);
		}

		if (empty($statement))
			return NULL;

		$result = $this->_execute($statement);

		if (is_bool($result))
			return NULL;

		return new Database_MySQL_Result($result, $as_object, $arguments);
	}

	/**
	 * Create a prepared statement after connecting.
	 *
	 * @link http://dev.mysql.com/doc/en/prepare.html
	 *
	 * @throws  Database_Exception
	 * @param   string  $name       Statement name or NULL to have one generated
	 * @param   string  $statement  SQL statement
	 * @return  string  Statement name
	 */
	public function prepare($name, $statement)
	{
		if ($name === NULL)
		{
			$name = 'kohana_'.sha1($statement);
		}

		$this->_execute(
			'PREPARE '.$this->quote_identifier($name)
			.' FROM '.$this->quote_literal($statement)
		);

		return $name;
	}

	/**
	 * Created a prepared statement from a SQL expression object.
	 *
	 * @throws  Database_Exception
	 * @param   SQL_Expression  $statement  SQL statement
	 * @return  Database_MySQL_Statement
	 */
	public function prepare_statement($statement)
	{
		$parameters = array();

		$statement = $this->_parse(
			(string) $statement,
			$statement->parameters,
			$parameters
		);

		$name = $this->prepare(NULL, $statement);

		$result = new Database_MySQL_Statement($this, $name, $parameters);
		$result->statement = $statement;

		return $result;
	}

	/**
	 * Quote a literal value for inclusion in a SQL query
	 *
	 * @uses Database_MySQL::escape()
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

	public function rollback()
	{
		$this->_execute('ROLLBACK');
	}

	/**
	 * Retrieve the tables of a schema in a format almost identical to that of
	 * the Tables table of the SQL-92 Information Schema. Includes four non-
	 * standard fields: `engine`, `auto_increment`, `table_collation` and
	 * `table_comment`.
	 *
	 * @link http://dev.mysql.com/doc/en/tables-table.html
	 *
	 * @param   array|string|SQL_Identifier $schema Converted to SQL_Identifier. NULL for the default schema.
	 * @return  array
	 */
	public function schema_tables($schema = NULL)
	{
		if ( ! $schema)
		{
			// Use default schema
			$schema = $this->_config['connection']['database'];
		}
		else
		{
			if ( ! $schema instanceof SQL_Identifier)
			{
				// Convert to identifier
				$schema = new SQL_Identifier($schema);
			}

			$schema = $schema->name;
		}

		$sql =
			'SELECT table_name, table_type,'
			.'   engine, auto_increment, table_collation, table_comment'
			.' FROM information_schema.tables WHERE table_schema = '
			.$this->quote_literal($schema);

		if ( ! $prefix = $this->table_prefix())
		{
			// No table prefix
			return $this->execute_query($sql)->as_array('table_name');
		}

		// Filter on table prefix
		$sql .= " AND table_name LIKE '"
			.strtr($prefix, array('_' => '\_', '%' => '\%'))
			."%'";

		$prefix = strlen($prefix);
		$result = array();

		foreach ($this->execute_query($sql) as $table)
		{
			// Strip table prefix from table name
			$table['table_name'] = substr($table['table_name'], $prefix);
			$result[$table['table_name']] = $table;
		}

		return $result;
	}

	/**
	 * Retrieve the columns of a table in a format almost identical to that of
	 * the SQL-92 Information Schema. Includes five non-standard fields:
	 * `column_type`, `column_key`, `extra`, `privileges` and `column_comment`.
	 *
	 * ENUM and SET also have their possible values extracted into `options`.
	 *
	 * @link http://dev.mysql.com/doc/en/columns-table.html
	 *
	 * @param   array|string|SQL_Identifier $table  Converted to SQL_Table unless SQL_Identifier
	 * @return  array
	 */
	public function table_columns($table)
	{
		if ( ! $table instanceof SQL_Identifier)
		{
			// Convert to table
			$table = new SQL_Table($table);
		}

		if ( ! $schema = $table->namespace)
		{
			// Use default schema
			$schema = $this->_config['connection']['database'];
		}

		// Only add table prefix to SQL_Table (exclude from SQL_Identifier)
		$table = ($table instanceof SQL_Table)
			? ($this->table_prefix().$table->name)
			: $table->name;

		$result =
			'SELECT column_name, ordinal_position, column_default, is_nullable,'
			.'   data_type, character_maximum_length,'
			.'   numeric_precision, numeric_scale, collation_name,'
			.'   column_type, column_key, extra, privileges, column_comment'
			.' FROM information_schema.columns'
			.' WHERE table_schema = '.$this->quote_literal($schema)
			.'   AND table_name = '.$this->quote_literal($table);

		$result = $this->execute_query($result)->as_array('column_name');

		foreach ($result as & $column)
		{
			if ($column['data_type'] === 'enum'
				OR $column['data_type'] === 'set')
			{
				$open = strpos($column['column_type'], '(');
				$close = strpos($column['column_type'], ')', $open);

				// Text between parentheses without single quotes
				$column['options'] = explode(
					"','",
					substr(
						$column['column_type'],
						$open + 2,
						$close - 3 - $open
					)
				);
			}
			elseif (strlen($column['column_type']) > 8)
			{
				// Test for UNSIGNED or UNSIGNED ZEROFILL
				if (substr_compare($column['column_type'], 'unsigned', -8) === 0
					OR substr_compare($column['column_type'], 'unsigned', -17, 8) === 0)
				{
					$column['data_type'] .= ' unsigned';
				}
			}
		}

		return $result;
	}

	public function table_prefix()
	{
		return $this->_config['table_prefix'];
	}
}
