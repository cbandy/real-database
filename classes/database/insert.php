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
