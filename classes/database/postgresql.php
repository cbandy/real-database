<?php

/**
 * [PostgreSQL](http://www.postgresql.org/) connection and expression factory.
 *
 * [!!] Requires PostgreSQL >= 8.2
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/book.pgsql
 */
class Database_PostgreSQL extends Database implements Database_iEscape, Database_iIntrospect
{
	/**
	 * @link http://bugs.php.net/51607
	 * @var boolean
	 */
	public static $bug_copy_quote_table;

	/**
	 * @link http://bugs.php.net/51609
	 * @var boolean
	 */
	public static $bug_copy_to_null;

	/**
	 * @link http://bugs.php.net/50195
	 * @var boolean
	 */
	public static $bug_copy_to_schema;

	/**
	 * Create an ALTER TABLE statement.
	 *
	 * @param   mixed   $name   Converted to SQL_Table
	 * @return  Database_PostgreSQL_Alter_Table
	 */
	public static function alter_table($name = NULL)
	{
		return new Database_PostgreSQL_Alter_Table($name);
	}

	/**
	 * Create a CREATE INDEX statement.
	 *
	 * @param   mixed                   $name       Converted to SQL_Identifier
	 * @param   mixed                   $table      Converted to SQL_Table
	 * @param   array|SQL_Expression    $columns    List of columns converted to SQL_Column
	 * @return  Database_PostgreSQL_Create_Index
	 */
	public static function create_index($name = NULL, $table = NULL, $columns = NULL)
	{
		return new Database_PostgreSQL_Create_Index($name, $table, $columns);
	}

	/**
	 * Create a column expression
	 *
	 * @param   mixed   $name   Converted to SQL_Column
	 * @param   mixed   $type   Converted to SQL_Expression
	 * @return  Database_PostgreSQL_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_PostgreSQL_DDL_Column($name, $type);
	}

	/**
	 * Create a DELETE command
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @return  Database_PostgreSQL_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_PostgreSQL_Delete($table, $alias);
	}

	/**
	 * Create an expression for comparing whether or not two values are
	 * distinct.
	 *
	 * @param mixed     $left       Left operand
	 * @param string    $operator   Equality operator
	 * @param mixed     $right      Right operand
	 * @return  Database_PostgreSQL_Identical
	 */
	public static function identical($left, $operator, $right)
	{
		return new Database_PostgreSQL_Identical($left, $operator, $right);
	}

	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $columns    Each element converted to SQL_Column
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
	 * @param   mixed   $table  Converted to SQL_Table
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

	protected $_placeholder = '/(?:\?|(?<=^|::|[^:]):\w++)/';

	/**
	 * @var string  Default schema
	 */
	protected $_schema;

	/**
	 * @var string  Version of the connected server
	 */
	protected $_version;

	/**
	 * Create a PostgreSQL connection
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | string  | Character set
	 *  profiling             | boolean | Enable execution profiling
	 *  search_path           | string  | Initial search_path
	 *  table_prefix          | string  | Table prefix
	 *  connection.database   | string  |
	 *  connection.hostname   | string  | Server address or path to a local socket
	 *  connection.password   | string  |
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.port       | integer | Server port
	 *  connection.ssl        | mixed   | TRUE to require, FALSE to disable, or 'prefer' to negotiate
	 *  connection.username   | string  |
	 *
	 * Instead of separate parameters, the full connection string can be
	 * configured in `connection.info` to be passed directly to `pg_connect()`.
	 *
	 * @link http://www.postgresql.org/docs/current/static/libpq-connect.html Connection string definition
	 * @link http://www.postgresql.org/docs/current/static/ddl-schemas.html#DDL-SCHEMAS-PATH Schema search path
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct($name, $config);

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
			if ($status === PGSQL_BAD_RESPONSE
				OR $status === PGSQL_NONFATAL_ERROR
				OR $status === PGSQL_FATAL_ERROR)
			{
				throw new Database_PostgreSQL_Exception($result);
			}

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
	 * @param   resource        $result     Result resource
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the row class constructor
	 * @return  Database_Result Result set or NULL
	 */
	protected function _evaluate_query($result, $as_object, $arguments)
	{
		$status = pg_result_status($result);

		if ($status === PGSQL_TUPLES_OK)
			return new Database_PostgreSQL_Result($result, $as_object, $arguments);

		if ($status === PGSQL_BAD_RESPONSE
			OR $status === PGSQL_NONFATAL_ERROR
			OR $status === PGSQL_FATAL_ERROR)
		{
			throw new Database_PostgreSQL_Exception($result);
		}

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
			$benchmark = Profiler::start("Database ($this->_name)", $statement);
		}

