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
class Database_SQLServer_Query_Result extends Database_SQLServer_Result
{
	/**
	 * @var Database_SQLServer_Query
	 */
	protected $_query;

	/**
	 * @param   Database_SQLServer_Query    $query
	 * @param   resource    $statement
	 * @param   mixed       $as_object
	 */
	public function __construct($query, $statement, $as_object)
	{
		parent::__construct($statement, $as_object);

		$this->_query = $query;
	}
}
