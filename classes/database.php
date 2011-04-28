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
abstract class Database
{
	/**
	 * @var array   Singleton instances
	 */
	protected static $_instances;

	/**
	 * Create an ALTER TABLE statement.
	 *
	 * @param   mixed   $name   Converted to SQL_Table
	 * @return  SQL_DDL_Alter_Table
	 */
	public static function alter_table($name = NULL)
	{
		return new SQL_DDL_Alter_Table($name);
	}

	/**
	 * Create a binary value
	 *
	 * @param   mixed   $value
	 * @return  Database_Binary
	 */
	public static function binary($value)
	{
		return new Database_Binary($value);
	}

	/**
	 * Create a column identifier
	 *
	 * @param   array|string    $value
	 * @return  SQL_Column
	 */
	public static function column($value)
	{
		return new SQL_Column($value);
	}

	/**
	 * Create a conditions accumulator
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  SQL_Conditions
	 */
	public static function conditions($left = NULL, $operator = NULL, $right = NULL)
	{
		return new SQL_Conditions($left, $operator, $right);
	}

	/**
	 * Create a CREATE INDEX statement.
	 *
	 * @param   mixed   $name       Converted to SQL_Identifier
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $columns    Each element converted to SQL_Column
	 * @return  SQL_DDL_Create_Index
	 */
	public static function create_index($name = NULL, $table = NULL, $columns = array())
	{
		return new SQL_DDL_Create_Index($name, $table, $columns);
	}

	/**
	 * Create a CREATE TABLE statement.
	 *
	 * @param   mixed   $name   Converted to SQL_Table
	 * @return  SQL_DDL_Create_Table
	 */
	public static function create_table($name = NULL)
	{
		return new SQL_DDL_Create_Table($name);
	}

	/**
	 * Create a CREATE VIEW statement.
	 *
	 * @param   mixed           $name   Converted to SQL_Table
	 * @param   SQL_Expression  $query
	 * @return  SQL_DDL_Create_View
	 */
	public static function create_view($name = NULL, $query = NULL)
	{
		return new SQL_DDL_Create_View($name, $query);
	}

	/**
	 * Create a timestamp value
	 *
	 * @param   integer|string  $time       Unix timestamp or time in a format accepted by strtotime()
	 * @param   mixed           $timezone   Fallback timezone, converted to DateTimeZone
	 * @param   string          $format     Format accepted by date(), defaults to Database_DateTime::SQL
	 * @return  Database_DateTime
	 */
	public static function datetime($time = 'now', $timezone = NULL, $format = Database_DateTime::SQL)
	{
		return new Database_DateTime($time, $timezone, $format);
	}

	/**
	 * Create a column expression
	 *
	 * @param   mixed   $name   Converted to SQL_Column
	 * @param   mixed   $type   Converted to SQL_Expression
	 * @return  SQL_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new SQL_DDL_Column($name, $type);
	}

	/**
	 * Create a constraint expression
	 *
	 * @param   string  $type   CHECK, FOREIGN, PRIMARY or UNIQUE
	 * @param   mixed   $name   Converted to SQL_Identifier
	 * @return  SQL_DDL_Constraint
	 */
	public static function ddl_constraint($type, $name = NULL)
	{
		$result = "SQL_DDL_Constraint_$type";
		$result = new $result;

		if ($name !== NULL)
		{
			$result->name($name);
		}

		return $result;
	}

	/**
	 * Create a DELETE command
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @return  Database_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_Delete($table, $alias);
	}

	/**
	 * Create a DROP statement.
	 *
	 * @param   string  $type   INDEX, SCHEMA, VIEW, etc.
	 * @param   mixed   $name   Converted to SQL_Identifier
	 * @return  SQL_DDL_Drop
	 */
	public static function drop($type, $name = NULL)
	{
		return new SQL_DDL_Drop($type, $name);
	}

