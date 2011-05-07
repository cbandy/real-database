<?php

/**
 * Prepared statement for [Database_PDO]. Parameters are named or 1-indexed
 * positional literals (not both).
 *
 * @package     RealDatabase
 * @subpackage  PDO
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_Statement extends Database_Statement
{
	/**
	 * @var Database_PDO
	 */
	protected $_db;

	/**
	 * @var PDOStatement
	 */
	protected $_statement;

	/**
	 * @uses Database_PDO_Statement::parameters()
	 *
	 * @param   Database_PDO    $db
	 * @param   PDOStatement    $statement  Prepared statement
	 * @param   array           $parameters Unquoted literal parameters
	 */
	public function __construct($db, $statement, $parameters = array())
	{
		$this->_db = $db;
		$this->_parameters = array();
		$this->_statement = $statement;

		$this->parameters($parameters);
	}

	public function __toString()
	{
		return $this->_statement->queryString;
	}

	/**
	 * Execute the statement.
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	protected function _execute()
	{
		if ($this->_db->profiling())
		{
			$benchmark = Profiler::start("Database ($this->_db)", 'Prepared: '.$this->_statement->queryString);
		}

		try
		{
			$this->_statement->execute();
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

	/**
	 * Bind a variable to a parameter, optionally specifying the data type using
	 * a [PDO::PARAM constant](http://php.net/manual/pdo.constants).
	 *
	 * @param   integer $param  Parameter index
	 * @param   mixed   $var    Variable to bind
	 * @param   integer $type   Parameter type, PDO::PARAM_* constant
	 * @return  $this
	 */
	public function bind($param, & $var, $type = NULL)
	{
		if ($type !== NULL)
		{
			$this->_statement->bindParam($param, $var, $type);
		}
		elseif (is_string($var))
		{
			$this->_statement->bindParam($param, $var, PDO::PARAM_STR);
		}
		elseif (is_int($var))
		{
			$this->_statement->bindParam($param, $var, PDO::PARAM_INT);
		}
		elseif (is_bool($var))
		{
			$this->_statement->bindParam($param, $var, PDO::PARAM_BOOL);
		}
		else
		{
			$this->_statement->bindParam($param, $var);
		}

		return parent::bind($param, $var);
	}

	/**
	 * Execute the statement, returning the number of rows affected.
	 *
	 * @throws  Database_Exception
	 * @return  integer Number of affected rows
	 */
	public function execute_command()
	{
		if (empty($this->_statement->queryString))
			return 0;

		$this->_execute();

		return $this->_statement->rowCount();
	}

	/**
	 * Execute the statement, returning the value of an IDENTITY column.
	 *
	 * Behavior varies between driver implementations. Reliable only when
	 * inserting one row.
	 *
	 * @throws  Database_Exception
	 * @return  array   List including number of affected rows and an identity value
	 */
	public function execute_insert()
	{
		return array($this->execute_command(), $this->_db->last_insert_id());
	}

	/**
	 * Execute the statement, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement).
	 *
	 * @throws  Database_Exception
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the row class constructor
	 * @return  Database_Result Result set or NULL
	 */
	public function execute_query($as_object = FALSE, $arguments = array())
	{
		if (empty($this->_statement->queryString))
			return NULL;

		$this->_execute();

		if ($this->_statement->columnCount() === 0)
			return NULL;

		return new Database_PDO_Result($this->_statement, $as_object, $arguments);
	}

	/**
	 * Set the value of a parameter, optionally specifying the data type using
	 * a [PDO::PARAM constant](http://php.net/manual/pdo.constants).
	 *
	 * @param   integer $param  Parameter index
	 * @param   mixed   $value  Literal value to assign
	 * @param   integer $type   Parameter type, PDO::PARAM_* constant
	 * @return  $this
	 */
	public function param($param, $value, $type = NULL)
	{
		if ($type !== NULL)
		{
			$this->_statement->bindValue($param, $value, $type);
		}
		elseif (is_string($value))
		{
			$this->_statement->bindValue($param, $value, PDO::PARAM_STR);
		}
		elseif (is_int($value))
		{
			$this->_statement->bindValue($param, $value, PDO::PARAM_INT);
		}
		elseif (is_bool($value))
		{
			$this->_statement->bindValue($param, $value, PDO::PARAM_BOOL);
		}
		else
		{
			$this->_statement->bindValue($param, $value);
		}

		return parent::param($param, $value);
	}

	/**
	 * Set multiple parameter values or return the current parameter values.
	 *
	 * @param   array   $params Values to assign or NULL to return the current values
	 * @return  $this|array
	 */
	public function parameters($params = NULL)
	{
		if ($params !== NULL)
		{
			foreach ($params as $param => $value)
			{
				$this->param($param, $value);
			}
		}

		return parent::parameters($params);
	}
}
