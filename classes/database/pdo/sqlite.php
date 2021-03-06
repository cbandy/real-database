<?php

/**
 * [SQLite](http://www.sqlite.org/) connection and expression factory using PDO.
 *
 *  Configuration Option  | Type    | Description
 *  --------------------  | ----    | -----------
 *  table_prefix          | string  | Table prefix
 *  connection.dsn        | string  | Full DSN or a predefined DSN name
 *  connection.options    | array   | PDO options
 *  connection.persistent | boolean | Use the PHP connection pool
 *  connection.pragmas    | array   | [PRAGMA][] settings as "key => value" pairs
 *  connection.uri        | string  | URI to a file containing the DSN
 *
 * *[DSN]: Data Source Name
 * *[PDO]: PHP Data Objects
 * *[URI]: Uniform Resource Identifier
 * [PRAGMA]: http://www.sqlite.org/pragma.html
 *
 * @link http://www.php.net/manual/ref.pdo-sqlite
 * @link http://www.php.net/manual/ref.pdo-sqlite.connection PDO SQLite DSN
 *
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_SQLite extends Database_PDO
	implements Database_iEscape, Database_iIntrospect
{
	/**
	 * Create a CREATE TABLE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @return  Database_SQLite_DDL_Create_Table
	 */
	public static function create_table($name = NULL)
	{
		return new Database_SQLite_DDL_Create_Table($name);
	}

	/**
	 * Create a column expression.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @param   mixed                                       $type   Converted to SQL_Expression
	 * @return  Database_SQLite_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_SQLite_DDL_Column($name, $type);
	}

	/**
	 * Create an expression for comparing whether or not two values are
	 * distinct.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Equality operator
	 * @param   mixed   $right      Right operand
	 * @return  Database_SQLite_Identical
	 */
	public static function identical($left, $operator, $right)
	{
		return new Database_SQLite_Identical($left, $operator, $right);
	}

	/**
	 * Create an INSERT statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns, each converted to SQL_Column
	 * @return  Database_SQLite_DML_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_SQLite_DML_Insert($table, $columns);
	}

	/**
	 * Create a query set.
	 *
	 * @param   SQL_Expression  $query
	 * @return  Database_SQLite_DML_Set
	 */
	public static function query_set($query = NULL)
	{
		return new Database_SQLite_DML_Set($query);
	}

	/**
	 * Create a SELECT statement.
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs
	 * @return  Database_SQLite_DML_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_SQLite_DML_Select($columns);
	}

	/**
	 * Create a PDO connection for SQLite
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct($name, $config);

		$this->_config['connection']['username'] = NULL;
		$this->_config['connection']['password'] = NULL;
	}

	public function connect()
	{
		parent::connect();

		if ( ! empty($this->_config['connection']['pragmas']))
		{
			$pragmas = '';

			foreach ($this->_config['connection']['pragmas'] as $key => $value)
			{
				$pragmas .= '; PRAGMA '.$key.' = '.$this->quote_literal($value);
			}

			$this->execute_command($pragmas);
		}

		// Initialize the savepoint stack
		$this->_savepoints = new Database_Savepoint_Deep;
	}

	/**
	 * Create or redefine an SQL aggregate function.
	 *
	 * @link http://php.net/manual/function.pdo-sqlitecreateaggregate
	 *
	 * @param   string      $name       Name of the SQL function to be created or redefined
	 * @param   callback    $step       Called for each row of a result set
	 * @param   callback    $final      Called after all rows of a result set have been processed
	 * @param   integer     $arguments  Number of arguments that the SQL function takes
	 *
	 * @return  boolean
	 */
	public function create_aggregate($name, $step, $final, $arguments = -1)
	{
		$this->_connection or $this->connect();

		return $this->_connection->sqliteCreateAggregate(
			$name, $step, $final, $arguments
		);
	}

	/**
	 * Create or redefine an SQL function.
	 *
	 * @link http://php.net/manual/function.pdo-sqlitecreatefunction
	 *
	 * @param   string      $name       Name of the SQL function to be created or redefined
	 * @param   callback    $callback   Callback which implements the SQL function
	 * @param   integer     $arguments  Number of arguments that the SQL function takes
	 *
	 * @return  boolean
	 */
	public function create_function($name, $callback, $arguments = -1)
	{
		$this->_connection or $this->connect();

		return $this->_connection->sqliteCreateFunction(
			$name, $callback, $arguments
		);
	}

	/**
	 * Return information about an SQLite data type.
	 *
	 * @link http://www.sqlite.org/datatype3.html
	 *
	 * @param   string  $type       SQL data type
	 * @param   string  $attribute  Attribute to return
	 * @return  array|mixed Array of attributes or an attribute value
	 */
	public function datatype($type, $attribute = NULL)
	{
		if (strpos($type, 'int') !== FALSE)
		{
			$result = array('type' => 'integer');
		}
		elseif (strpos($type, 'char') !== FALSE
			OR strpos($type, 'clob') !== FALSE
			OR strpos($type, 'text') !== FALSE)
		{
			$result = array('type' => 'string');
		}
		elseif (strpos($type, 'blob') !== FALSE)
		{
			$result = array('type' => 'binary');
		}
		elseif (strpos($type, 'real') !== FALSE
			OR strpos($type, 'floa') !== FALSE
			OR strpos($type, 'doub') !== FALSE)
		{
			$result = array('type' => 'float');
		}
		else
		{
			// Anything else is probably being used as intended by the standard
			return parent::datatype($type, $attribute);
		}

		if ($attribute !== NULL)
			return @$result[$attribute];

		return $result;
	}

	public function execute_command($statement)
	{
		$this->_connection or $this->connect();

		if ( ! is_string($statement))
		{
			// Bypass the parsing done by PDO to allow SQLite to execute
			// compound statements
			$statement = $this->quote($statement);
		}

		return parent::execute_command($statement);
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @uses Database_PDO::escape_literal()
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
	 * Retrieve the tables of a schema in a format almost identical to that of
	 * the Tables table of the SQL-92 Information Schema. Includes one
	 * non-standard field, `sql`, which contains the text of the original CREATE
	 * statement.
	 *
	 * @param   array|string|SQL_Identifier $schema Converted to SQL_Identifier. NULL for the default schema.
	 * @return  array
	 */
	public function schema_tables($schema = NULL)
	{
		if ( ! $schema)
		{
			// Use default schema
			$schema = 'main';
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
			"SELECT tbl_name AS table_name, CASE type WHEN 'table'"
			."   THEN 'BASE TABLE' ELSE 'VIEW' END AS table_type, sql"
			.' FROM '.$this->_quote_left.$schema.$this->_quote_right
			.".sqlite_master WHERE type IN ('table', 'view')";

		if ( ! $this->_table_prefix)
		{
			// No table prefix
			return $this->execute_query($sql)->as_array('table_name');
		}

		// Filter on table prefix
		$sql .= ' AND tbl_name LIKE '.$this->quote_literal(
			strtr($this->_table_prefix, array('_' => '\_', '%' => '\%')).'%'
		)." ESCAPE '\'";

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
			$schema = 'main';
		}

		// Only add table prefix to SQL_Table (exclude from SQL_Identifier)
		$table = ($table instanceof SQL_Table)
			? ($this->_table_prefix.$table->name)
			: $table->name;

		$sql = 'PRAGMA '.$this->_quote_left.$schema.$this->_quote_right
			.'.table_info('.$this->_quote_left.$table.$this->_quote_right.')';

		$result = array();

		if ($rows = $this->execute_query($sql))
		{
			foreach ($rows as $row)
			{
				$type = strtolower($row['type']);

				if ($open = strpos($type, '('))
				{
					$close = strpos($type, ')', $open);

					// Text between parentheses
					$length = substr($type, $open + 1, $close - 1 - $open);

					// Text before parentheses
					$type = substr($type, 0, $open);
				}
				else
				{
					$length = NULL;
				}

				$row = array(
					'column_name'       => $row['name'],
					'ordinal_position'  => $row['cid'] + 1,
					'column_default'    => $row['dflt_value'],
					'is_nullable'       => empty($row['notnull']) ? 'YES' : 'NO',
					'data_type'         => $type,
					'character_maximum_length'  => NULL,
					'numeric_precision' => NULL,
					'numeric_scale'     => NULL,
				);

				if ($length)
				{
					if (strpos($type, 'char') !== FALSE
						OR strpos($type, 'clob') !== FALSE
						OR strpos($type, 'text') !== FALSE)
					{
						$row['character_maximum_length'] = $length;
					}
					else
					{
						$length = explode(',', $length);
						$row['numeric_precision'] = reset($length);

						if (next($length))
						{
							$row['numeric_scale'] = current($length);
						}
					}
				}

				$result[$row['column_name']] = $row;
			}
		}

		return $result;
	}
}
