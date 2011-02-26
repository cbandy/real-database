<?php

/**
 * A statement that returns identity of an inserted row when executed. When
 * identity() is not set, only the number of affected rows is returned.
 *
 * @package     RealDatabase
 * @category    Statement Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iInsert
{
	/**
	 * Set the name of the IDENTITY column to return when executed.
	 *
	 * Behavior varies between database implementations. Reliable only when
	 * inserting one row.
	 *
	 * @param   mixed   $column Converted to Database_Column
	 * @return  $this
	 */
	public function identity($column);
}
