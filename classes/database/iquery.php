<?php

/**
 * A statement that returns a result set when executed.
 *
 * @package     RealDatabase
 * @category    Statement Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iQuery
{
	/**
	 * Return rows as associative arrays when executed.
	 *
	 * @return  $this
	 */
	public function as_assoc();

	/**
	 * Set the class as which to return rows when executed.
	 *
	 * @param   string|boolean  $class      Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the class constructor
	 * @return  $this
	 */
	public function as_object($class = TRUE, $arguments = array());
}
