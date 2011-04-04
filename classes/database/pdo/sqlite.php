<?php

/**
 * SQLite connection using PDO and SQLite expression factory.
 *
 * *[PDO]: PHP Data Objects
 *
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/ref.pdo-sqlite
 * @link http://www.sqlite.org/
 */
class Database_PDO_SQLite extends Database_PDO implements Database_iEscape, Database_iIntrospect
{
	public static function create($type, $name = NULL)
	{
		if (strtoupper($type) === 'TABLE')
			return new Database_SQLite_Create_Table($name);

		return parent::create($type, $name);
	}

	/**
	 * Create a column expression
	 *
	 * @param   mixed   $name   Converted to SQL_Column
	 * @param   mixed   $type   Converted to SQL_Expression
	 * @return  Database_SQLite_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_SQLite_DDL_Column($name, $type);
	}

	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $columns    Each element converted to SQL_Column
	 * @return  Database_SQLite_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_SQLite_Insert($table, $columns);
	}

	/**
	 * Create a PDO connection for SQLite
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | string  | Character set
	 *  pragmas               | array   | [PRAGMA][] settings as "key => value" pairs
	 *  profiling             | boolean | Enable execution profiling
	 *  table_prefix          | string  | Table prefix
	 *  connection.dsn        | string  | Full DSN or a predefined DSN name
	 *  connection.options    | array   | PDO options
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.uri        | string  | URI to a file containing the DSN
	 *
	 * *[DSN]: Data Source Name
	 * *[URI]: Uniform Resource Identifier
	 * [PRAGMA]: http://www.sqlite.org/pragma.html
	 *
	 * @link http://php.net/manual/ref.pdo-sqlite.connection PDO SQLite DSN
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

	public function charset($charset)
	{
		$this->execute_command('PRAGMA encoding = "'.$charset.'"');
	}

	public function connect()
	{
		parent::connect();

		if ( ! empty($this->_config['pragmas']))
		{
			foreach ($this->_config['pragmas'] as $pragma => $value)
			{
				$this->execute_command('PRAGMA '.$pragma.' = '.$this->quote_literal($value));
			}
		}
	}

	/**
	 * Return information about a SQLite data type
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

		if ( ! $prefix = $this->table_prefix())
		{
			// No table prefix
			return $this->execute_query($sql)->as_array('table_name');
		}

		// Filter on table prefix
		$sql .= ' AND tbl_name LIKE '.$this->quote_literal(
			strtr($prefix, array('_' => '\_', '%' => '\%')).'%'
		)." ESCAPE '\'";

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
			? $this->table_prefix().$table->name
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
