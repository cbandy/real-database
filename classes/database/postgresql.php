<?php

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PostgreSQL extends Database implements Database_iEscape
{
	/**
	 * @link http://bugs.php.net/51607
	 * @var boolean
	 */
	protected static $_BUG_COPY_QUOTE_TABLE;

	/**
	 * @link http://bugs.php.net/51609
	 * @var boolean
	 */
	protected static $_BUG_COPY_TO_NULL;

	/**
	 * @link http://bugs.php.net/50195
	 * @var boolean
	 */
	protected static $_BUG_COPY_TO_SCHEMA;

	/**
	 * Create a DELETE command
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  Database_PostgreSQL_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_PostgreSQL_Delete($table, $alias);
	}

	/**
	 * Initialize runtime constants
	 *
	 * @link http://php.net/manual/reserved.constants
	 *
	 * @return  void
	 */
	public static function initialize()
	{
		if (defined('PHP_VERSION_ID'))
		{
			// Only available in PHP >= 5.2.7
			$php_version_id = PHP_VERSION_ID;
		}
		else
		{
			list($major, $minor, $release) = explode('.', PHP_VERSION);
			$php_version_id = $major * 10000 + $minor * 100 + $release;
		}

		if ($php_version_id < 50300)
		{
			Database_PostgreSQL::$_BUG_COPY_QUOTE_TABLE = $php_version_id < 50214;
			Database_PostgreSQL::$_BUG_COPY_TO_NULL     = $php_version_id < 50214;
			Database_PostgreSQL::$_BUG_COPY_TO_SCHEMA   = $php_version_id < 50212;
		}
		else
		{
			Database_PostgreSQL::$_BUG_COPY_QUOTE_TABLE = $php_version_id < 50303;
			Database_PostgreSQL::$_BUG_COPY_TO_NULL     = $php_version_id < 50303;
			Database_PostgreSQL::$_BUG_COPY_TO_SCHEMA   = $php_version_id < 50302;
		}
	}

	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  Database_PostgreSQL_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_PostgreSQL_Insert($table, $columns);
	}

	/**
	 * Create a SELECT query
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 * @return  Database_PostgreSQL_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_PostgreSQL_Select($columns);
	}

	/**
	 * Create an UPDATE command
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @param   array   $values Hash of (column => value) assignments
	 * @return  Database_PostgreSQL_Update
	 */
	public static function update($table = NULL, $alias = NULL, $values = NULL)
	{
		return new Database_PostgreSQL_Update($table, $alias, $values);
	}

	/**
	 * @var resource    Unique connection to the server
	 */
	protected $_connection;

	protected $_placeholder = '/(?:\?|(?<=::|[^:]):\w++)/';

	/**
	 * @var string  Table prefix
	 */
	protected $_prefix;

	/**
	 * @var string  Default schema
	 */
	protected $_schema;

	/**
	 * @var string  Version of the connected server
	 */
	protected $_version;

	protected function __construct($name, $config)
	{
		parent::__construct($name, $config);

		if (empty($this->_config['schema']))
		{
			$this->_prefix = $this->_schema = '';
		}
		else
		{
			// Separate table prefix from schema
			$schema = explode('.', $this->_config['schema'], 2);
			$this->_schema = reset($schema);
			$this->_prefix = (string) next($schema);
		}

		if (empty($this->_config['connection']['info']))
		{
			// Build connection string
			$this->_config['connection']['info'] = '';

			extract($this->_config['connection']);

			if ( ! empty($hostname))
			{
				$info .= "host='$hostname'";
			}

			if ( ! empty($port))
			{
				$info .= " port='$port'";
			}

			if ( ! empty($username))
			{
				$info .= " user='$username'";
			}

			if ( ! empty($password))
			{
				$info .= " password='$password'";
			}

			if ( ! empty($database))
			{
				$info .= " dbname='$database'";
			}

			if (isset($ssl))
			{
				if ($ssl === TRUE)
				{
					$info .= " sslmode='require'";
				}
				elseif ($ssl === FALSE)
				{
					$info .= " sslmode='disable'";
				}
				else
				{
					$info .= " sslmode='$ssl'";
				}
			}

			$this->_config['connection']['info'] = $info;
		}
	}

	/**
	 * Evaluate a result resource as though it were a command
	 *
	 * Frees the resource.
	 *
	 * @throws  Database_Exception
	 * @param   resource    Result resource
	 * @return  integer Number of affected rows
	 */
	protected function _evaluate_command($result)
	{
		$status = pg_result_status($result);

		if ($status === PGSQL_COMMAND_OK)
		{
			$rows = pg_affected_rows($result);
		}
		elseif ($status === PGSQL_TUPLES_OK)
		{
			$rows = pg_num_rows($result);
		}
		else
		{
			if ($status === PGSQL_BAD_RESPONSE OR $status === PGSQL_NONFATAL_ERROR OR $status === PGSQL_FATAL_ERROR)
				throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

			if ($status === PGSQL_COPY_IN OR $status === PGSQL_COPY_OUT)
			{
				pg_end_copy($this->_connection);
			}

			$rows = 0;
		}

		pg_free_result($result);

		return $rows;
	}

	/**
	 * Evaluate a result resource as though it were a query
	 *
	 * Frees the resource.
	 *
	 * @throws  Database_Exception
	 * @param   resource    $result     Result resource
	 * @param   mixed       $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 * @return  Database_Result Result set or NULL
	 */
	protected function _evaluate_query($result, $as_object)
	{
		$status = pg_result_status($result);

		if ($status === PGSQL_TUPLES_OK)
			return new Database_PostgreSQL_Result($result, $as_object);

		if ($status === PGSQL_BAD_RESPONSE OR $status === PGSQL_NONFATAL_ERROR OR $status === PGSQL_FATAL_ERROR)
			throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

		if ($status === PGSQL_COPY_IN OR $status === PGSQL_COPY_OUT)
		{
			pg_end_copy($this->_connection);
		}

		pg_free_result($result);

		return NULL;
	}

	/**
	 * Execute a statement after connecting
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL statement
	 * @return  resource    Result resource
	 */
	protected function _execute($statement)
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", $statement);
		}

		if ( ! pg_send_query($this->_connection, $statement))
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));
		}

		if ( ! $result = pg_get_result($this->_connection))
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Execute a prepared statement after connecting
	 *
	 * @throws  Database_Exception
	 * @param   string  $name       Statement name
	 * @param   array   $parameters Unquoted parameters
	 * @return  resource    Result resource
	 */
	protected function _execute_prepared($name, $parameters)
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", "Prepared: $name");
		}

		if ( ! pg_send_execute($this->_connection, $name, $parameters))
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));
		}

		if ( ! $result = pg_get_result($this->_connection))
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Recursively replace Expression and Identifier parameters until all
	 * parameters are unquoted literals
	 *
	 * @param   string  $statement          SQL statement with (or without) placeholders
	 * @param   array   $parameters         Unquoted parameters
	 * @param   array   $result_parameters  Parameters for the resulting statement
	 * @return  string  SQL statement
	 */
	protected function _parse($statement, $parameters, & $result_parameters)
	{
		$chunks = preg_split($this->_placeholder, $statement, NULL, PREG_SPLIT_OFFSET_CAPTURE);

		$max = count($chunks);
		$names = NULL;
		$position = 0;
		$prev = $chunks[0];
		$result = $prev[0];

		for ($i = 1; $i < $max; ++$i)
		{
			if ($statement[$chunks[$i][1] - 1] === '?')
			{
				$placeholder = $position++;
			}
			else
			{
				$offset = $prev[1] + strlen($prev[0]);
				$placeholder = substr($statement, $offset, $chunks[$i][1] - $offset);
			}

			//if ( ! array_key_exists($placeholder, $parameters))
			//	throw new Database_Exception('Expression lacking parameter ":param"', array(':param' => $placeholder));

			$value = $parameters[$placeholder];

			if ($value instanceof Database_Expression)
			{
				$result .= $this->_parse($value->__toString(), $value->parameters, $result_parameters);
			}
			elseif ($value instanceof Database_Identifier)
			{
				$result .= $this->quote($value);
			}
			elseif (is_int($placeholder))
			{
				$result_parameters[] = $value;
				$result .= '$'.count($result_parameters);
			}
			else
			{
				if ( ! isset($names[$placeholder]))
				{
					$result_parameters[] = $value;
					$names[$placeholder] = '$'.count($result_parameters);
				}

				$result .= $names[$placeholder];
			}

			$prev = $chunks[$i];
			$result .= $prev[0];
		}

		return $result;
	}

	/**
	 * Start a transaction
	 *
	 * @link http://www.postgresql.org/docs/current/static/sql-set-transaction.html
	 *
	 * @throws  Database_Exception
	 * @param   string  $mode   Transaction mode
	 * @return  void
	 */
	public function begin($mode = NULL)
	{
		$result = $this->_execute("BEGIN $mode");

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

		pg_free_result($result);
	}

	public function charset($charset)
	{
		$this->_connection or $this->connect();

		if (pg_set_client_encoding($this->_connection, $charset) !== 0)
			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));
	}

	public function commit()
	{
		$result = $this->_execute('COMMIT');

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

		pg_free_result($result);
	}

	public function connect()
	{
		try
		{
			// Raises E_WARNING upon error
			$this->_connection = empty($this->_config['connection']['persistent'])
				? pg_connect($this->_config['connection']['info'], PGSQL_CONNECT_FORCE_NEW)
				: pg_pconnect($this->_config['connection']['info'], PGSQL_CONNECT_FORCE_NEW);
		}
		catch (Exception $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ( ! is_resource($this->_connection))
			throw new Database_Exception('Unable to connect to PostgreSQL ":name"', array(':name' => $this->_instance));

		$this->_version = pg_parameter_status($this->_connection, 'server_version');

		if ( ! empty($this->_config['charset']))
		{
			$this->charset($this->_config['charset']);
		}

		if ($this->_schema)
		{
			$result = $this->_execute('SET search_path = '.$this->_schema.', pg_catalog');

			if (pg_result_status($result) !== PGSQL_COMMAND_OK)
				throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

			pg_free_result($result);
		}
	}

	/**
	 * Insert records into a table from an array of strings describing each row
	 *
	 * @throws  Database_Exception
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $rows       Each element is a delimited string
	 * @param   string  $delimiter  Column delimiter
	 * @param   string  $null       NULL representation
	 * @return  void
	 */
	public function copy_from($table, $rows, $delimiter = "\t", $null = '\\N')
	{
		$table = $this->quote_table($table);

		if (Database_PostgreSQL::$_BUG_COPY_QUOTE_TABLE)
		{
			$table = trim($table, $this->_quote);
		}

		try
		{
			// Raises E_WARNING on error
			$result = pg_copy_from($this->_connection, $table, $rows, addslashes($delimiter), addslashes($null));
		}
		catch (Exception $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ( ! $result)
			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));
	}

	/**
	 * Retrieve records from a table into an array of strings describing each row
	 *
	 * @throws  Database_Exception
	 * @throws  Kohana_Exception
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   string  $delimiter  Column delimiter
	 * @param   string  $null       NULL representation
	 * @return  array   Rows from the table as delimited strings
	 */
	public function copy_to($table, $delimiter = "\t", $null = '\\N')
	{
		if ( ! Database_PostgreSQL::$_BUG_COPY_QUOTE_TABLE)
		{
			$table = $this->quote_table($table);
		}
		elseif (Database_PostgreSQL::$_BUG_COPY_TO_SCHEMA)
		{
			$table = trim($this->quote_table($table), $this->_quote);
		}
		else
		{
			if ( ! $table instanceof Database_Identifier)
			{
				$table = new Database_Table($table);
			}

			if (empty($table->namespace))
			{
				$table = trim($this->quote_table($table), $this->_quote);
			}
			else
			{
				$table = $this->quote_table($table);
			}
		}

		if (Database_PostgreSQL::$_BUG_COPY_TO_NULL)
		{
			if ($null !== '\\N')
				throw new Kohana_Exception('Setting the NULL representation is broken before PHP 5.2.14 and 5.3.3');

			try
			{
				// Raises E_WARNING on error
				$result = pg_copy_to($this->_connection, $table, addslashes($delimiter));
			}
			catch (Exception $e)
			{
				throw new Database_Exception(':error', array(':error' => $e->getMessage()));
			}
		}
		else
		{
			try
			{
				// Raises E_WARNING on error
				$result = pg_copy_to($this->_connection, $table, addslashes($delimiter), addslashes($null));
			}
			catch (Exception $e)
			{
				throw new Database_Exception(':error', array(':error' => $e->getMessage()));
			}
		}

		if ($result === FALSE)
			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));

		return $result;
	}

	public function disconnect()
	{
		if (is_resource($this->_connection))
		{
			pg_close($this->_connection);

			$this->_connection = NULL;
		}
	}

	/**
	 * Quote a value while escaping characters that could cause a SQL injection
	 * attack.
	 *
	 * @link http://archives.postgresql.org/pgsql-php/2007-02/msg00014.php
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function escape($value)
	{
		$this->_connection or $this->connect();

		// PHP >= 6.0.0
		//if (is_binary($value))
		if ($value instanceof Database_Binary)
		{
			$value = pg_escape_bytea($this->_connection, $value);
		}
		else
		{
			$value = pg_escape_string($this->_connection, $value);
		}

		return "'$value'";
	}

	public function execute_command($statement)
	{
		return $this->_evaluate_command($this->_execute($statement));
	}

	/**
	 * Execute a prepared command, returning the number of affected rows
	 *
	 * @throws  Database_Exception
	 * @param   string  $name       Statement name
	 * @param   array   $parameters Unquoted parameters
	 * @return  integer Number of affected rows
	 */
	public function execute_prepared_command($name, $parameters = array())
	{
		return $this->_evaluate_command($this->_execute_prepared($name, $parameters));
	}

	/**
	 * Execute a prepared query, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement)
	 *
	 * @throws  Database_Exception
	 * @param   string  $name       Statement name
	 * @param   array   $parameters Unquoted parameters
	 * @return  Database_Result Result set or NULL
	 */
	public function execute_prepared_query($name, $parameters = array(), $as_object = FALSE)
	{
		return $this->_evaluate_query($this->_execute_prepared($name, $parameters), $as_object);
	}

	public function execute_query($statement, $as_object = FALSE)
	{
		if (empty($statement))
			return NULL;

		return $this->_evaluate_query($this->_execute($statement), $as_object);
	}

	/**
	 * Create a prepared statement after connecting
	 *
	 * @link http://php.net/manual/function.pg-prepare
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

		$this->_connection or $this->connect();

		if ( ! pg_send_prepare($this->_connection, $name, $statement))
			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));

		if ( ! $result = pg_get_result($this->_connection))
			throw new Database_Exception(':error', array(':error' => pg_last_error($this->_connection)));

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

		pg_free_result($result);

		return $name;
	}

	public function prepare_command($statement, $parameters = array())
	{
		$params = array();
		$statement = $this->_parse($statement, $parameters, $params);
		$name = $this->prepare(NULL, $statement);

		return new Database_PostgreSQL_Command($this, $name, $statement, $params);
	}

	public function prepare_query($statement, $parameters = array())
	{
		$params = array();
		$statement = $this->_parse($statement, $parameters, $params);
		$name = $this->prepare(NULL, $statement);

		return new Database_PostgreSQL_Query($this, $name, $statement, $params);
	}

	/**
	 * Quote a literal value for inclusion in a SQL query
	 *
	 * @uses Database_PostgreSQL::escape()
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

	/**
	 * Abort the current transaction or roll back to a savepoint
	 *
	 * @throws  Database_Exception
	 * @param   string  $savepoint  Savepoint name
	 * @return  void
	 */
	public function rollback($savepoint = NULL)
	{
		$result = $this->_execute($savepoint ? "ROLLBACK TO $savepoint" : 'ROLLBACK');

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

		pg_free_result($result);
	}

	/**
	 * Define a new savepoint in the current transaction
	 *
	 * @throws  Database_Exception
	 * @param   string  $name   Savepoint name
	 * @return  void
	 */
	public function savepoint($name)
	{
		$result = $this->_execute("SAVEPOINT $name");

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_Exception(':error', array(':error' => pg_result_error($result)));

		pg_free_result($result);
	}

	public function table_prefix()
	{
		return $this->_prefix;
	}
}

// Static initialization
Database_PostgreSQL::initialize();
