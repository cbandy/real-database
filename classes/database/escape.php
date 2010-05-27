<?php

/**
 * @package     RealDatabase
 * @category    Driver Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database_Escape extends Database
{
	/**
	 * Quote a value while escaping characters that could cause a SQL injection
	 * attack.
	 *
	 * @param   mixed   Value to quote
	 * @return  string
	 */
	abstract public function escape($value);

	/**
	 * Quote a literal value for inclusion in a SQL query.
	 *
	 * @uses Database::escape()
	 *
	 * @param   mixed   Value to quote
	 * @return  string
	 */
	public function quote_literal($value)
	{
		if (is_object($value) OR is_string($value))
			return $this->escape($value);

		return parent::quote_literal($value);
	}
}
