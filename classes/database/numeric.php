<?php

/**
 * @package     RealDatabase
 * @category    Data Types
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Numeric
{
	/**
	 * @var string  Format used to convert this object to string
	 */
	public $format;

	/**
	 * @var mixed
	 */
	public $value;

	/**
	 * @param   mixed   $value
	 * @param   integer $scale  Number of digits in the fractional part
	 */
	public function __construct($value, $scale)
	{
		$this->format = '%.'.$scale.'F';
		$this->value = $value;
	}

	/**
	 * @uses sprintf()
	 */
	public function __toString()
	{
		return sprintf($this->format, $this->value);
	}
}
