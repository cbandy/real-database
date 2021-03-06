<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database_DDL_Column extends SQL_DDL_Column
{
	/**
	 * Enable IDENTITY behavior on the column. Adds a PRIMARY KEY constraint and causes the database
	 * server to generate a unique default value for each inserted row. Typically requires the
	 * column to have INTEGER or BIGINT datatype.
	 *
	 * @return  $this
	 */
	abstract public function identity();
}
