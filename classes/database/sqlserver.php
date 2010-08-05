<?php

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://sqlsrvphp.codeplex.com/ Microsoft SQL Server Driver for PHP
 * @link http://msdn.microsoft.com/en-us/library/ee229551.aspx SQL Server Driver for PHP Documentation
 */
class Database_SQLServer extends Database
{
	/**
	 * @var resource
	 */
	protected $_connection;

	/**
	 * Create a SQL Server connection
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  table_prefix          | string  | Table prefix
	 *  connection.charset    | string  | Character set
	 *  connection.database   | string  |
	 *  connection.hostname   | string  | Server address or alias
	 *  connection.info       | array   | [Connection attributes](http://msdn.microsoft.com/en-US/library/ff628167.aspx)
	 *  connection.password   | string  |
	 *  connection.port       | integer | Server port
	 *  connection.username   | string  |
	 *
	 * When specified, `charset`, `database`, `password` and `username` will override
	 * `CharacterSet`, `Database`, `PWD` and `UID` of `connection.info`, respectively.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name   Instance name
	 * @param   array   $config Configuration
	 */
	protected function __construct($name, $config)
	{
		parent::__construct($name, $config);

		if ( ! empty($this->_config['connection']['port']))
		{
			$this->_config['connection']['hostname'] .= ','.$this->_config['connection']['port'];
		}

		if (empty($this->_config['connection']['info']))
		{
			$this->_config['connection']['info'] = array();
		}

		if ( ! empty($this->_config['connection']['username']))
		{
			$this->_config['connection']['info']['UID'] = $this->_config['connection']['username'];
		}

		if ( ! empty($this->_config['connection']['password']))
		{
			$this->_config['connection']['info']['PWD'] = $this->_config['connection']['password'];
		}

		if ( ! empty($this->_config['connection']['database']))
		{
			$this->_config['connection']['info']['Database'] = $this->_config['connection']['database'];
		}

		if ( ! empty($this->_config['connection']['charset']))
		{
			$this->_config['connection']['info']['CharacterSet'] = $this->_config['connection']['charset'];
		}

		if (empty($this->_config['table_prefix']))
		{
			$this->_config['table_prefix'] = '';
		}
	}

	/**
	 * Recursively replace Expression and Identifier parameters until all parameters are positional
	 * literals.
	 *
	 * @param   string  $statement          SQL statement
	 * @param   array   $parameters         Unquoted parameters
	 * @param   array   $result_parameters  Parameters for the resulting statement
	 * @return  string  SQL statement
	 */
	protected function _parse($statement, $parameters, & $result_parameters)
	{
		$chunks = preg_split($this->_placeholder, $statement, NULL, PREG_SPLIT_OFFSET_CAPTURE);

		$position = 0;
		$prev = $chunks[0];
		$result = $prev[0];

		for ($i = 1, $max = count($chunks); $i < $max; ++$i)
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
			else
			{
				$result_parameters[] = $value;
				$result .= '?';
			}

			$prev = $chunks[$i];
			$result .= $prev[0];
		}

		return $result;
	}

	public function begin()
	{
		$this->_connection or $this->connect();

		if ( ! sqlsrv_begin_transaction($this->_connection))
			throw new Database_SQLServer_Exception;
	}

	public function commit()
	{
		$this->_connection or $this->connect();

		if ( ! sqlsrv_commit($this->_connection))
			throw new Database_SQLServer_Exception;
	}

	public function connect()
	{
		//try
		//{
			// FIXME is warning/error raised here?
			$this->_connection = sqlsrv_connect($this->_config['connection']['hostname'], $this->_config['connection']['info']);
		//}
		//catch (Exception $e)
		//{
		//	throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		//}

		if ( ! is_resource($this->_connection))
			throw new Database_SQLServer_Exception('Unable to connect to SQL Server ":name"', array(':name' => $this->_instance));
	}

	public function disconnect()
	{
		if (is_resource($this->_connection))
		{
			sqlsrv_close($this->_connection);

			$ths->_connection = NULL;
		}
	}

	/**
	 * Execute a SQL statement, returning the number of rows affected.
	 *
	 * @throws  Database_SQLServer_Exception
	 * @param   string  $statement  SQL command
	 * @return  integer Number of affected rows
	 */
	public function execute_command($statement)
	{
		$this->_connection or $this->connect();

		if ( ! $result = sqlsrv_query($this->_connection, $statement))
			throw new Database_SQLServer_Exception;

		if (($rows = sqlsrv_rows_affected($result)) === FALSE)
			throw new Database_SQLServer_Exception;

		sqlsrv_free_stmt($result);

		return $rows;
	}

	/**
	 * Execute a SQL statement, returning the result set or NULL when the statement is not a query,
	 * e.g., a DELETE statement.
	 *
	 * @throws  Database_SQLServer_Exception
	 * @param   string  $statement  SQL query
	 * @param   mixed   $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 * @return  Database_SQLServer_Result_Single    Result set or NULL
	 */
	public function execute_query($statement, $as_object = FALSE)
	{
		if (empty($statement))
			return NULL;

		$this->_connection or $this->connect();

		if ( ! $result = sqlsrv_query($this->_connection, $statement, NULL, array('Scrollable' => SQLSRV_CURSOR_STATIC)))
			throw new Database_SQLServer_Exception;

		if (sqlsrv_num_fields($result))
			return new Database_SQLServer_Result_Single($result, $as_object);

		sqlsrv_free_stmt($result);

		return NULL;
	}

	/**
	 * Create a prepared statement resource
	 *
	 * @link http://msdn.microsoft.com/en-US/library/cc296181.aspx
	 *
	 * @throws  Database_SQLServer_Exception
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Unquoted parameters
	 * @param   array   $options    Query properties
	 * @return  resource
	 */
	public function prepare($statement, $parameters = array(), $options = array())
	{
		$this->_connection or $this->connect();

		if ( ! $result = sqlsrv_prepare($this->_connection, $statement, $parameters, $options))
			throw new Database_SQLServer_Exception;

		return $result;
	}

	public function prepare_command($statement, $parameters = array())
	{
		$params = array();
		$statement = $this->_parse($statement, $parameters, $params);
		$stmt = $this->prepare($statement, $params);

		return new Database_SQLServer_Command($this, $stmt, $statement, $params);
	}

	public function prepare_query($statement, $parameters = array())
	{
		$params = array();
		$statement = $this->_parse($statement, $parameters, $params);
		$stmt = $this->prepare($statement, $params, array('Scrollable' => SQLSRV_CURSOR_STATIC));

		return new Database_SQLServer_Query($this, $stmt, $statement, $params);
	}

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
			$value = $prefix.'['.$value.']';
		}

		return $value;
	}

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
				$prefix .= '['.$part.'].';
			}
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		return $prefix.'['.$value.']';
	}

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

		return $prefix.'['.$this->table_prefix().$value.']';
	}

	public function rollback()
	{
		$this->_connection or $this->connect();

		if ( ! sqlsrv_rollback($this->_connection))
			throw new Database_SQLServer_Exception;
	}

	public function table_prefix()
	{
		return $this->_config['table_prefix'];
	}
}
