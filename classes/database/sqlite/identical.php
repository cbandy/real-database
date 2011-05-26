<?php

/**
 * Null-safe equality comparator for SQLite.
 *
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_expr.html#binaryops
 */
class Database_SQLite_Identical extends SQL_Identical
{
	public function __toString()
	{
		return ($this->_value === '=')
			? ':left IS :right'
			: ':left IS NOT :right';
	}
}
