<?php

/**
 * Prepared statement for [Database_MySQL]. Parameters are positional literals.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Database_MySQL::prepare_statement()
 */
class Database_MySQL_Statement extends Database_Statement
{
	/**
	 * @var Database_MySQL
	 */
	protected $_db;

	/**
	 * @var string  Quoted statement name
	 */
	protected $_name;

	/**
	 * @var string  Original SQL of this statement
	 */
	public $statement;

	/**
	 * @param   Database_MySQL  $db
	 * @param   string  $name       Statement name
	 * @param   array   $parameters Unquoted literal parameters
	 */
	public function __construct($db, $name, $parameters = array())
	{
		$this->_db = $db;
		$this->_name = $db->quote_identifier($name);
		$this->_parameters = $parameters;
		$this->_statement =& $this->statement;
	}

	public function __toString()
	{
		return $this->_name;
	}

	/**
	 * Assign the current parameters to user variables and return the statement
	 * to execute.
	 *
	 * @throws  Database_Exception
	 * @return  string  EXECUTE statement
	 */
	protected function _set_variables()
	{
		$result = 'EXECUTE '.$this->_name;

		if ($this->_parameters)
		{
			foreach ($this->_parameters as $key => $value)
			{
				$set[] = '@kohana_'.$key.' = '.$this->_db->quote_literal($value);
				$variables[] = '@kohana_'.$key;
			}

			$this->_db->execute_command('SET '.implode(', ', $set));

			$result .= ' USING '.implode(', ', $variables);
		}

		return $result;
	}

	/**
	 * Deallocate this this prepared statement.
	 *
	 * If you do not explicitly deallocate a prepared statement, it is
	 * deallocated when the session ends.
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	public function deallocate()
	{
		$this->_db->execute_command('DEALLOCATE PREPARE '.$this->_name);
	}

	/**
	 * Execute the statement, returning the number of rows affected.
	 *
	 * @throws  Database_Exception
	 * @return  integer Number of affected rows
	 */
	public function execute_command()
	{
		return $this->_db->execute_command($this->_set_variables());
	}

	/**
	 * Execute the statement, returning the value of a column from the first
	 * row.
	 *
	 * @throws  Database_Exception
	 * @return  array   List including number of affected rows and the identity of the first row
	 */
	public function execute_insert()
	{
		return $this->_db->execute_insert($this->_set_variables(), NULL);
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
		return $this->_db->execute_query($this->_set_variables(), $as_object, $arguments);
	}
}
