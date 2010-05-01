<?php

/**
 * @package SQLite
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLite2 extends Database_Escape implements Database_iInsert
{
	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns
	 * @return  Database_SQLite2_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_SQLite2_Insert($table, $columns);
	}

	/**
	 * @var SQLiteDatabase
	 */
	protected $_connection;

	protected function __construct($name, $config)
	{
		parent::__construct($name, $config);

		if (empty($this->_config['schema']))
		{
			$this->_config['schema'] = '';
		}
	}

	/**
	 * Execute a statement after connecting
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL statement
	 * @return  void
	 */
	protected function _execute($statement)
	{
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			$benchmark = Profiler::start("Database ($this->_instance)", $statement);
		}

		try
		{
			// Raises E_WARNING upon error
			$result = $this->_connection->queryExec($statement, $error);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ( ! $result)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $error));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}

	public function begin()
	{
		$this->_execute('BEGIN');
	}

	public function charset($charset)
	{
		$this->_execute('PRAGMA encoding = "'.$charset.'"');
	}

	public function commit()
	{
		$this->_execute('COMMIT');
	}

	public function connect()
	{
		$this->_connection = new SQLiteDatabase($this->_config['connection']['filename']);

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
		$value = sqlite_escape_string((string) $value);

		return "'$value'";
	}

	/**
	 * Execute a SQL statement, returning the number of rows affected
	 *
	 * DELETE statements that do not have a WHERE clause are optimized
	 * internally such that no count is obtained. These statements will always
	 * return 0.
	 *
	 * @link http://www.sqlite.org/c_interface.html
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL command
	 * @return  integer Number of affected rows
	 */
	public function execute_command($statement)
	{
		if (empty($statement))
			return 0;

		$this->_execute($statement);

		return $this->_connection->changes();
	}

	public function execute_insert($statement)
	{
		return array($this->execute_command($statement), $this->_connection->lastInsertRowid());
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
			// Raises E_WARNING upon error
			$result = $this->_connection->query($statement, SQLITE_ASSOC, $error);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ( ! $result)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $error));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		if ($result->numFields() === 0)
			return NULL;

		return new Database_SQLite2_Result($result, $as_object);
	}

	public function rollback()
	{
		$this->_execute('ROLLBACK');
	}

	public function table_prefix()
	{
		return $this->_config['schema'];
	}
}
