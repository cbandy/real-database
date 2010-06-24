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
class Database_MySQL extends Database implements Database_iEscape, Database_iInsert
{
	/**
	 * @var boolean Whether or not mysql_set_charset() exists
	 */
	protected static $_SET_CHARSET;

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

	protected $_quote = '`';

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

		if ( ! isset($this->_config['schema']))
		{
			$this->_config['schema'] = '';
		}
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
		$this->_connection or $this->connect();

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

		if ( ! mysql_select_db($database, $this->_connection))
			throw new Database_Exception(':error', array(':error' => mysql_error($this->_connection)), mysql_errno($this->_connection));

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

	public function table_prefix()
	{
		return $this->_config['schema'];
	}
}

// Static initialization
Database_MySQL::initialize();
