<?php

/**
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Command extends Database_Expression
{
	/**
	 * Execute the command on a Database
	 *
	 * @param   Database    $db Connection on which to execute
	 * @return  integer Number of affected rows
	 */
	public function execute($db)
	{
		if ($db instanceof Database_Escape)
			return $db->execute_command($db->quote($this));

		return $this->prepare($db)->execute();
	}

	/**
	 * Prepare the command to be executed on a Database
	 *
	 * @param   Database    $db Connection on which to prepare (and later execute)
	 * @return  Database_Prepared_Command
	 */
	public function prepare($db)
	{
		return $db->prepare_command($this->__toString(), $this->parameters);
	}
}
