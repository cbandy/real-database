<?php

/**
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Command_Insert_Identity extends Database_Command_Insert
{
	/**
	 * @var mixed   Column to return when executed
	 */
	protected $_return;

	/**
	 * Execute the INSERT on a Database. Returns an array when identity() is set.
	 *
	 * @param   Database_iInsert    $db Connection on which to execute
	 * @return  integer Number of affected rows
	 * @return  array   List including number of affected rows and identity value
	 */
	public function execute($db)
	{
		if ( ! empty($this->_return))
			return $db->execute_insert($db->quote($this));

		return parent::execute($db);
	}

	/**
	 * Name of the IDENTITY column to return when executed
	 *
	 * Behavior varies between database implementations. Reliable only when
	 * inserting one row.
	 *
	 * @param   mixed   $column Converted to Database_Column
	 * @return  $this
	 */
	public function identity($column)
	{
		if ( ! empty($column)
			AND ! $column instanceof Database_Expression
			AND ! $column instanceof Database_Identifier)
		{
			$column = new Database_Column($column);
		}

		$this->_return = $column;

		return $this;
	}
}
