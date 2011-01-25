<?php

/**
 * @package     RealDatabase
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Select extends SQL_DML_Select
	implements Database_iExecutable, Database_iQuery
{
	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	public function as_object($class = TRUE)
	{
		$this->as_object = $class;

		return $this;
	}

	/**
	 * Execute the query against a Database.
	 *
	 * @throws  Database_Exception
	 * @param   Database    $db Connection on which to execute
	 * @return  Database_Result Result set
	 */
	public function execute($db)
	{
		return $db->execute_query($this, $this->as_object);
	}
}