	/**
	 * Create a DROP TABLE statement.
	 *
	 * @param   mixed   $name   Converted to SQL_Table
	 * @return  SQL_DDL_Drop_Table
	 */
	public static function drop_table($name = NULL)
	{
		return new SQL_DDL_Drop_Table($name);
	}

	/**
	 * Create an expression
	 *
	 * @param   mixed   $value      SQL expression
	 * @param   array   $parameters Unquoted parameters
	 * @return  SQL_Expression
	 */
	public static function expression($value, $parameters = array())
	{
		return new SQL_Expression($value, $parameters);
	}

	/**
	 * Create a database connection. The configuration group will be loaded
	 * from the database configuration file based on the connection name unless
	 * it is passed directly.
	 *
	 * @throws  Kohana_Exception
	 * @param   string          $name   Connection name
	 * @param   array           $config Configuration
	 * @param   string|array    $quote  Character used to quote identifiers or an array of the left and right characters
	 * @return  Database
	 */
	public static function factory($name = 'default', $config = NULL, $quote = NULL)
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
		return new $driver($name, $config, $quote);
	}

	/**
	 * Create an identifier
	 *
	 * @param   array|string    $value
	 * @return  SQL_Identifier
	 */
	public static function identifier($value)
	{
		return new SQL_Identifier($value);
	}

	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $columns    Each element converted to SQL_Column
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
	 * @param   string          $name   Instance name
	 * @param   array           $config Configuration
	 * @param   string|array    $quote  Character used to quote identifiers or an array of the left and right characters
	 * @return  Database
	 */
	public static function instance($name = 'default', $config = NULL, $quote = NULL)
	{
		if ( ! isset(Database::$_instances[$name]))
		{
			Database::$_instances[$name] = Database::factory($name, $config, $quote);
		}

		return Database::$_instances[$name];
	}

	/**
	 * Create a query
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
	 * Create a query set
	 *
	 * @param   SQL_Expression  $query
	 * @return  Database_Query_Set
	 */
	public static function query_set($query = NULL)
	{
		return new Database_Query_Set($query);
	}

	/**
	 * Create a table reference accumulator.
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @return  SQL_Table_Reference
	 */
	public static function reference($table = NULL, $alias = NULL)
	{
		return new SQL_Table_Reference($table, $alias);
	}

	/**
	 * Create a SELECT query
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 * @return  Database_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_Select($columns);
	}

	/**
	 * Create a table identifier
	 *
	 * @param   array|string    $value
	 * @return  SQL_Table
	 */
	public static function table($value)
	{
		return new SQL_Table($value);
	}

	/**
	 * Create an UPDATE command
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @param   array   $values Hash of (column => value) assignments
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
	 * @var string  Regular expression which matches expression placeholders
	 */
	protected $_placeholder = '/(?:\?|:\w++)/';

	/**
	 * @var string  Left character used to quote identifiers (tables, columns, aliases, etc.)
	 */
	protected $_quote_left = '"';

	/**
	 * @var string  Right character used to quote identifiers (tables, columns, aliases, etc.)
	 */
	protected $_quote_right = '"';

	/**
	 * Create a database connection. The database type is not verified.
	 *
	 * @param   string          $name   Connection name
	 * @param   array           $config Configuration
	 * @param   string|array    $quote  Character used to quote identifiers or an array of the left and right characters
	 */
	public function __construct($name, $config, $quote = NULL)
	{
		$this->_config = $config;
		$this->_name = $name;

		if ($quote !== NULL)
		{
			if (is_array($quote))
			{
				$this->_quote_left = reset($quote);
				$this->_quote_right = next($quote);
			}
			else
			{
				$this->_quote_left = $this->_quote_right = $quote;
			}
		}
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
				$parameters[$placeholder],
				$result_parameters
			).$prev[0];
		}

		return $result;
	}

	/**
	 * Recursively expand a parameter value to a SQL fragment consisting only of
	 * positional placeholders.
	 *
	 * @param   mixed   $value              Unquoted parameter
	 * @param   array   $result_parameters  Parameters for the resulting fragment
	 * @return  string  SQL fragment
	 */
	protected function _parse_value($value, & $result_parameters)
	{
		if (is_array($value))
		{
			if (empty($value))
				return '';

			$result = array();

			foreach ($value as $v)
			{
				$result[] = $this->_parse_value($v, $result_parameters);
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

		$result_parameters[] = $value;

		return '?';
	}

	/**
	 * Start a transaction
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	abstract public function begin();

	/**
	 * Set the connection character set. May disconnect the session for some drivers.
	 *
	 * @throws  Database_Exception
	 * @param   string  $charset    Character set
	 * @return  void
	 */
	abstract public function charset($charset);

	/**
	 * Commit the current transaction
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	abstract public function commit();

	/**
	 * Connect
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	abstract public function connect();

	/**
	 * Return information about a SQL data type
	 *
	 * @param   string  $type       SQL data type
	 * @param   string  $attribute  Attribute to return
	 * @return  array|mixed Array of attributes or an attribute value
	 */
	public function datatype($type, $attribute = NULL)
	{
		static $types = array
		(
			// SQL-92
			'bit'                           => array('type' => 'string', 'exact' => TRUE),
			'bit varying'                   => array('type' => 'string'),
			'char'                          => array('type' => 'string', 'exact' => TRUE),
			'char varying'                  => array('type' => 'string'),
			'character'                     => array('type' => 'string', 'exact' => TRUE),
			'character varying'             => array('type' => 'string'),
			'date'                          => array('type' => 'string'),
			'dec'                           => array('type' => 'float', 'exact' => TRUE),
			'decimal'                       => array('type' => 'float', 'exact' => TRUE),
			'double precision'              => array('type' => 'float'),
			'float'                         => array('type' => 'float'),
			'int'                           => array('type' => 'integer', 'min' => '-2147483648', 'max' => '2147483647'),
			'integer'                       => array('type' => 'integer', 'min' => '-2147483648', 'max' => '2147483647'),
			'interval'                      => array('type' => 'string'),
			'national char'                 => array('type' => 'string', 'exact' => TRUE),
			'national char varying'         => array('type' => 'string'),
			'national character'            => array('type' => 'string', 'exact' => TRUE),
			'national character varying'    => array('type' => 'string'),
			'nchar'                         => array('type' => 'string', 'exact' => TRUE),
			'nchar varying'                 => array('type' => 'string'),
			'numeric'                       => array('type' => 'float', 'exact' => TRUE),
			'real'                          => array('type' => 'float'),
			'smallint'                      => array('type' => 'integer', 'min' => '-32768', 'max' => '32767'),
			'time'                          => array('type' => 'string'),
			'time with time zone'           => array('type' => 'string'),
			'timestamp'                     => array('type' => 'datetime'),
			'timestamp with time zone'      => array('type' => 'datetime'),
			'varchar'                       => array('type' => 'string'),

			// SQL:1999
			'binary large object'               => array('type' => 'binary'),
			'blob'                              => array('type' => 'binary'),
			'boolean'                           => array('type' => 'boolean'),
			'char large object'                 => array('type' => 'string'),
			'character large object'            => array('type' => 'string'),
			'clob'                              => array('type' => 'string'),
			'national character large object'   => array('type' => 'string'),
			'nchar large object'                => array('type' => 'string'),
			'nclob'                             => array('type' => 'string'),
			'time without time zone'            => array('type' => 'string'),
			'timestamp without time zone'       => array('type' => 'datetime'),

			// SQL:2003
			'bigint'    => array('type' => 'integer', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),

			// SQL:2008
			'binary'            => array('type' => 'binary', 'exact' => TRUE),
			'binary varying'    => array('type' => 'binary'),
			'varbinary'         => array('type' => 'binary'),
		);

		if ($attribute !== NULL)
			return @$types[$type][$attribute];

		if (isset($types[$type]))
			return $types[$type];

		return array();
	}

	/**
	 * Disconnect
	 *
	 * @return  void
	 */
	abstract public function disconnect();

	/**
	 * Execute a SQL statement by dispatching to other execute_* methods.
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
	 * @param   string|SQL_Expression   $statement  SQL statement
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
	 * Execute a SQL statement, returning the number of rows affected
	 *
	 * Do not use this method to count the rows returned by a query (e.g., a
	 * SELECT statement). Always use execute_query() for statements that return
	 * results.
	 *
	 * @throws  Database_Exception
	 * @param   string|SQL_Expression   $statement  SQL command
	 * @return  integer Number of affected rows
	 */
	abstract public function execute_command($statement);

	/**
	 * Execute a SQL statement, returning the value of an IDENTITY column.
	 *
	 * Behavior varies between database implementations. Reliable only when
	 * inserting one row.
	 *
	 * @throws  Database_Exception
	 * @param   string|SQL_Expression   $statement  SQL insert
	 * @param   mixed                   $identity   Converted to SQL_Column
	 * @return  array   List including number of affected rows and an identity value
	 */
	abstract public function execute_insert($statement, $identity);

	/**
	 * Execute a SQL statement, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement)
	 *
	 * @throws  Database_Exception
	 * @param   string|SQL_Expression   $statement  SQL query
	 * @param   string|boolean          $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array                   $arguments  Arguments to pass to the class constructor
	 * @return  Database_Result Result set or NULL
	 */
	abstract public function execute_query($statement, $as_object = FALSE, $arguments = array());

	/**
	 * Quote a value for inclusion in a SQL statement. Dispatches to other
	 * quote_* methods.
	 *
	 * @uses Database::quote_column()
	 * @uses Database::quote_expression()
	 * @uses Database::quote_identifier()
	 * @uses Database::quote_literal()
	 * @uses Database::quote_table()
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string  SQL fragment
	 */
	public function quote($value)
	{
		if (is_array($value))
		{
			return $value
				? implode(', ', array_map(array($this, __FUNCTION__), $value))
				: '';
		}

		if (is_object($value))
		{
			if ($value instanceof SQL_Expression)
				return $this->quote_expression($value);

			if ($value instanceof SQL_Column)
				return $this->quote_column($value);

			if ($value instanceof SQL_Table)
				return $this->quote_table($value);

			if ($value instanceof SQL_Identifier)
				return $this->quote_identifier($value);
		}

		return $this->quote_literal($value);
	}

	/**
	 * Quote a column identifier for inclusion in a SQL query. Adds the table
	 * prefix unless the namespace is an instance of SQL_Identifier.
	 *
	 * @uses Database::quote_identifier()
	 * @uses Database::quote_table()
	 *
	 * @param   mixed   $value  Column to quote
	 * @return  string
	 */
	public function quote_column($value)
	{
		if ($value instanceof SQL_Identifier)
		{
			$namespace = $value->namespace;
			$value = $value->name;
		}
		elseif (is_array($value))
		{
			$namespace = $value;
			$value = array_pop($namespace);
		}
		else
		{
			$namespace = explode('.', $value);
			$value = array_pop($namespace);
		}

		if (empty($namespace))
		{
			$prefix = '';
		}
		elseif ($namespace instanceof SQL_Table OR ! $namespace instanceof SQL_Identifier)
		{
			$prefix = $this->quote_table($namespace).'.';
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		if ($value === '*')
		{
			$value = $prefix.$value;
		}
		else
		{
			$value = $prefix.$this->_quote_left.$value.$this->_quote_right;
		}

		return $value;
	}

	/**
	 * Quote an expression's parameters for inclusion in a SQL query
	 *
	 * @param   SQL_Expression  $value  Expression to quote
	 * @return  string
	 */
	public function quote_expression($value)
	{
		$parameters = $value->parameters;
		$value = (string) $value;

		if (empty($parameters))
			return $value;

		// Trying to maintain context between calls (and recurse) using
		// preg_replace_callback is too complicated. Capturing the placeholder
		// offsets allows us to iterate over a single expression and recurse
		// using the call stack.
		$chunks = preg_split(
			$this->_placeholder,
			$value,
			NULL,
			PREG_SPLIT_OFFSET_CAPTURE
		);

		$position = 0;
		$prev = $chunks[0];
		$result = $prev[0];

		for ($i = 1, $max = count($chunks); $i < $max; ++$i)
		{
			if ($value[$chunks[$i][1] - 1] === '?')
			{
				// Character before the current chunk is a question mark
				$placeholder = $position++;
			}
			else
			{
				// End of the previous chunk
				$offset = $prev[1] + strlen($prev[0]);

				// Text between the current chunk and the previous one
				$placeholder = substr($value, $offset, $chunks[$i][1] - $offset);
			}

			$prev = $chunks[$i];
			$result .= $this->quote($parameters[$placeholder]).$prev[0];
		}

		return $result;
	}

	/**
	 * Quote an identifier for inclusion in a SQL query
	 *
	 * @param   mixed   $value  Identifier to quote
	 * @return  string
	 */
	public function quote_identifier($value)
	{
		if ($value instanceof SQL_Identifier)
		{
			$namespace = $value->namespace;
			$value = $value->name;
		}
		elseif (is_array($value))
		{
			$namespace = $value;
			$value = array_pop($namespace);
		}
		else
		{
			$namespace = explode('.', $value);
			$value = array_pop($namespace);
		}

		if (empty($namespace))
		{
			$prefix = '';
		}
		elseif (is_array($namespace))
		{
			$prefix = '';

			foreach ($namespace as $part)
			{
				// Quote each of the parts
				$prefix .= $this->_quote_left.$part.$this->_quote_right.'.';
			}
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		$value = $prefix.$this->_quote_left.$value.$this->_quote_right;

		return $value;
	}

	/**
	 * Quote a literal value for inclusion in a SQL query
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function quote_literal($value)
	{
		if ($value === NULL)
		{
			$value = 'NULL';
		}
		elseif ($value === TRUE)
		{
			$value = "'1'";
		}
		elseif ($value === FALSE)
		{
			$value = "'0'";
		}
		elseif (is_int($value))
		{
			$value = (string) $value;
		}
		elseif (is_float($value))
		{
			$value = sprintf('%F', $value);
		}
		elseif (is_array($value))
		{
			$value = '('.implode(', ', array_map(array($this, __FUNCTION__), $value)).')';
		}
		else
		{
			$value = "'$value'";
		}

		return $value;
	}

	/**
	 * Quote a table identifier for inclusion in a SQL query. Adds the table prefix.
	 *
	 * @uses Database::quote_identifier()
	 * @uses Database::table_prefix()
	 *
	 * @param   mixed   $value  Table to quote
	 * @return  string
	 */
	public function quote_table($value)
	{
		if ($value instanceof SQL_Identifier)
		{
			$namespace = $value->namespace;
			$value = $value->name;
		}
		elseif (is_array($value))
		{
			$namespace = $value;
			$value = array_pop($namespace);
		}
		else
		{
			$namespace = explode('.', $value);
			$value = array_pop($namespace);
		}

		if (empty($namespace))
		{
			$prefix = '';
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		$value = $prefix.$this->_quote_left.$this->table_prefix().$value.$this->_quote_right;

		return $value;
	}

	/**
	 * Abort the current transaction
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	abstract public function rollback();

	/**
	 * Return the table prefix
	 *
	 * @return  string
	 */
	abstract public function table_prefix();
}
