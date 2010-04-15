<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Prepared_Command extends Database_Expression
{
	/**
	 * @var Database
	 */
	protected $_db;

	/**
	 * @param   Database    $db
	 * @param   mixed       $statement  SQL or database-specific handle
	 * @param   array       $parameters Unquoted parameters
	 */
	public function __construct($db, $statement, $parameters)
	{
		parent::__construct($statement, $parameters);

		$this->_db = $db;
	}

	/**
	 * Execute the command, returning the number of rows affected
	 *
	 * @throws  Database_Exception
	 * @return  integer Number of affected rows
	 */
	public function execute()
	{
		return $this->_db->execute_command($this->compile($this->_db));
	}
}
