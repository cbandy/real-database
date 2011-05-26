<?php

/**
 * Null-safe equality comparator for MySQL.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/comparison-operators.html#operator_equal-to
 */
class Database_MySQL_Identical extends SQL_Identical
{
	public function __toString()
	{
		return ($this->_value === '=')
			? ':left <=> :right'
			: 'NOT (:left <=> :right)';
	}
}
