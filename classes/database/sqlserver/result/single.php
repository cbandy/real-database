<?php

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLServer_Result_Single extends Database_SQLServer_Result
{
	public function __destruct()
	{
		sqlsrv_free_stmt($this->_statement);
	}
}
