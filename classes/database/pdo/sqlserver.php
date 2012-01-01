<?php

/**
 * [SQL Server](http://www.microsoft.com/sqlserver/) connection and expression
 * factory using PDO.
 *
 *  Configuration Option    | Type    | Description
 *  --------------------    | ----    | -----------
 *  release_during_rollback | boolean | Release savepoints during rollback
 *  table_prefix            | string  | Table prefix
 *  connection.dsn          | string  | Full DSN or a predefined DSN name
 *  connection.options      | array   | PDO options
 *  connection.password     | string  |
 *  connection.persistent   | boolean | Use the PHP connection pool
 *  connection.uri          | string  | URI to a file containing the DSN
 *  connection.username     | string  |
 *
 * *[DSN]: Data Source Name
 * *[PDO]: PHP Data Objects
 * *[URI]: Uniform Resource Identifier
 *
 * [!!] Set `PDO::SQLSRV_ATTR_ENCODING` in `connection.options` to use an
 * encoding other than UTF-8.
 *
 * @link http://sqlsrvphp.codeplex.com/ Microsoft Drivers for PHP for SQL Server
 * @link http://msdn.microsoft.com/library/ee229547.aspx Documentation
 * @link http://msdn.microsoft.com/library/ff628159.aspx PDO connection parameters
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_SQLServer extends Database_PDO
	implements Database_iIntrospect
{
	/**
	 * Maximum number of unicode characters allowed in an identifier
	 *
	 * @link http://msdn.microsoft.com/library/ms175874.aspx Identifiers
	 */
	const MAX_LENGTH_IDENTIFIER = 116;

	/**
	 * Create a column expression.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @param   mixed                                       $type   Converted to SQL_Expression
	 * @return  Database_SQLServer_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_SQLServer_DDL_Column($name, $type);
	}

	/**
	 * Create a DELETE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  Database_SQLServer_DML_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_SQLServer_DML_Delete($table, $alias);
	}

	/**
	 * Create an INSERT statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns, each converted to SQL_Column
	 * @return  Database_SQLServer_DML_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_SQLServer_DML_Insert($table, $columns);
	}

	/**
	 * Create a SELECT statement.
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs
	 * @return  Database_SQLServer_DML_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_SQLServer_DML_Select($columns);
	}

	/**
	 * Create an UPDATE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @param   array                                       $values Hash of (column => value) assignments
	 * @return  Database_SQLServer_DML_Update
	 */
	public static function update($table = NULL, $alias = NULL, $values = NULL)
	{
		return new Database_SQLServer_DML_Update($table, $alias, $values);
	}

	protected $_quote_left = '[';

	protected $_quote_right = ']';

	/**
	 * Create a PDO connection for SQL Server
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct($name, $config);

		// Execute queries directly by default
		$this->_config['connection']['options'][PDO::SQLSRV_ATTR_DIRECT_QUERY]
			= TRUE;
	}

	/**
	 * Set the connection encoding.
	 *
	 * @link http://msdn.microsoft.com/library/cc296183.aspx
	 *
	 * @throws  Database_Exception
	 * @param   integer $encoding   Encoding constant
	 * @return  void
	 */
	public function charset($encoding)
	{
		$this->_connection or $this->connect();

		try
		{
			$this->_connection->setAttribute(
				PDO::SQLSRV_ATTR_ENCODING, $encoding
			);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
		}
	}

	public function commit($name = NULL)
	{
		$this->_connection or $this->connect();

		if (Kohana::$profiling)
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'commit('.$name.')'
			);
		}

		try
		{
			if ($name === NULL
				OR $this->_savepoints->uncommitted_position($name) === 1)
			{
				$this->_connection->commit();

				// Reset the savepoint stack
				$this->_savepoints->reset();
			}
			else
			{
				// Remove this savepoint and all savepoints after it from the
				// uncommitted stack.
				// TODO: a NULL result means the savepoint is invalid
				if ( ! $this->_savepoints->commit_to($name));
			}
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function connect()
	{
		parent::connect();

		// Initialize the savepoint stack
		$this->_savepoints = new Database_SQLServer_Savepoints;
	}

	public function datatype($type, $attribute = NULL)
	{
		static $types = array
		(
			// SQL Server >= 2005
			'bit'               => array('type' => 'boolean'),
			'datetime'          => array('type' => 'datetime'),
			'image'             => array('type' => 'binary'),
			'money'             => array('type' => 'float', 'exact' => TRUE, 'min' => '-922337203685477.5808', 'max' => '922337203685477.5807'),
			'ntext'             => array('type' => 'string'),
			'nvarchar'          => array('type' => 'string'),
			'smalldatetime'     => array('type' => 'datetime'),
			'smallmoney'        => array('type' => 'float', 'exact' => TRUE, 'min' => '-214748.3648', 'max' => '214748.3647'),
			'sql_variant'       => array('type' => 'mixed'),
			'text'              => array('type' => 'string'),
			'timestamp'         => array('type' => 'integer'),
			'tinyint'           => array('type' => 'integer', 'min' => '0', 'max' => '255'),
			'uniqueidentifier'  => array('type' => 'string'),
			'xml'               => array('type' => 'string'),

			// SQL Server >= 2008
			'datetime2'         => array('type' => 'datetime'),
			'datetimeoffset'    => array('type' => 'datetime'),
			'geography'         => array('type' => 'string'),
			'geometry'          => array('type' => 'string'),
			'hierarchyid'       => array('type' => 'string'),
			'rowversion'        => array('type' => 'integer'),
		);

		if ( ! isset($types[$type]))
			return parent::datatype($type, $attribute);

		if ($attribute !== NULL)
			return @$types[$type][$attribute];

		return $types[$type];
	}

	public function prepare($statement, $options = array())
	{
		// Disable direct execution while respecting all options
		$options = array(PDO::SQLSRV_ATTR_DIRECT_QUERY => FALSE) + $options;

		return parent::prepare($statement, $options);
	}

	public function rollback($name = NULL)
	{
		$this->_connection or $this->connect();

		if (Kohana::$profiling)
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'rollback('.$name.')'
			);
		}

		try
		{
			if ($name === NULL
				OR $this->_savepoints->uncommitted_position($name) === 1)
			{
				$this->_connection->rollBack();

				// Reset the savepoint stack
				$this->_savepoints->reset();
			}
			else
			{
				// Rollback any intervening committed duplicates
				while ($this->_savepoints->position($name)
					> $this->_savepoints->position_uncommitted($name))
				{
					// Rollback and release the committed savepoint
					$this->_connection->exec(
						'ROLLBACK TRANSACTION '
						.$this->_quote_left.$name.$this->_quote_right
					);

					// Remove all savepoints after it
					$this->_savepoints->pop_until($name);

					// Remove the released savepoint
					$this->_savepoints->pop();
				}

				if ( ! empty($this->_config['release_during_rollback']))
				{
					// Rollback and release the savepoint
					$this->_connection->exec(
						'ROLLBACK TRANSACTION '
						.$this->_quote_left.$name.$this->_quote_right
					);

					// Remove all savepoints after this one
					$this->_savepoints->pop_until($name);

					// Remove this savepoint
					$this->_savepoints->pop();
				}
				else
				{
					// Reinstate the savepoint after rollback
					$this->_connection->exec(
						'ROLLBACK TRANSACTION '
						.$this->_quote_left.$name.$this->_quote_right
						.'; SAVE TRANSACTION '
						.$this->_quote_left.$name.$this->_quote_right
					);

					// Remove all savepoints after this one
					$this->_savepoints->pop_until($name);
				}
			}
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function savepoint($name = NULL)
	{
		if ($name === NULL)
		{
			$name = 'kohana_txn_'.count($this->_savepoints);
		}

		$this->_connection or $this->connect();

		if (Kohana::$profiling)
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'savepoint('.$name.')'
			);
		}

		try
		{
			$this->_connection->exec(
				'SAVE TRANSACTION '.$this->_quote_left.$name.$this->_quote_right
			);
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		$this->_savepoints->push($name);

		return $name;
	}

	public function schema_tables($schema = NULL)
	{
		$sql = 'SELECT table_name, table_type FROM information_schema.tables';
		$parameters = array();

		if ( ! $schema)
		{
			// Use the default schema of the connected user
			$sql .= ' JOIN sys.database_principals'
				." ON (type = 'S' AND name = user_name())"
				.' WHERE table_schema = default_schema_name';
		}
		else
		{
			if ( ! $schema instanceof SQL_Identifier)
			{
				// Convert to identifier
				$schema = new SQL_Identifier($schema);
			}

			$sql .= ' WHERE table_schema = ?';
			$parameters[] = $schema->name;

			if ($catalog = $schema->namespace)
			{
				if ( ! $catalog instanceof SQL_Identifier)
				{
					// Convert to identifier
					$catalog = new SQL_Identifier($catalog);
				}

				$sql .= ' AND table_catalog = ?';
				$parameters[] = $catalog->name;
			}
		}

		if ( ! $this->_table_prefix)
		{
			// No table prefix
			return $this->execute_query(new Database_Statement($sql, $parameters))
				->as_array('table_name');
		}

		// Filter on table prefix
		$sql .= ' AND table_name LIKE ? ESCAPE ?';
		$parameters[] = strtr($this->_table_prefix, array('_' => '\_', '%' => '\%')).'%';
		$parameters[] = '\\';

		$prefix = strlen($this->_table_prefix);
		$result = array();

		foreach ($this->execute_query(new Database_Statement($sql, $parameters)) as $table)
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

		$sql =
			'SELECT column_name, ordinal_position, column_default, is_nullable,'
			.'   data_type, character_maximum_length,'
			.'   numeric_precision, numeric_scale, datetime_precision'
			.' FROM information_schema.columns';

		if ( ! $schema = $table->namespace)
		{
			// Use the default schema of the connected user
			$sql .= ' JOIN sys.database_principals'
				." ON (type = 'S' AND name = user_name())"
				.' WHERE table_schema = default_schema_name';
		}
		else
		{
			if ( ! $schema instanceof SQL_Identifier)
			{
				// Convert to identifier
				$schema = new SQL_Identifier($schema);
			}

			$sql .= ' WHERE table_schema = ?';
			$parameters[] = $schema->name;

			if ($catalog = $schema->namespace)
			{
				if ( ! $catalog instanceof SQL_Identifier)
				{
					// Convert to identifier
					$catalog = new SQL_Identifier($catalog);
				}

				$sql .= ' AND table_catalog = ?';
				$parameters[] = $catalog->name;
			}
		}

		$sql .= ' AND table_name = ?';

		// Only add the table prefix to SQL_Table (exclude from SQL_Identifier)
		$parameters[] = ($table instanceof SQL_Table)
			? ($this->_table_prefix.$table->name)
			: $table->name;

		return $this->execute_query(new Database_Statement($sql, $parameters))
			->as_array('column_name');
	}
}
