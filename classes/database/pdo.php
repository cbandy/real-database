<?php

/**
 * @package PDO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO extends Database implements Database_iInsert
{
	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns
	 * @return  Database_Command_Insert_Identity
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_Command_Insert_Identity($table, $columns);
	}

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

	public function begin()
	{
		$this->_connection or $this->connect();

		try
		{
			$this->_connection->beginTransaction();
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}
	}

	public function commit()
	{
		$this->_connection or $this->connect();

		try
		{
			$this->_connection->commit();
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
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

		try
		{
			return $this->_connection->exec($statement);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}
	}

	public function execute_insert($statement)
	{
		return array($this->execute_command($statement), $this->_connection->lastInsertId());
	}

	public function execute_query($statement, $as_object = FALSE)
	{
		if (empty($statement))
			return NULL;

		$this->_connection or $this->connect();

		try
		{
			$statement = $this->_connection->query($statement);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
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

		try
		{
			return $this->_connection->prepare($statement);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}
	}

	public function rollback()
	{
		$this->_connection or $this->connect();

		try
		{
			$this->_connection->rollBack();
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}
	}

	public function table_prefix()
	{
		return $this->_config['schema'];
	}
}
