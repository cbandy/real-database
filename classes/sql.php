<?php

/**
 * Interface for a SQL implementation and factory for SQL expressions and
 * identifiers.
 *
 * @package     RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class SQL
{
	/**
	 * Create an alias expression.
	 *
	 * @param   mixed                                       $value
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 */
	public static function alias($value, $alias)
	{
		return new SQL_Alias($value, $alias);
	}

	/**
	 * Create an ALTER TABLE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @return  SQL_DDL_Alter_Table
	 */
	public static function alter_table($name = NULL)
	{
		return new SQL_DDL_Alter_Table($name);
	}

	/**
	 * Create a column identifier.
	 *
	 * @param   array|string    $name
	 * @return  SQL_Column
	 */
	public static function column($name)
	{
		return new SQL_Column($name);
	}

	/**
	 * Create a conditions accumulator.
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
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name       Converted to SQL_Identifier
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns converted to SQL_Column
	 * @return  SQL_DDL_Create_Index
	 */
	public static function create_index($name = NULL, $table = NULL, $columns = NULL)
	{
		return new SQL_DDL_Create_Index($name, $table, $columns);
	}

	/**
	 * Create a CREATE TABLE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @return  SQL_DDL_Create_Table
	 */
	public static function create_table($name = NULL)
	{
		return new SQL_DDL_Create_Table($name);
	}

	/**
	 * Create a CREATE VIEW statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @param   SQL_Expression                              $query
	 * @return  SQL_DDL_Create_View
	 */
	public static function create_view($name = NULL, $query = NULL)
	{
		return new SQL_DDL_Create_View($name, $query);
	}

	/**
	 * Create a CHECK constraint.
	 *
	 * @param   SQL_Conditions  $conditions
	 * @return  SQL_DDL_Constraint_Check
	 */
	public static function ddl_check($conditions = NULL)
	{
		return new SQL_DDL_Constraint_Check($conditions);
	}

	/**
	 * Create a column expression.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @param   string|SQL_Expression                       $type   Converted to SQL_Expression
	 * @return  SQL_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new SQL_DDL_Column($name, $type);
	}

	/**
	 * Create a FOREIGN KEY constraint.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns converted to SQL_Column
	 * @return  SQL_DDL_Constraint_Foreign
	 */
	public static function ddl_foreign($table = NULL, $columns = NULL)
	{
		return new SQL_DDL_Constraint_Foreign($table, $columns);
	}

	/**
	 * Create a PRIMARY KEY constraint.
	 *
	 * @param   array   $columns    List of columns converted to SQL_Column
	 * @return  SQL_DDL_Constraint_Primary
	 */
	public static function ddl_primary($columns = NULL)
	{
		return new SQL_DDL_Constraint_Primary($columns);
	}

	/**
	 * Create a UNIQUE constraint.
	 *
	 * @param   array   $columns    List of columns converted to SQL_Column
	 * @return  SQL_DDL_Constraint_Unique
	 */
	public static function ddl_unique($columns = NULL)
	{
		return new SQL_DDL_Constraint_Unique($columns);
	}

	/**
	 * Create a DELETE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   string                                      $alias  Table alias
	 * @return  SQL_DML_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new SQL_DML_Delete($table, $alias);
	}

	/**
	 * Create a DROP statement.
	 *
	 * @param   string                                      $type   INDEX, SCHEMA, VIEW, etc.
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Identifier
	 * @return  SQL_DDL_Drop
	 */
	public static function drop($type, $name = NULL)
	{
		return new SQL_DDL_Drop($type, $name);
	}

	/**
	 * Create a DROP TABLE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @return  SQL_DDL_Drop_Table
	 */
	public static function drop_table($name = NULL)
	{
		return new SQL_DDL_Drop_Table($name);
	}

	/**
	 * Create an expression.
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
	 * Create an identifier.
	 *
	 * @param   array|string    $name
	 * @return  SQL_Identifier
	 */
	public static function identifier($name)
	{
		return new SQL_Identifier($name);
	}

	/**
	 * Create an INSERT statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns converted to SQL_Column
	 * @return  SQL_DML_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new SQL_DML_Insert($table, $columns);
	}

	/**
	 * Create a query set.
	 *
	 * @param   SQL_Expression  $query
	 * @return  SQL_DML_Set
	 */
	public static function query_set($query = NULL)
	{
		return new SQL_DML_Set($query);
	}

	/**
	 * Create a table reference accumulator.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   string                                      $alias  Table alias
	 * @return  SQL_Table_Reference
	 */
	public static function reference($table = NULL, $alias = NULL)
	{
		return new SQL_Table_Reference($table, $alias);
	}

	/**
	 * Create a SELECT statement.
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs
	 * @return  SQL_DML_Select
	 */
	public static function select($columns = NULL)
	{
		return new SQL_DML_Select($columns);
	}

	/**
	 * Create a table identifier.
	 *
	 * @param   array|string    $name
	 * @return  SQL_Table
	 */
	public static function table($name)
	{
		return new SQL_Table($name);
	}

	/**
	 * Create an UPDATE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   string                                      $alias  Table alias
	 * @param   array                                       $values Hash of (column => value) assignments
	 * @return  SQL_DML_Update
	 */
	public static function update($table = NULL, $alias = NULL, $values = NULL)
	{
		return new SQL_DML_Update($table, $alias, $values);
	}

	/**
	 * @var string  PCRE which matches expression placeholders
	 */
	protected $_placeholder = '/(?:\?|:\w++)/';

	/**
	 * @var string  Left character used to quote identifiers
	 */
	protected $_quote_left = '"';

	/**
	 * @var string  Right character used to quote identifiers
	 */
	protected $_quote_right = '"';

	/**
	 * @var string  Prefix added to tables when quoting
	 */
	protected $_table_prefix;

	/**
	 * @param   string          $table_prefix   Prefix added to tables when quoting
	 * @param   string|array    $quote          Character used to quote identifiers or an array of the left and right characters
	 */
	public function __construct($table_prefix = '', $quote = NULL)
	{
		$this->_table_prefix = $table_prefix;

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

	/**
	 * Return information about a SQL data type.
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
		{
			if ( ! isset($types[$type]) OR ! isset($types[$type][$attribute]))
				return NULL;

			return $types[$type][$attribute];
		}

		if (isset($types[$type]))
			return $types[$type];

		return array();
	}

	/**
	 * Quote a value for inclusion in a SQL statement. Dispatches to other
	 * quote_* methods.
	 *
	 * @uses SQL::quote_column()
	 * @uses SQL::quote_expression()
	 * @uses SQL::quote_identifier()
	 * @uses SQL::quote_literal()
	 * @uses SQL::quote_table()
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
	 * Quote a column identifier for inclusion in a SQL statement. Adds the
	 * table prefix unless the namespace is an instance of [SQL_Identifier].
	 *
	 * @uses SQL::quote_identifier()
	 * @uses SQL::quote_table()
	 *
	 * @param   array|string|SQL_Identifier $value  Column to quote
	 * @return  string  SQL fragment
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
		elseif ($namespace instanceof SQL_Table
			OR ! $namespace instanceof SQL_Identifier)
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
	 * Quote an expression's parameters for inclusion in a SQL statement.
	 *
	 * @param   SQL_Expression  $value  Expression to quote
	 * @return  string  SQL fragment
	 */
	public function quote_expression($value)
	{
		$parameters = $value->parameters;
		$value = (string) $value;

		// An expression without parameters is just raw SQL
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
	 * Quote an identifier for inclusion in a SQL statement.
	 *
	 * @param   array|string|SQL_Identifier $value  Identifier to quote
	 * @return  string  SQL fragment
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
	 * Quote a literal value for inclusion in a SQL statement.
	 *
	 * @param   mixed   $value  Literal value to quote
	 * @return  string  SQL fragment
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
	 * Quote a table identifier for inclusion in a SQL query. Adds the table
	 * prefix.
	 *
	 * @uses SQL::quote_identifier()
	 *
	 * @param   array|string|SQL_Identifier $value  Table to quote
	 * @return  string  SQL fragment
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

		$value = $prefix
			.$this->_quote_left.$this->_table_prefix.$value.$this->_quote_right;

		return $value;
	}

	/**
	 * Return the table prefix.
	 *
	 * @return  string
	 */
	public function table_prefix()
	{
		return $this->_table_prefix;
	}
}
