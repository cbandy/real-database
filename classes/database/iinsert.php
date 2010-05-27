<?php

/**
 * @package     RealDatabase
 * @category    Driver Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iInsert
{
	/**
	 * Execute an INSERT statement, returning the number of affected rows and
	 * the identity of one affected row.
	 *
	 * Note: When inserting multiple rows, the identity value may correspond to
	 * _any_ affected row.
	 *
	 * @param   string  $statement  INSERT statement
	 * @return  array   List including number of affected rows and an identity value
	 */
	public function execute_insert($statement);
}
