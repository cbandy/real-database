<?php

/**
 * Null-safe equality comparator for PostgreSQL.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/functions-comparison.html
 */
class Database_PostgreSQL_Identical extends SQL_Identical
{
	public function __toString()
	{
		return ($this->_value === '=')
			? ':left IS NOT DISTINCT FROM :right'
			: ':left IS DISTINCT FROM :right';
	}
}
