<?php

/**
 * Interface for a database connection and factory for SQL expressions and identifiers.
 *
 * Though it is possible (and correct) to call factory methods statically, it is better to call the
 * methods dynamically when building statements for a particular connection. This allows the driver
 * to return a statement builder which generates SQL in its particular dialect/syntax.
 *
 *     // SELECT statement using a syntax that works on most systems
 *     $query = Database::select();
 *
 *     // SELECT statement using MySQL syntax
 *     $query = Database_MySQL::select();
 *
 *     // SELECT statement using whichever syntax this connection needs
 *     $query = Database::instance()->select();
 *
 * @package     RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database extends SQL
{
	/**
	 * @var array   Singleton instances
	 */
	protected static $_instances;

	/**
	 * Create a binary literal value.
	 *
	 * @param   mixed   $value
	 * @return  Database_Binary
	 */
	public static function binary($value)
	{
		return new Database_Binary($value);
	}

	/**
	 * Create a timestamp literal value.
	 *
	 * @param   integer|string      $time       Unix timestamp or time in a format accepted by strtotime()
	 * @param   string|DateTimeZone $timezone   Fallback timezone, converted to DateTimeZone
	 * @param   string              $format     Format accepted by date()
	 * @return  Database_DateTime
	 */
	public static function datetime($time = 'now', $timezone = NULL, $format = Database_DateTime::SQL)
	{
		return new Database_DateTime($time, $timezone, $format);
	}

	/**
	 * Create a DELETE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  Database_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_Delete($table, $alias);
	}

	/**
	 * Create a database connection. The configuration group will be loaded
	 * from the database configuration file based on the connection name unless
	 * it is passed directly.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 * @return  Database
	 */
	public static function factory($name = 'default', $config = NULL)
	{
		if ($config === NULL)
		{
			// Load the configuration
			$config = Kohana::config('database')->$name;
		}

		if (empty($config['type']))
			throw new Kohana_Exception('Database type not defined in ":name" configuration', array(':name' => $name));

		// Set the driver class name
		$driver = 'Database_'.$config['type'];

		// Create the database connection
		return new $driver($name, $config);
	}

	/**
	 * Create an INSERT statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns, each converted to SQL_Column
	 * @return  Database_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_Insert($table, $columns);
	}

	/**
	 * Get a singleton Database instance. The configuration group will be
	 * loaded from the database configuration file based on the instance name
	 * unless it is passed directly.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name   Instance name
	 * @param   array   $config Configuration
	 * @return  Database
	 */
	public static function instance($name = 'default', $config = NULL)
	{
		if ( ! isset(Database::$_instances[$name]))
		{
			Database::$_instances[$name] = Database::factory($name, $config);
		}

		return Database::$_instances[$name];
	}

	/**
	 * Create a query.
	 *
	 * @param   string  $statement  SQL query
	 * @param   array   $parameters Unquoted parameters
	 * @return  Database_Query
	 */
	public static function query($statement, $parameters = array())
	{
		return new Database_Query($statement, $parameters);
	}

	/**
	 * Create a query set.
	 *
	 * @param   SQL_Expression  $query
	 * @return  Database_Query_Set
	 */
	public static function query_set($query = NULL)
	{
		return new Database_Query_Set($query);
	}

	/**
	 * Create a SELECT statement.
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs
	 * @return  Database_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_Select($columns);
	}

	/**
	 * Create an UPDATE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @param   array                                       $values Hash of (column => value) assignments
	 * @return  Database_Update
	 */
	public static function update($table = NULL, $alias = NULL, $values = NULL)
	{
		return new Database_Update($table, $alias, $values);
	}

	/**
	 * @var array   Configuration
	 */
	protected $_config;

	/**
	 * @var string  Connection name
	 */
	protected $_name;

	/**
	 * Create a database connection. The database type is not verified.
	 *
	 *  Configuration Option | Type          | Description
	 *  -------------------- | ----          | -----------
	 *  quote_character      | array\|string | Character used to quote identifiers or an array of the left and right characters
	 *  table_prefix         | string        | Table prefix
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct(
			empty($config['table_prefix']) ? '' : $config['table_prefix'],
			isset($config['quote_character']) ? $config['quote_character'] : NULL
		);

		$this->_config = $config;
		$this->_name = $name;
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function __toString()
	{
		return $this->_name;
	}

	/**
	 * Recursively replace array, Expression and Identifier parameters until all
	 * parameters are positional literals.
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

		$position = 0;
		$prev = $chunks[0];
		$result = $prev[0];

		for ($i = 1, $max = count($chunks); $i < $max; ++$i)
		{
			if ($statement[$chunks[$i][1] - 1] === '?')
			{
				// Character before the current chunk is a question mark
				$placeholder = $position++;
			}
			else
			{
				// End of the previous chunk
				$offset = $prev[1] + strlen($prev[0]);

				// Text between the current chunk and the previous one
				$placeholder = substr(
					$statement,
					$offset,
					$chunks[$i][1] - $offset
				);
			}

			$prev = $chunks[$i];
			$result .= $this->_parse_value(
				$parameters,
				$placeholder,
				$result_parameters
			).$prev[0];
		}

		return $result;
	}

	/**
	 * Recursively expand a parameter value to an SQL fragment consisting only
	 * of positional placeholders.
	 *
	 * @param   array           $array              Unquoted parameters
	 * @param   integer|string  $key                Index of the parameter value to parse
	 * @param   array           $result_parameters  Parameters for the resulting fragment
	 * @return  string  SQL fragment
	 */
	protected function _parse_value($array, $key, & $result_parameters)
	{
		$value = $array[$key];

		if (is_array($value))
		{
			if (empty($value))
				return '';

			$result = array();

			foreach ($value as $k => $v)
			{
				$result[] = $this->_parse_value($value, $k, $result_parameters);
			}

			return implode(', ', $result);
		}

		if ($value instanceof SQL_Expression)
			return $this->_parse(
				(string) $value,
				$value->parameters,
				$result_parameters
			);

		if ($value instanceof SQL_Identifier)
			return $this->quote($value);

		// Capture possible reference
		$result_parameters[] =& $array[$key];

		return '?';
	}

	/**
	 * Start a transaction or set a savepoint in the current transaction.
	 *
	 * @throws  Database_Exception
	 * @param   string  $name   Savepoint name or NULL to have one generated
	 * @return  string  Savepoint name
	 */
	abstract public function begin($name = NULL);

	/**
	 * Set the connection character set. May disconnect the session for some drivers.
	 *
	 * @throws  Database_Exception
	 * @param   string  $charset    Character set
	 * @return  void
	 */
	abstract public function charset($charset);

	/**
	 * Commit the current transaction or release a savepoint.
	 *
	 * @throws  Database_Exception
	 * @param   string  $name   Savepoint name or NULL to commit the transaction
	 * @return  void
	 */
	abstract public function commit($name = NULL);

	/**
	 * Connect
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	abstract public function connect();

	/**
	 * Disconnect
	 *
	 * @return  void
	 */
	abstract public function disconnect();

	/**
	 * Execute an SQL statement by dispatching to other execute_* methods.
	 *
	 * Returns a result set when the statement is [Database_iQuery] or is
	 * [Database_iReturning] and has returning set. Returns an array when the
	 * statement is [Database_iInsert] and has an identity set. Returns the
	 * number of affected rows otherwise.
	 *
	 * @uses Database::execute_command()
	 * @uses Database::execute_insert()
	 * @uses Database::execute_query()
	 *
	 * @throws  Database_Exception
	 *
	 * @param   string|Database_Statement|SQL_Expression    $statement  SQL statement
	 *
	 * @return  integer         Number of affected rows
	 * @return  array           List including number of affected rows and an identity value
	 * @return  Database_Result Result set
	 */
	public function execute($statement)
	{
		if (is_object($statement))
		{
			if ($statement instanceof Database_iQuery)
				return $this->execute_query($statement, $statement->as_object);

			if ($statement instanceof Database_iInsert AND $statement->identity)
				return $this->execute_insert($statement, $statement->identity);

			if ($statement instanceof Database_iReturning AND $statement->returning)
				return $this->execute_query($statement, $statement->as_object);
		}

		return $this->execute_command($statement);
	}

	/**
	 * Execute an SQL statement, returning the number of rows affected.
	 *
	 * Do not use this method to count the rows returned by a query (e.g., a
	 * SELECT statement). Always use execute_query() for statements that return
	 * results.
	 *
	 * @throws  Database_Exception
	 * @param   string|Database_Statement|SQL_Expression    $statement  SQL command
	 * @return  integer Number of affected rows
	 */
	abstract public function execute_command($statement);

	/**
	 * Execute an SQL statement, returning the value of an IDENTITY column.
	 *
	 * Behavior varies between database implementations. Reliable only when
	 * inserting one row.
	 *
	 * @throws  Database_Exception
	 * @param   string|Database_Statement|SQL_Expression    $statement  SQL insert
	 * @param   array|string|SQL_Expression|SQL_Identifier  $identity   Converted to SQL_Column
	 * @return  array   List including number of affected rows and an identity value
	 */
	abstract public function execute_insert($statement, $identity);

	/**
	 * Execute an SQL statement, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement).
	 *
	 * @throws  Database_Exception
	 * @param   string|Database_Statement|SQL_Expression    $statement  SQL query
	 * @param   string|boolean                              $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array                                       $arguments  Arguments to pass to the class constructor
	 * @return  Database_Result Result set or NULL
	 */
	abstract public function execute_query($statement, $as_object = FALSE, $arguments = array());

	/**
	 * Convert a generic [SQL_Expression] into a natively parameterized
	 * [Database_Statement]. Parameter names are driver-specific, but the
	 * default implementation replaces all [SQL_Expression] and [SQL_Identifier]
	 * parameters so that the remaining parameters are a 0-indexed array of
	 * literals.
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
	 * Abort the current transaction or revert to a savepoint.
	 *
	 * @throws  Database_Exception
	 * @param   string  $name   Savepoint name or NULL to abort the transaction
	 * @return  void
	 */
	abstract public function rollback($name = NULL);

	/**
	 * Set a savepoint in the current transaction.
	 *
	 * @throws  Database_Exception
	 * @param   string  $name   Savepoint name or NULL to have one generated
	 * @return  string  Savepoint name
	 */
	abstract public function savepoint($name = NULL);
}
