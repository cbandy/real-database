<?php

/**
 * Prepared statement for [Database_PostgreSQL]. Parameters are positional
 * literals that map to named positions: $1, $2, etc.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/libpq-exec.html#LIBPQ-PQPREPARE
 *
 * @see Database_PostgreSQL::prepare_statement()
 */
class Database_PostgreSQL_Statement extends Database_Statement
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
		$this->_parameters = $parameters;
		$this->_statement =& $this->statement;
	}

	public function __toString()
	{
		return $this->_name;
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
			$this->_parameters
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
			$this->_parameters,
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
	 * @return  Database_PostgreSQL_Result  Result set or NULL
	 */
	public function execute_query($as_object = FALSE, $arguments = array())
	{
		return $this->_db->execute_prepared_query(
			$this->_name,
			$this->_parameters,
			$as_object,
			$arguments
		);
	}
}
