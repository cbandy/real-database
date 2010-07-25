<?php

/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_MySQL extends Database implements Database_iEscape, Database_iInsert, Database_iIntrospect
{
	/**
	 * @var boolean Whether or not mysql_set_charset() exists
	 */
	protected static $_SET_CHARSET;

	/**
	 * @see Database_MySQL::_select_database()
	 *
	 * @var array   Active databases
	 */
	protected static $_databases;

	/**
	 * Initialize runtime constants
	 *
	 * @link http://php.net/manual/function.mysql-set-charset
	 *
	 * @return  void
	 */
	public static function initialize()
	{
		// Only available in PHP >= 5.2.3 when compiled against MySQL >= 5.0.7
		Database_MySQL::$_SET_CHARSET = function_exists('mysql_set_charset');
	}

	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  Database_Command_Insert_Identity
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_Command_Insert_Identity($table, $columns);
	}

	/**
	 * @var resource    Link identifier
	 */
	protected $_connection;

	/**
	 * @var string  Persistent connection hash according to PHP driver
	 */
	protected $_connection_id;

	protected $_quote = '`';

	/**
	 * Create a MySQL connection
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | string  | Character set
	 *  profiling             | boolean | Enable execution profiling
	 *  table_prefix          | string  | Table prefix
	 *  connection.database   | string  |
	 *  connection.flags      | integer | Combination of client constants, e.g. MYSQL_CLIENT_SSL
	 *  connection.hostname   | string  | Server address or path to a local socket
	 *  connection.password   | string  |
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.port       | integer | Server port
	 *  connection.username   | string  |
	 *
	 * @link http://php.net/manual/mysql.constants Client constants
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name   Instance name
	 * @param   array   $config Configuration
	 */
	protected function __construct($name, $config)
	{
		parent::__construct($name, $config);

		if ( ! isset($this->_config['connection']['flags']))
		{
			$this->_config['connection']['flags'] = 0;
		}

		if ( ! empty($this->_config['connection']['port']))
		{
			$this->_config['connection']['hostname'] .= ':'.$this->_config['connection']['port'];
		}

		if ( ! isset($this->_config['table_prefix']))
		{
			$this->_config['table_prefix'] = '';
		}

		$this->_connection_id = $this->_config['connection']['hostname'].'_'.$this->_config['connection']['username'].'_'.$this->_config['connection']['password'].'_'.$this->_config['connection']['flags'];
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
		elseif ( ! empty($this->_config['connection']['persistent']) AND $this->_config['connection']['database'] !== Database_MySQL::$_databases[$this->_connection_id])
		{
			// Select database on persistent connections
			$this->_select_database($this->_config['connection']['database']);
		}

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", $statement);
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

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ($result === FALSE)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => mysql_error($this->_connection)), mysql_errno($this->_connection));
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
			throw new Database_Exception(':error', array(':error' => mysql_error($this->_connection)), mysql_errno($this->_connection));

		Database_MySQL::$_databases[$this->_connection_id] = $database;
	}

	public function begin()
	{
		$this->_execute('START TRANSACTION');
	}

	public function charset($charset)
	{
		if ( ! Database_MySQL::$_SET_CHARSET)
			return parent::charset($charset);

		$this->_connection or $this->connect();

		if ( ! mysql_set_charset($charset, $this->_connection))
			throw new Database_Exception(':error', array(':error' => mysql_error($this->_connection)), mysql_errno($this->_connection));
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
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ( ! is_resource($this->_connection))
			throw new Database_Exception('Unable to connect to MySQL ":name"', array(':name' => $this->_instance));

		$this->_select_database($database);

		if ( ! empty($this->_config['charset']))
		{
			$this->charset($this->_config['charset']);
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

		$value = mysql_real_escape_string((string) $value, $this->_connection);

		return "'$value'";
	}

	public function execute_command($statement)
	{
		if (empty($statement))
			return 0;

		$result = $this->_execute($statement);

		if (is_resource($result))
		{
			mysql_free_result($result);
		}

		return mysql_affected_rows($this->_connection);
	}

	public function execute_insert($statement)
	{
		return array($this->execute_command($statement), mysql_insert_id($this->_connection));
	}

	public function execute_query($statement, $as_object = FALSE)
	{
		if (empty($statement))
			return NULL;

		$result = $this->_execute($statement);

		if (is_bool($result))
			return NULL;

		return new Database_MySQL_Result($result, $as_object);
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
	 * Retrieve the columns of a table in a format almost identical to that of
	 * the SQL-92 Information Schema. Includes five non-standard fields:
	 * `column_type`, `column_key`, `extra`, `privileges` and `column_comment`.
	 *
	 * ENUM and SET also have their possible values extracted into `options`.
	 *
	 * @link http://dev.mysql.com/doc/en/columns-table.html
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @return  array
	 */
	public function table_columns($table)
	{
		if ($table instanceof Database_Identifier)
		{
			$schema = $table->namespace;
			$table = $table->name;
		}
		elseif (is_array($table))
		{
			$schema = $table;
			$table = array_pop($schema);
		}
		else
		{
			$schema = explode('.', $table);
			$table = array_pop($schema);
		}

		if (empty($schema))
		{
			$schema = $this->_config['connection']['database'];
		}

		$result =
			'SELECT column_name, ordinal_position, column_default, is_nullable, data_type, character_maximum_length, numeric_precision, numeric_scale, collation_name,'
			.'   column_type, column_key, extra, privileges, column_comment'
			.' FROM information_schema.columns'
			.' WHERE table_schema = '.$this->quote_literal($schema).' AND table_name = '.$this->quote_literal($this->table_prefix().$table);

		$result = $this->execute_query($result)->as_array('column_name');

		foreach ($result as & $column)
		{
			if ($column['data_type'] === 'enum' OR $column['data_type'] === 'set')
			{
				$open = strpos($column['column_type'], '(');
				$close = strpos($column['column_type'], ')', $open);

				// Text between parentheses without single quotes
				$column['options'] = explode("','", substr($column['column_type'], $open + 2, $close - 3 - $open));
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

// Static initialization
Database_MySQL::initialize();
