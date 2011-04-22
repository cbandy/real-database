<?php

/**
 * Prepared statement for [Database_PostgreSQL].
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PostgreSQL_Statement
{
	/**
	 * @var Database_PostgreSQL
	 */
	protected $_db;

	/**
	 * @var string  Statement name
	 */
	protected $_name;

	/**
	 * @var array   Unquoted parameters
	 */
	public $parameters;

	/**
	 * @var string  Original SQL of this statement
	 */
	public $statement;

	/**
	 * @param   Database_PostgreSQL $db
	 * @param   string  $name       Statement name
	 * @param   array   $parameters Unquoted literal parameters
	 */
	public function __construct($db, $name, $parameters = array())
	{
		$this->_db = $db;
		$this->_name = $name;
		$this->parameters = $parameters;
	}

	public function __toString()
	{
		return $this->_name;
	}

	/**
	 * Bind a variable to a parameter.
	 *
	 * @param   string  $param  Parameter index, e.g., '$1'
	 * @param   mixed   $var    Variable to bind
	 * @return  $this
	 */
	public function bind($param, & $var)
	{
		$this->parameters[$param] =& $var;

		return $this;
	}

	/**
	 * Deallocate this this prepared statement
	 *
	 * If you do not explicitly deallocate a prepared statement, it is
	 * deallocated when the session ends.
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	public function deallocate()
	{
		$this->_db->execute_command(
			'DEALLOCATE '.$this->_db->quote_identifier($this->_name)
		);
	}

	/**
	 * Execute the statement, returning the number of rows affected.
	 *
	 * @throws  Database_Exception
	 * @return  integer Number of affected rows
	 */
	public function execute_command()
	{
		return $this->_db->execute_prepared_command(
			$this->_name,
			$this->parameters
		);
	}

	/**
	 * Execute the statement, returning the value of a column from the first
	 * row.
	 *
	 * @throws  Database_Exception
	 * @param   mixed           $identity   Converted to SQL_Column
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the row class constructor
	 * @return  array   List including number of affected rows and a value from the first row
	 */
	public function execute_insert($identity, $as_object = FALSE, $arguments = array())
	{
		return $this->_db->execute_prepared_insert(
			$this->_name,
			$identity,
			$this->parameters,
			$as_object,
			$arguments
		);
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
		return $this->_db->execute_prepared_query(
			$this->_name,
			$this->parameters,
			$as_object,
			$arguments
		);
	}

	/**
	 * Set the value of a parameter.
	 *
	 * @param   string  $param  Parameter index, e.g., '$1'
	 * @param   mixed   $value  Literal value to assign
	 * @return  $this
	 */
	public function param($param, $value)
	{
		$this->parameters[$param] = $value;

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
		$this->parameters = $params + $this->parameters;

		return $this;
	}
}
