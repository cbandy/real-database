<?php

/**
 * @package     RealDatabase
 * @category    Driver Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iMultiple
{
	/**
	 * Execute a SQL statement or compound statement with multiple results.
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL statement(s)
	 * @param   mixed   $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 * @return  Database_Result_Iterator    Forward-only iterator over the results
	 */
	public function execute_multiple($statement, $as_object = FALSE);
}
