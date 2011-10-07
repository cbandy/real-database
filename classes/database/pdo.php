<?php

/**
 * PDO connection.
 *
 *  Configuration Option  | Type    | Description
 *  --------------------  | ----    | -----------
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
 * *[PDO]: PHP Data Objects
 * *[URI]: Uniform Resource Identifier
 *
 * @link http://www.php.net/manual/book.pdo
 * @link http://www.php.net/manual/pdo.construct PDO connection parameters
 *
 * @package     RealDatabase
 * @subpackage  PDO
 * @category    Drivers
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

	/**
	 * @var Database_Savepoint_Stack    Stack of savepoint names
	 */
	protected $_savepoints;

	/**
	 * Create a PDO connection
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct($name, $config);

		// Use exceptions for all errors
		$this->_config['connection']['options'][PDO::ATTR_ERRMODE]
			= PDO::ERRMODE_EXCEPTION;

		if ( ! empty($this->_config['connection']['persistent']))
		{
			$this->_config['connection']['options'][PDO::ATTR_PERSISTENT]
				= TRUE;
		}

		if ( ! empty($this->_config['connection']['uri']))
		{
			$this->_config['connection']['dsn']
				= 'uri:'.$this->_config['connection']['uri'];
		}
	}

	public function begin($name = NULL)
	{
		if (count($this->_savepoints))
		{
			// Nested transaction
			return $this->savepoint($name);
		}

		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'begin('.$name.')'
			);
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

			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		if ($name === NULL)
		{
			$name = 'kohana_txn_'.count($this->_savepoints);
		}

		$this->_savepoints->push($name);

		return $name;
	}

	public function charset($charset)
	{
		// This SQL-92 syntax is not supported by all drivers
		$this->execute_command('SET NAMES '.$this->quote_literal($charset));
	}

	public function commit($name = NULL)
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'commit('.$name.')'
			);
		}

		try
		{
			if ($name === NULL OR $this->_savepoints->position($name) === 1)
			{
				$this->_connection->commit();

				// Reset the savepoint stack
				$this->_savepoints->reset();
			}
			else
			{
				// This SQL:1999 syntax is not supported by all drivers
				$this->_connection->exec(
					'RELEASE SAVEPOINT '
					.$this->_quote_left.$name.$this->_quote_right
				);

				// Remove all savepoints after this one
				$this->_savepoints->pop_until($name);

				// Remove this savepoint
				$this->_savepoints->pop();
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
		try
		{
			$this->_connection = new PDO(
				$this->_config['connection']['dsn'],
				$this->_config['connection']['username'],
				$this->_config['connection']['password'],
				$this->_config['connection']['options']
			);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
		}

		// Initialize the savepoint stack
		$this->_savepoints = new Database_Savepoint_Stack;
	}

	public function disconnect()
	{
		$this->_connection = NULL;
	}

	/**
	 * Quote a literal value while escaping characters that could cause an SQL
	 * injection attack.
	 *
	 * Not all drivers support this method.
	 *
	 * @param   mixed   $value  Literal value to quote
	 * @return  string
	 */
	public function escape_literal($value)
	{
		$this->_connection or $this->connect();

		return $this->_connection->quote( (string) $value);
	}

	public function execute_command($statement)
	{
		$this->_connection or $this->connect();

		if ( ! is_string($statement))
		{
			if ( ! $statement instanceof Database_Statement)
			{
				$statement = $this->parse_statement($statement);
			}

			$parameters = $statement->parameters();
			$statement = (string) $statement;
		}

		if (empty($statement))
			return 0;

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', $statement
			);
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

			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
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
	 * @param   string|Database_Statement|SQL_Expression    $statement  SQL insert
	 * @param   mixed                                       $identity   Ignored
	 * @return  array   List including number of affected rows and an identity value
	 */
	public function execute_insert($statement, $identity)
	{
		return array(
			$this->execute_command($statement),
			$this->last_insert_id()
		);
	}

	public function execute_query($statement, $as_object = FALSE, $arguments = array())
	{
		$this->_connection or $this->connect();

		if ( ! is_string($statement))
		{
			if ( ! $statement instanceof Database_Statement)
			{
				$statement = $this->parse_statement($statement);
			}

			$parameters = $statement->parameters();
			$statement = (string) $statement;
		}

		if (empty($statement))
			return NULL;

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', $statement
			);
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

			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
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
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'prepare('.$statement.')'
			);
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

			throw new Database_Exception(
				':error', array(':error' => $e->getMessage()), $e->getCode()
			);
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Created a prepared statement from a PDO-compatible [Database_Statement]
	 * or a generic [SQL_Expression].
	 *
	 * @throws  Database_Exception
	 * @param   Database_Statement|SQL_Expression   $statement  SQL statement
	 * @return  Database_PDO_Statement
	 */
	public function prepare_statement($statement)
	{
		if ( ! $statement instanceof Database_Statement)
		{
			$statement = $this->parse_statement($statement);
		}

		return new Database_PDO_Statement(
			$this,
			$this->prepare( (string) $statement),
			$statement->parameters()
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

	public function rollback($name = NULL)
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'rollback('.$name.')'
			);
		}

		try
		{
			if ($name === NULL OR $this->_savepoints->position($name) === 1)
			{
				$this->_connection->rollBack();

				// Reset the savepoint stack
				$this->_savepoints->reset();
			}
			else
			{
				// This SQL:1999 syntax is not supported by all drivers
				$this->_connection->exec(
					'ROLLBACK TO '.$this->_quote_left.$name.$this->_quote_right
				);

				// Remove all savepoints after this one
				$this->_savepoints->pop_until($name);
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

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', 'savepoint('.$name.')'
			);
		}

		try
		{
			// This SQL:1999 syntax is not supported by all drivers
			$this->_connection->exec(
				'SAVEPOINT '.$this->_quote_left.$name.$this->_quote_right
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
}