		if ( ! pg_send_query($this->_connection, $statement))
		{
			// @codeCoverageIgnoreStart
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
			// @codeCoverageIgnoreEnd
		}

		if ( ! $result = pg_get_result($this->_connection))
		{
			// @codeCoverageIgnoreStart
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
			// @codeCoverageIgnoreEnd
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
			$benchmark = Profiler::start(
				"Database ($this->_name)",
				"Prepared: $name"
			);
		}

		if ( ! pg_send_execute($this->_connection, $name, $parameters))
		{
			// @codeCoverageIgnoreStart
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
			// @codeCoverageIgnoreEnd
		}

		if ( ! $result = pg_get_result($this->_connection))
		{
			// @codeCoverageIgnoreStart
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
			// @codeCoverageIgnoreEnd
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Recursively replace array, Expression and Identifier parameters until all
	 * parameters are unquoted literals.
	 *
	 * @param   string  $statement          SQL statement with (or without) placeholders
	 * @param   array   $parameters         Unquoted parameters
	 * @param   array   $result_parameters  Parameters for the resulting statement
	 * @return  string  SQL statement
	 */
	protected function _parse($statement, $parameters, & $result_parameters)
	{
		$chunks = preg_split(
			$this->_placeholder,
			$statement,
			NULL,
			PREG_SPLIT_OFFSET_CAPTURE
		);

		$fragments = NULL;
		$position = 0;
		$prev = $chunks[0];
		$result = $prev[0];

		for ($i = 1, $max = count($chunks); $i < $max; ++$i)
		{
			if ($statement[$chunks[$i][1] - 1] === '?')
			{
				// Positional parameter
				$placeholder = $position++;
				$value = $parameters[$placeholder];

				if (is_array($value))
				{
					$result .= $this->_parse_array($value, $result_parameters);
				}
				elseif ($value instanceof SQL_Expression)
				{
					$result .= $this->_parse(
						(string) $value,
						$value->parameters,
						$result_parameters
					);
				}
				elseif ($value instanceof SQL_Identifier)
				{
					$result .= $this->quote($value);
				}
				else
				{
					// Capture possible reference
					$result_parameters[] =& $parameters[$placeholder];
					$result .= '$'.count($result_parameters);
				}
			}
			else
			{
				// Named parameter
				$offset = $prev[1] + strlen($prev[0]);
				$placeholder = substr(
					$statement,
					$offset,
					$chunks[$i][1] - $offset
				);

				if ( ! isset($fragments[$placeholder]))
				{
					$value = $parameters[$placeholder];

					if (is_array($value))
					{
						$fragments[$placeholder] = $this->_parse_array(
							$value,
							$result_parameters
						);
					}
					elseif ($value instanceof SQL_Expression)
					{
						$fragments[$placeholder] = $this->_parse(
							(string) $value,
							$value->parameters,
							$result_parameters
						);
					}
					elseif ($value instanceof SQL_Identifier)
					{
						$fragments[$placeholder] = $this->quote($value);
					}
					else
					{
						// Capture possible reference
						$result_parameters[] =& $parameters[$placeholder];
						$fragments[$placeholder] = '$'.count($result_parameters);
					}
				}

				$result .= $fragments[$placeholder];
			}

			$prev = $chunks[$i];
			$result .= $prev[0];
		}

		return $result;
	}

	/**
	 * Recursively convert an array to a SQL fragment with parameters consisting
	 * only of unquoted literals.
	 *
	 * @param   array   $array              Unquoted parameters
	 * @param   array   $result_parameters  Parameters for the resulting fragment
	 * @return  string  SQL fragment
	 */
	protected function _parse_array($array, & $result_parameters)
	{
		if (empty($array))
			return '';

		$result = '';

		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$result .= $this->_parse_array($value, $result_parameters);
			}
			elseif ($value instanceof SQL_Expression)
			{
				$result .= $this->_parse(
					(string) $value,
					$value->parameters,
					$result_parameters
				);
			}
			elseif ($value instanceof SQL_Identifier)
			{
				$result .= $this->quote($value);
			}
			else
			{
				// Capture possible reference
				$result_parameters[] =& $array[$key];
				$result .= '$'.count($result_parameters);
			}

			$result .= ', ';
		}

