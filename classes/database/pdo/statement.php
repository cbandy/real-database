<?php

/**
 * Prepared statement for [Database_PDO].
 *
 * @package     RealDatabase
 * @subpackage  PDO
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_Statement
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
	 * Bind a variable to a parameter.
	 *
	 * @param   integer $param  Parameter index
	 * @param   mixed   $var    Variable to bind
	 * @return  $this
	 */
	public function bind($param, & $var)
	{
		if (is_string($var))
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

		return $this;
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
	 * Set the value of a parameter.
	 *
	 * @param   integer $param  Parameter index
	 * @param   mixed   $value  Literal value to assign
	 * @return  $this
	 */
	public function param($param, $value)
	{
		if (is_string($value))
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

		return $this;
	}

	/**
	 * Add multiple parameter values.
	 *
	 * @param   array   $params Literal values to assign
	 * @return  $this
	 */
	public function parameters($params)
	{
		foreach ($params as $param => $value)
		{
			$this->param($param, $value);
		}

		return $this;
	}
}
