<?php

/**
 * @package     RealDatabase
 * @category    Statement Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iExecutable
{
	/**
	 * Execute the statement on a Database.
	 *
	 * @throws  Database_Exception
	 * @param   Database    $db Connection on which to execute
	 * @return  mixed
	 */
	public function execute($db);
}
