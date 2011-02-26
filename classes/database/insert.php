<?php

/**
 * An INSERT command which can return the identity of an inserted row when
 * executed.
 *
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Insert extends SQL_DML_Insert
	implements Database_iInsert
{
	/**
	 * @var SQL_Expression|SQL_Identifier Column to return when executed
	 */
	public $identity;

	/**
	 * Execute the INSERT on a Database. Returns an array when identity() is set.
	 *
	 * @throws  Database_Exception
	 * @param   Database    $db Connection on which to execute
	 * @return  integer Number of affected rows
	 * @return  array   List including number of affected rows and identity value
	 */
	public function execute($db)
	{
		if ( ! empty($this->identity))
			return $db->execute_insert($this, $this->identity);

		return $db->execute_command($this);
	}

	public function identity($column)
	{
		if ( ! empty($column)
			AND ! $column instanceof SQL_Expression
			AND ! $column instanceof SQL_Identifier)
		{
			$column = new SQL_Column($column);
		}

		$this->identity = $column;

		return $this;
	}
}
