<?php

/**
 * PDO connection.
 *
 * *[PDO]: PHP Data Objects
 *
 * @package     RealDatabase
 * @subpackage  PDO
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/book.pdo
 */
class Database_PDO extends Database
{
	/**
	 * @var PDO
	 */
	protected $_connection;

	/**
	 * Create a PDO connection
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | string  | Character set
	 *  profiling             | boolean | Enable execution profiling
	 *  table_prefix          | string  | Table prefix
	 *  connection.dsn        | string  | Full DSN or a predefined DSN name
	 *  connection.options    | array   | Driver-specific options
	 *  connection.password   | string  |
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.uri        | string  | URI to a file containing the DSN
	 *  connection.username   | string  |
	 *
	 * *[DSN]: Data Source Name
	 * *[URI]: Uniform Resource Identifier
	 *
	 * @link http://php.net/manual/pdo.construct PDO connection parameters
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct($name, $config);

		// Use exceptions for all errors
		$this->_config['connection']['options'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		if ( ! empty($this->_config['connection']['persistent']))
		{
			$this->_config['connection']['options'][PDO::ATTR_PERSISTENT] = TRUE;
		}

		if ( ! empty($this->_config['connection']['uri']))
		{
			$this->_config['connection']['dsn'] = 'uri:'.$this->_config['connection']['uri'];
		}

		if (empty($this->_config['table_prefix']))
		{
			$this->_config['table_prefix'] = '';
		}
	}

	/**
	 * Replace array, expression and identifier parameters until all parameters
	 * are 1-indexed, positional literals.
	 *
	 * @param   SQL_Expression  $statement  SQL statement
	 * @return  array   List including the SQL string and parameter array
	 */
	protected function _parse_statement($statement)
	{
		// PDOStatement parameters are 1-indexed, pad the array with a value
		$parameters = array(NULL);

		$statement = $this->_parse(
			(string) $statement,
			$statement->parameters,
			$parameters
		);

		// Remove padding
		unset($parameters[0]);

		return array($statement, $parameters);
	}

	public function begin()
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_name)", 'begin()');
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

			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function charset($charset)
	{
		$this->execute_command("SET NAMES '$charset'");
	}

	public function commit()
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_name)", 'commit()');
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

			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
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
			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
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
	 * attack
	 *
	 * Not all drivers support this method.
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function escape($value)
	{
		$this->_connection or $this->connect();

		return $this->_connection->quote( (string) $value);
	}

	public function execute_command($statement)
	{
		$this->_connection or $this->connect();

		if ($statement instanceof SQL_Expression)
		{
			list($statement, $parameters) = $this->_parse_statement($statement);
		}
		elseif ( ! is_string($statement))
		{
			$statement = $this->quote($statement);
		}

		if (empty($statement))
			return 0;

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_name)", $statement);
		}

		try
		{
			if (empty($parameters))
			{
				$result = $this->_connection->exec($statement);
			}
			else
			{
				$result = $this->_connection->prepare($statement);
				$result->execute($parameters);
				$result = $result->rowCount();
			}
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
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
	 * @param   string|SQL_Expression   $statement  SQL insert
	 * @param   mixed                   $identity   Ignored
	 * @return  array   List including number of affected rows and an identity value
	 */
	public function execute_insert($statement, $identity)
	{
		return array($this->execute_command($statement), $this->last_insert_id());
	}

	public function execute_query($statement, $as_object = FALSE, $arguments = array())
	{
		$this->_connection or $this->connect();

		if ($statement instanceof SQL_Expression)
		{
			list($statement, $parameters) = $this->_parse_statement($statement);
		}
		elseif ( ! is_string($statement))
		{
			$statement = $this->quote($statement);
		}

		if (empty($statement))
			return NULL;

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_name)", $statement);
		}

		try
		{
			if (empty($parameters))
			{
				$statement = $this->_connection->query($statement);
			}
			else
			{
				$statement = $this->_connection->prepare($statement);
				$statement->execute($parameters);
			}
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		if ($statement->columnCount() === 0)
			return NULL;

		return new Database_PDO_Result($statement, $as_object, $arguments);
	}

	/**
	 * Retrieve the identity of a row from the previously executed INSERT.
	 *
	 * Not all drivers support this method. If multiple rows were inserted, the
	 * row to which the identity value belongs depends on the driver.
	 *
	 * @return  string The identity value of an inserted row
	 */
	public function last_insert_id()
	{
		return $this->_connection->lastInsertId();
	}

	/**
	 * Create a prepared statement after connecting
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL statement
	 * @param   array   $options    Hash of (option => value) pairs
	 * @return  PDOStatement    Prepared statement
	 */
	public function prepare($statement, $options = array())
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_name)", "prepare($statement)");
		}

		try
		{
			$result = $this->_connection->prepare($statement, $options);
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Create a prepared statement from a SQL expression object.
	 *
	 * @throws  Database_Exception
	 * @param   SQL_Expression  $statement  SQL statement
	 * @return  Database_PDO_Statement
	 */
	public function prepare_statement($statement)
	{
		list($statement, $parameters) = $this->_parse_statement($statement);

		return new Database_PDO_Statement(
			$this,
			$this->prepare($statement),
			$parameters
		);
	}

	/**
	 * Whether or not profiling is enabled
	 *
	 * @return  boolean
	 */
	public function profiling()
	{
		return ! empty($this->_config['profiling']);
	}

	public function rollback()
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_name)", 'rollback()');
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

			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function table_prefix()
	{
		return $this->_config['table_prefix'];
	}
}
