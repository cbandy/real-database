<?php

/**
 * @package PDO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO extends Database
{
	/**
	 * @var PDO
	 */
	protected $_connection;

	protected function __construct($name, $config)
	{
		parent::__construct($name, $config);

		// Use exceptions for all errors
		$this->_config['connection']['options'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		if ( ! empty($this->_config['connection']['persistent']))
		{
			$this->_config['connection']['options'][PDO::ATTR_PERSISTENT] = TRUE;
		}

		if ( ! isset($this->_config['schema']))
		{
			$this->_config['schema'] = '';
		}
	}

	/**
	 * Recursively replace Expression and Identifier parameters until all
	 * parameters are positional literals.
	 *
	 * @param   string  $statement          SQL statement
	 * @param   array   $parameters         Unquoted parameters
	 * @param   array   $result_parameters  Parameters for the resulting statement
	 * @return  string  SQL statement
	 */
	protected function _parse($statement, $parameters, & $result_parameters)
	{
		$chunks = preg_split($this->_placeholder, $statement, NULL, PREG_SPLIT_OFFSET_CAPTURE);

		$max = count($chunks);
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

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", 'begin()');
		}

		try
		{
			$this->_connection->beginTransaction();
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function commit()
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", 'commit()');
		}

		try
		{
			$this->_connection->commit();
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function connect()
	{
		try
		{
			$this->_connection = new PDO(
				$this->_config['connection']['dsn'],
				$this->_config['connection']['username'],
				$this->_config['connection']['password'],
				$this->_config['connection']['options']);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ( ! empty($this->_config['charset']))
		{
			$this->charset($this->_config['charset']);
		}
	}

	public function disconnect()
	{
		$this->_connection = NULL;
	}

	/**
	 * Quote a value while escaping characters that could cause a SQL injection
	 * attack.
	 *
	 * Not all drivers support this method.
	 *
	 * @param   mixed   Value to quote
	 * @return  string
	 */
	public function escape($value)
	{
		$this->_connection or $this->connect();

		return $this->_connection->quote((string) $value);
	}

	public function execute_command($statement)
	{
		if (empty($statement))
			return 0;

		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", $statement);
		}

		try
		{
			$result = $this->_connection->exec($statement);
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Execute an INSERT statement, returning the number of affected rows and
	 * the identity of one affected row.
	 *
	 * Not all drivers support this method. When inserting multiple rows, the
	 * row to which the identity value belongs depends on the driver.
	 *
	 * @param   string  $statement  INSERT statement
	 * @return  array   List including number of affected rows and an identity value
	 */
	public function execute_insert($statement)
	{
		return array($this->execute_command($statement), $this->_connection->lastInsertId());
	}

	public function execute_query($statement, $as_object = FALSE)
	{
		if (empty($statement))
			return NULL;

		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", $statement);
		}

		try
		{
			$statement = $this->_connection->query($statement);
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		if ($statement->columnCount() === 0)
			return NULL;

		return new Database_PDO_Result($statement, $as_object);
	}

	/**
	 * Create a prepared statement after connecting
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL statement
	 * @return  PDOStatement
	 */
	public function prepare($statement)
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", "prepare($statement)");
		}

		try
		{
			$result = $this->_connection->prepare($statement);
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	public function prepare_command($statement, $parameters = array())
	{
		$params = array(NULL);
		$statement = $this->prepare($this->_parse($statement, $parameters, $params));
		unset($params[0]);

		return new Database_PDO_Command($this, $statement, $params);
	}

	public function prepare_query($statement, $parameters = array())
	{
		$params = array(NULL);
		$statement = $this->prepare($this->_parse($statement, $parameters, $params));
		unset($params[0]);

		return new Database_PDO_Query($this, $statement, $params);
	}

	public function rollback()
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", 'rollback()');
		}

		try
		{
			$this->_connection->rollBack();
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function table_prefix()
	{
		return $this->_config['schema'];
	}
}
