<?php

/**
 * @package     RealDatabase
 * @category    Driver Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iEscape
{
	/**
	 * Quote a value while escaping characters that could cause a SQL injection
	 * attack
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function escape($value);
}