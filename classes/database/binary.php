<?php

/**
 * A literal that is treated as binary when escaped.
 *
 * @package     RealDatabase
 * @category    Data Types
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Binary
{
	/**
	 * @var mixed
	 */
	protected $_value;

	/**
	 * @param   mixed   $value
	 */
	public function __construct($value)
	{
		$this->_value = $value;
	}

	public function __toString()
	{
		return (string) $this->_value;
	}
}