		// Strip trailing comma
		return substr($result, 0, -2);
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
			throw new Database_PostgreSQL_Exception($result);

		pg_free_result($result);
	}

	public function charset($charset)
	{
		$this->_connection or $this->connect();

		if (pg_set_client_encoding($this->_connection, $charset) !== 0)
			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
	}

	public function commit()
	{
		$result = $this->_execute('COMMIT');

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_PostgreSQL_Exception($result);

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
				'Unable to connect to PostgreSQL ":name"',
				array(':name' => $this->_name)
			);

		$this->_version = pg_parameter_status(
			$this->_connection,
			'server_version'
		);

		if ( ! empty($this->_config['charset']))
		{
			$this->charset($this->_config['charset']);
		}

		if ( ! empty($this->_config['search_path']))
		{
			$result = $this->_execute(
				'SET search_path = '.$this->_config['search_path']
			);

			if (pg_result_status($result) !== PGSQL_COMMAND_OK)
				throw new Database_PostgreSQL_Exception($result);

			pg_free_result($result);
		}
	}

	/**
	 * Insert records into a table from an array of strings describing each row
	 *
	 * @throws  Database_Exception
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $rows       Each element is a delimited string
	 * @param   string  $delimiter  Column delimiter
	 * @param   string  $null       NULL representation
	 * @return  void
	 */
	public function copy_from($table, $rows, $delimiter = "\t", $null = '\\N')
	{
		$table = $this->quote_table($table);

		if (Database_PostgreSQL::$bug_copy_quote_table)
		{
			$table = trim($table, $this->_quote);
		}

		$this->_connection or $this->connect();

		try
		{
			// Raises E_WARNING on error
			$result = pg_copy_from(
				$this->_connection,
				$table,
				$rows,
				addslashes($delimiter),
				addslashes($null)
			);
		}
		catch (Exception $e)
		{
			throw new Database_Exception(
				':error',
				array(':error' => $e->getMessage()),
				$e->getCode()
			);
		}

		if ( ! $result)
			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
	}

	/**
	 * Retrieve records from a table into an array of strings describing each row
	 *
	 * @throws  Database_Exception
	 * @throws  Kohana_Exception
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   string  $delimiter  Column delimiter
	 * @param   string  $null       NULL representation
	 * @return  array   Rows from the table as delimited strings
	 */
	public function copy_to($table, $delimiter = "\t", $null = '\\N')
	{
		if ( ! Database_PostgreSQL::$bug_copy_quote_table)
		{
			$table = $this->quote_table($table);
		}
		elseif (Database_PostgreSQL::$bug_copy_to_schema)
		{
			$table = trim($this->quote_table($table), $this->_quote);
		}
		else
		{
			if ( ! $table instanceof SQL_Identifier)
			{
				$table = new SQL_Table($table);
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

		$this->_connection or $this->connect();

		if (Database_PostgreSQL::$bug_copy_to_null)
		{
			if ($null !== '\\N')
				throw new Kohana_Exception(
					'Setting the NULL representation is broken before PHP 5.2.14 and 5.3.3'
				);

			try
			{
				// Raises E_WARNING on error
				$result = pg_copy_to(
					$this->_connection,
					$table,
					addslashes($delimiter)
				);
			}
			catch (Exception $e)
			{
				throw new Database_Exception(
					':error',
					array(':error' => $e->getMessage()),
					$e->getCode()
				);
			}
		}
		else
		{
			try
			{
				// Raises E_WARNING on error
				$result = pg_copy_to(
					$this->_connection,
					$table,
					addslashes($delimiter),
					addslashes($null)
				);
			}
			catch (Exception $e)
			{
				throw new Database_Exception(
					':error',
					array(':error' => $e->getMessage()),
					$e->getCode()
				);
			}
		}

		if ($result === FALSE)
			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);

		return $result;
	}

	/**
	 * Return information about a PostgresSQL data type
	 *
	 * @link http://www.postgresql.org/docs/current/static/datatype.html#DATATYPE-TABLE
	 *
	 * @param   string  $type       SQL data type
	 * @param   string  $attribute  Attribute to return
	 * @return  array|mixed Array of attributes or an attribute value
	 */
	public function datatype($type, $attribute = NULL)
	{
		static $types = array
		(
			// PostgreSQL >= 7.4
			'box'       => array('type' => 'string'),
			'bytea'     => array('type' => 'binary'),
			'cidr'      => array('type' => 'string'),
			'circle'    => array('type' => 'string'),
			'inet'      => array('type' => 'string'),
			'int2'      => array('type' => 'integer', 'min' => '-32768', 'max' => '32767'),
			'int4'      => array('type' => 'integer', 'min' => '-2147483648', 'max' => '2147483647'),
			'int8'      => array('type' => 'integer', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),
			'line'      => array('type' => 'string'),
			'lseg'      => array('type' => 'string'),
			'macaddr'   => array('type' => 'string'),
			'money'     => array('type' => 'float', 'exact' => TRUE, 'min' => '-92233720368547758.08', 'max' => '92233720368547758.07'),
			'path'      => array('type' => 'string'),
			'point'     => array('type' => 'string'),
			'polygon'   => array('type' => 'string'),
			'text'      => array('type' => 'string'),

			// PostgreSQL >= 8.3
			'tsquery'   => array('type' => 'string'),
			'tsvector'  => array('type' => 'string'),
			'uuid'      => array('type' => 'string'),
			'xml'       => array('type' => 'string'),
		);

		if ( ! isset($types[$type]))
			return parent::datatype($type, $attribute);

		if ($attribute !== NULL)
			return @$types[$type][$attribute];

		return $types[$type];
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
	 * Quote a literal value while escaping characters that could cause a SQL
	 * injection attack.
	 *
	 * @link http://archives.postgresql.org/pgsql-php/2007-02/msg00014.php
	 *
	 * @param   mixed   $value  Literal value to quote
	 * @return  string
	 */
	public function escape_literal($value)
	{
		$this->_connection or $this->connect();

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
		if ( ! is_string($statement))
		{
			$statement = $this->quote($statement);
		}

		$rows = $this->_evaluate_command($this->_execute($statement));

		while ($result = pg_get_result($this->_connection))
		{
			$rows += $this->_evaluate_command($result);
		}

		return $rows;
	}

	/**
	 * Execute a SQL statement, returning the value of a column from the first
	 * row.
	 *
	 * @throws  Database_Exception
	 * @param   string|SQL_Expression   $statement  SQL insert
	 * @param   mixed                   $identity   Converted to SQL_Column
	 * @param   string|boolean          $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array                   $arguments  Arguments to pass to the row class constructor
	 * @return  array   List including number of affected rows and a value from the first row
	 */
	public function execute_insert($statement, $identity, $as_object = FALSE, $arguments = array())
	{
		if ( ! $identity instanceof SQL_Expression
			AND ! $identity instanceof SQL_Identifier)
		{
			$identity = new SQL_Column($identity);
		}

		if ($statement instanceof Database_iReturning
			AND ! empty($statement->parameters[':returning']))
		{
			$result = $this->_evaluate_query(
				$this->_execute($this->quote($statement)),
				$as_object,
				$arguments
			);
		}
		else
		{
			if ( ! is_string($statement))
			{
				$statement = $this->quote($statement);
			}

			$result = $this->_evaluate_query(
				$this->_execute(
					$statement.' RETURNING '.$this->quote($identity)
				),
				$as_object,
				$arguments
			);
		}

		$rows = $result->count();
		$result = $result->get(
			($identity instanceof SQL_Identifier) ? $identity->name : NULL
		);

		return array($rows, $result);
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
		return $this->_evaluate_command(
			$this->_execute_prepared($name, $parameters)
		);
	}

	/**
	 * Execute a prepared insert, returning the value of a column from the first
	 * row.
	 *
	 * @throws  Database_Exception
	 * @param   string          $name       Statement name
	 * @param   mixed           $identity   Converted to SQL_Column
	 * @param   array           $parameters Unquoted statement parameters
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the row class constructor
	 * @return  array   List including number of affected rows and a value from the first row
	 */
	public function execute_prepared_insert($name, $identity, $parameters = array(), $as_object = FALSE, $arguments = array())
	{
		if ( ! $identity instanceof SQL_Expression
			AND ! $identity instanceof SQL_Identifier)
		{
			$identity = new SQL_Column($identity);
		}

		$result = $this->_evaluate_query(
			$this->_execute_prepared($name, $parameters),
			$as_object,
			$arguments
		);

		$rows = $result->count();
		$result = $result->get(
			($identity instanceof SQL_Identifier) ? $identity->name : NULL
		);

		return array($rows, $result);
	}

	/**
	 * Execute a prepared query, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement)
	 *
	 * @throws  Database_Exception
	 * @param   string          $name       Statement name
	 * @param   array           $parameters Unquoted parameters
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the row class constructor
	 * @return  Database_Result Result set or NULL
	 */
	public function execute_prepared_query($name, $parameters = array(), $as_object = FALSE, $arguments = array())
	{
		return $this->_evaluate_query(
			$this->_execute_prepared($name, $parameters),
			$as_object,
			$arguments
		);
	}

	public function execute_query($statement, $as_object = FALSE, $arguments = array())
	{
		if (empty($statement))
			return NULL;

		if ( ! is_string($statement))
		{
			$statement = $this->quote($statement);
		}

		return $this->_evaluate_query($this->_execute($statement), $as_object, $arguments);
	}

	/**
	 * Convert a generic [SQL_Expression] into a [Database_Statement] with an
	 * array of literal parameters that map to named positions: $1, $2, etc.
	 *
	 * @param   SQL_Expression  $statement  SQL statement
	 * @return  Database_Statement
	 */
	public function parse_statement($statement)
	{
		$parameters = array();

		$statement = $this->_parse(
			(string) $statement,
			$statement->parameters,
			$parameters
		);

		return new Database_Statement($statement, $parameters);
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
		{
			// @codeCoverageIgnoreStart
			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
			// @codeCoverageIgnoreEnd
		}

		if ( ! $result = pg_get_result($this->_connection))
		{
			// @codeCoverageIgnoreStart
			throw new Database_Exception(
				':error',
				array(':error' => pg_last_error($this->_connection))
			);
			// @codeCoverageIgnoreEnd
		}

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_PostgreSQL_Exception($result);

		pg_free_result($result);

		return $name;
	}

	/**
	 * Created a prepared statement from a PostgreSQL-compatible
	 * [Database_Statement] or a generic [SQL_Expression].
	 *
	 * @throws  Database_Exception
	 * @param   Database_Statement|SQL_Expression   $statement  SQL statement
	 * @return  Database_PostgreSQL_Statement
	 */
	public function prepare_statement($statement)
	{
		if ( ! $statement instanceof Database_Statement)
		{
			$statement = $this->parse_statement($statement);
		}

		$name = $this->prepare(NULL, (string) $statement);

		$result = new Database_PostgreSQL_Statement($this, $name, $statement->parameters());
		$result->statement = (string) $statement;

		return $result;
	}

	/**
	 * Quote a literal value for inclusion in a SQL query.
	 *
	 * @uses Database_PostgreSQL::escape_literal()
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function quote_literal($value)
	{
		if (is_object($value) OR is_string($value))
			return $this->escape_literal($value);

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
		$result = $this->_execute(
			$savepoint ? ('ROLLBACK TO '.$savepoint) : 'ROLLBACK'
		);

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_PostgreSQL_Exception($result);

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
		$result = $this->_execute('SAVEPOINT '.$name);

		if (pg_result_status($result) !== PGSQL_COMMAND_OK)
			throw new Database_PostgreSQL_Exception($result);

		pg_free_result($result);
	}

	/**
	 * Return the default schema
	 *
	 * @return  string
	 */
	public function schema()
	{
		if (empty($this->_schema))
		{
			// Retrieve explicit search_path
			$result = $this->_execute('SELECT current_schemas(FALSE)');

			if (pg_result_status($result) !== PGSQL_TUPLES_OK)
				throw new Database_PostgreSQL_Exception($result);

			// Default schema is first in the array
			list($this->_schema) = explode(
				',',
				trim(pg_fetch_result($result, 0), '{}'),
				2
			);

			pg_free_result($result);
		}

		return $this->_schema;
	}

	public function schema_tables($schema = NULL)
	{
		if ( ! $schema)
		{
			// Use default schema
			$schema = $this->schema();
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
			'SELECT table_name, table_type'
			.' FROM information_schema.tables WHERE table_schema = '
			.$this->quote_literal($schema);

		if ( ! $this->_table_prefix)
		{
			// No table prefix
			return $this->execute_query($sql)->as_array('table_name');
		}

		// Filter on table prefix
		$sql .= " AND table_name LIKE '"
			.strtr($this->_table_prefix, array('_' => '\_', '%' => '\%'))
			."%'";

		$prefix = strlen($this->_table_prefix);
		$result = array();

		foreach ($this->execute_query($sql) as $table)
		{
			// Strip table prefix from table name
			$table['table_name'] = substr($table['table_name'], $prefix);
			$result[$table['table_name']] = $table;
		}

		return $result;
	}

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
			$schema = $this->schema();
		}

		// Only add table prefix to SQL_Table (exclude from SQL_Identifier)
		$table = ($table instanceof SQL_Table)
			? ($this->_table_prefix.$table->name)
			: $table->name;

		$sql =
			'SELECT column_name, ordinal_position, column_default, is_nullable,'
			.'   data_type, character_maximum_length,'
			.'   numeric_precision, numeric_scale, datetime_precision'
			.' FROM information_schema.columns'
			.' WHERE table_schema = '.$this->quote_literal($schema)
			.'   AND table_name = '.$this->quote_literal($table);

		return $this->execute_query($sql)->as_array('column_name');
	}

	/**
	 * Return the version of the connected server
	 *
	 * @return  string
	 */
	public function version()
	{
		return $this->_version;
	}
}

// Static initialization

if (version_compare(PHP_VERSION, '5.3.0', '<'))
{
	// PHP_VERSION_ID only available in PHP >= 5.2.7
	list($major, $minor, $release) = explode('.', PHP_VERSION);
	$php_version_id = $major * 10000 + $minor * 100 + $release;

	Database_PostgreSQL::$bug_copy_quote_table  = $php_version_id < 50214;
	Database_PostgreSQL::$bug_copy_to_null      = $php_version_id < 50214;
	Database_PostgreSQL::$bug_copy_to_schema    = $php_version_id < 50212;

	unset($major, $minor, $release, $php_version_id);
}
else
{
	Database_PostgreSQL::$bug_copy_quote_table  = PHP_VERSION_ID < 50303;
	Database_PostgreSQL::$bug_copy_to_null      = PHP_VERSION_ID < 50303;
	Database_PostgreSQL::$bug_copy_to_schema    = PHP_VERSION_ID < 50302;
}
