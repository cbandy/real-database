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
class Database_SQLServer_Query extends Database_Prepared_Query
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
	 * Execute the query, returning the result set or NULL when the statement is not a query,
	 * e.g., a DELETE statement.
	 *
	 * @throws  Database_SQLServer_Exception
	 * @return  Database_SQLServer_Query_Result Result set
	 */
	public function execute()
	{
		if ( ! sqlsrv_execute($this->_statement))
			throw new Database_SQLServer_Exception;

		if (sqlsrv_num_fields($this->_statement))
			return new Database_SQLServer_Query_Result($this, $this->_statement, $this->_as_object);

		return NULL;
	}
}
