<?php

/**
 * A statement that returns a result set only when executed with returning()
 * set. When returning() is not set, the number of affected rows is returned.
 *
 * @package     RealDatabase
 * @category    Statement Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iReturning
{
	/**
	 * Return affected rows as associative arrays when executed.
	 *
	 * @return  $this
	 */
	public function as_assoc();

	/**
	 * Set the class as which to return affected rows when executed.
	 *
	 * @param   string|boolean  $class  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @return  $this
	 */
	public function as_object($class = TRUE);

	/**
	 * Append multiple columns or expressions to be returned from the affected
	 * rows when executed.
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 * @return  $this
	 */
	public function returning($columns);
}
