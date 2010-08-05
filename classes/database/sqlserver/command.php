<?php

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLServer_Command extends Database_Prepared_Command
{
	/**
	 * @var resource
	 */
	protected $_statement;

	/**
	 * @param   Database_SQLServer  $db
	 * @param   resource    $stmt       Statement resource
	 * @param   mixed       $statement  SQL query
	 * @param   array       $parameters Unquoted parameters
	 */
	public function __construct($db, $stmt, $statement, $parameters = array())
	{
		parent::__construct($db, $statement, $parameters);

		$this->_statement = $stmt;
	}

	public function __destruct()
	{
		sqlsrv_free_stmt($this->_statement);
	}

	/**
	 * Execute the command, returning the number of rows affected.
	 *
	 * @throws  Database_SQLServer_Exception
	 * @return  integer Number of affected rows
	 */
	public function execute()
	{
		if ( ! sqlsrv_execute($this->_statement))
			throw new Database_SQLServer_Exception;

		if (($rows = sqlsrv_rows_affected($this->_statement)) === FALSE)
			throw new Database_SQLServer_Exception;

		return $rows;
	}
}
