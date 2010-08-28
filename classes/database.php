<?php

/**
 * @package     RealDatabase
 * @category    Driver Interfaces
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
	 * Create an ALTER command
	 *
	 * @param   string  $type   INDEX, TABLE, VIEW, etc.
	 * @param   mixed   $name   Converted to Database_Identifier
	 * @return  Database_Command
	 */
	public static function alter($type, $name = NULL)
	{
		$class = "Database_Command_Alter_$type";

		return new $class($name);
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
	 * @return  Database_Column
	 */
	public static function column($value)
	{
		return new Database_Column($value);
	}

	/**
	 * Create a command
	 *
	 * @param   string  $statement  SQL command
	 * @param   array   $parameters Unquoted parameters
	 * @return  Database_Command
	 */
	public static function command($statement, $parameters = array())
	{
		return new Database_Command($statement, $parameters);
	}

	/**
	 * Create a conditions accumulator
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  Database_Conditions
	 */
	public static function conditions($left = NULL, $operator = NULL, $right = NULL)
	{
		return new Database_Conditions($left, $operator, $right);
	}

	/**
	 * Create a CREATE command
	 *
	 * @param   string  $type   INDEX, TABLE, VIEW, etc.
	 * @param   mixed   $name   Converted to Database_Identifier
	 * @return  Database_Command
	 */
	public static function create($type, $name = NULL)
	{
		$class = "Database_Command_Create_$type";

		return new $class($name);
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
	 * @param   mixed   $name   Converted to Database_Column
	 * @param   mixed   $type   Converted to Database_Expression
	 * @return  Database_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_DDL_Column($name, $type);
	}

	/**
	 * Create a constraint expression
	 *
	 * @param   string  $type   CHECK, FOREIGN, PRIMARY or UNIQUE
	 * @param   mixed   $name   Converted to Database_Identifier
	 * @return  Database_DDL_Constraint
	 */
	public static function ddl_constraint($type, $name = NULL)
	{
		$result = "Database_DDL_Constraint_$type";
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
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  Database_Command_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_Command_Delete($table, $alias);
	}

	/**
	 * Create a DROP command
	 *
	 * @param   string  $type   INDEX, TABLE, VIEW, etc.
	 * @param   mixed   $name   Converted to Database_Identifier
	 * @return  Database_Command
	 */
	public static function drop($type, $name = NULL)
	{
		if (strtoupper($type) === 'TABLE')
			return new Database_Command_Drop_Table($name);

		return new Database_Command_Drop($type, $name);
	}

	/**
	 * Create an expression
	 *
	 * @param   mixed   $value      SQL expression
	 * @param   array   $parameters Unquoted parameters
	 * @return  Database_Expression
	 */
	public static function expression($value, $parameters = array())
	{
		return new Database_Expression($value, $parameters);
	}

	/**
	 * Create a table reference accumulator
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  Database_From
	 */
	public static function from($table = NULL, $alias = NULL)
	{
		return new Database_From($table, $alias);
	}

	/**
	 * Create an identifier
	 *
	 * @param   array|string    $value
	 * @return  Database_Identifier
	 */
	public static function identifier($value)
	{
		return new Database_Identifier($value);
	}

	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  Database_Command_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_Command_Insert($table, $columns);
	}

	/**
	 * Get a singleton Database instance
	 *
	 * The configuration group will be loaded from the database configuration
	 * file based on the instance name unless it is passed directly.
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
			if ($config === NULL)
			{
				// Load the configuration
				$config = Kohana::config('database')->$name;
			}

			if ( ! isset($config['type']))
				throw new Kohana_Exception('Database type not defined in ":name" configuration', array(':name' => $name));

			// Set the driver class name
			$driver = 'Database_'.$config['type'];

			// Create the database connection instance
			new $driver($name, $config);
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
	 * @param   Database_Query  $query
	 * @return  Database_Query_Set
	 */
	public static function query_set($query = NULL)
	{
		return new Database_Query_Set($query);
	}

	/**
	 * Create a SELECT query
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 * @return  Database_Query_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_Query_Select($columns);
	}

	/**
	 * Create a table identifier
	 *
	 * @param   array|string    $value
	 * @return  Database_Table
	 */
	public static function table($value)
	{
		return new Database_Table($value);
	}

	/**
	 * Create an UPDATE command
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @param   array   $values Hash of (column => value) assignments
	 * @return  Database_Command_Update
	 */
	public static function update($table = NULL, $alias = NULL, $values = NULL)
	{
		return new Database_Command_Update($table, $alias, $values);
	}

	/**
	 * @var array   Configuration
	 */
	protected $_config;

	/**
	 * @var string  Instance name
	 */
	protected $_instance;

	/**
	 * @var string  Regular expression which matches expression placeholders
	 */
	protected $_placeholder = '/(?:\?|:\w++)/';

	/**
	 * @var string  Character used to quote identifiers (tables, columns, aliases, etc.)
	 */
	protected $_quote = '"';

	/**
	 * Create a singleton database instance
	 *
	 * The database type is not verified.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name   Instance name
	 * @param   array   $config Configuration
	 */
	protected function __construct($name, $config)
	{
		if (isset(Database::$_instances[$name]))
			throw new Kohana_Exception('Database instance ":name" already exists', array(':name' => $name));

		$this->_config = $config;
		$this->_instance = $name;

		// Store the database instance
		Database::$_instances[$name] = $this;
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function __toString()
	{
		return $this->_instance;
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
	 * Execute a SQL statement, returning the number of rows affected
	 *
	 * Do not use this method to count the rows returned by a query (e.g., a
	 * SELECT statement). Always use execute_query() for statements that return
	 * results.
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL command
	 * @return  integer Number of affected rows
	 */
	abstract public function execute_command($statement);

	/**
	 * Execute a SQL statement, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement)
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL query
	 * @param   mixed   $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 * @return  Database_Result Result set or NULL
	 */
	abstract public function execute_query($statement, $as_object = FALSE);

	/**
	 * Prepare a SQL statement which will only return the number of rows affected
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL command
	 * @param   array   $parameters Unquoted parameters
	 * @return  Database_Prepared_Command
	 */
	public function prepare_command($statement, $parameters = array())
	{
		return new Database_Prepared_Command($this, $statement, $parameters);
	}

	/**
	 * Prepare a SQL statement which will return a result set
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL query
	 * @param   array   $parameters Unquoted parameters
	 * @return  Database_Prepared_Query
	 */
	public function prepare_query($statement, $parameters = array())
	{
		return new Database_Prepared_Query($this, $statement, $parameters);
	}

	/**
	 * Quote a value for inclusion in a SQL query
	 *
	 * @uses Database::quote_column()
	 * @uses Database::quote_expression()
	 * @uses Database::quote_identifier()
	 * @uses Database::quote_literal()
	 * @uses Database::quote_table()
	 *
	 * @param   mixed   $value  Value to quote
	 * @param   string  $alias  Alias
	 * @return  string
	 */
	public function quote($value, $alias = NULL)
	{
		if (is_array($value))
		{
			$value = empty($value) ? '' : implode(', ', array_map(array($this, __FUNCTION__), $value));
		}
		elseif (is_object($value))
		{
			if ($value instanceof Database_Expression)
			{
				$value = $this->quote_expression($value);
			}
			elseif ($value instanceof Database_Column)
			{
				$value = $this->quote_column($value);
			}
			elseif ($value instanceof Database_Table)
			{
				$value = $this->quote_table($value);
			}
			elseif ($value instanceof Database_Identifier)
			{
				$value = $this->quote_identifier($value);
			}
			else
			{
				$value = $this->quote_literal($value);
			}
		}
		else
		{
			$value = $this->quote_literal($value);
		}

		if (isset($alias))
			return $value.' AS '.$this->_quote.$alias.$this->_quote;

		return $value;
	}

	/**
	 * Quote a column identifier for inclusion in a SQL query. Adds the table
	 * prefix unless the namespace is an instance of Database_Identifier.
	 *
	 * @uses Database::quote_identifier()
	 * @uses Database::quote_table()
	 *
	 * @param   mixed   $value  Column to quote
	 * @return  string
	 */
	public function quote_column($value)
	{
		if ($value instanceof Database_Identifier)
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
		elseif ($namespace instanceof Database_Table OR ! $namespace instanceof Database_Identifier)
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
			$value = $prefix.$this->_quote.$value.$this->_quote;
		}

		return $value;
	}

	/**
	 * Quote an expression's parameters for inclusion in a SQL query
	 *
	 * @param   Database_Expression $value  Expression to quote
	 * @return  string
	 */
	public function quote_expression($value)
	{
		$parameters = $value->parameters;
		$value = (string) $value;

		$chunks = preg_split($this->_placeholder, $value, NULL, PREG_SPLIT_OFFSET_CAPTURE);

		$max = count($chunks);
		$position = 0;
		$prev = $chunks[0];
		$result = $prev[0];

		for ($i = 1; $i < $max; ++$i)
		{
			if ($value[$chunks[$i][1] - 1] === '?')
			{
				$placeholder = $position++;
			}
			else
			{
				$offset = $prev[1] + strlen($prev[0]);
				$placeholder = substr($value, $offset, $chunks[$i][1] - $offset);
			}

			//if ( ! array_key_exists($placeholder, $parameters))
			//	throw new Database_Exception('Expression lacking parameter ":param"', array(':param' => $placeholder));

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
		if ($value instanceof Database_Identifier)
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
				$prefix .= $this->_quote.$part.$this->_quote.'.';
			}
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		$value = $prefix.$this->_quote.$value.$this->_quote;

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
		if ($value instanceof Database_Identifier)
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

		$value = $prefix.$this->_quote.$this->table_prefix().$value.$this->_quote;

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
