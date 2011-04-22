<?php

/**
 * A DateTime literal that is converted to the appropriate format when escaped.
 *
 * @package     RealDatabase
 * @category    Literals
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_DateTime extends DateTime
{
	/**
	 * Format string for SQL-92 TIMESTAMP WITH TIME ZONE
	 */
	const SQL = 'Y-m-d H:i:sP';

	/**
	 * @var string  Format used to convert this object to string
	 */
	protected $_format;

	/**
	 * @param   integer|string      $time       Unix timestamp or time in a format accepted by strtotime()
	 * @param   string|DateTimeZone $timezone   Fallback timezone, converted to DateTimeZone
	 * @param   string              $format     Format accepted by date(), defaults to Database_DateTime::SQL
	 */
	public function __construct($time = 'now', $timezone = NULL, $format = Database_DateTime::SQL)
	{
		if (is_int($time))
		{
			$time = '@'.$time;
		}

		if ( ! $timezone)
		{
			// @link http://bugs.php.net/52063 Fixed in PHP 5.3.6
			parent::__construct($time);
		}
		else
		{
			if ( ! $timezone instanceof DateTimeZone)
			{
				$timezone = new DateTimeZone($timezone);
			}

			parent::__construct($time, $timezone);
		}

		$this->_format = $format;
	}

	public function __toString()
	{
		return $this->format($this->_format);
	}

	/**
	 * Resets the format used to convert this object to string
	 *
	 * @param   string  $format Format accepted by date()
	 * @return  $this
	 */
	public function setFormat($format)
	{
		$this->_format = $format;

		return $this;
	}

	/**
	 * Resets the display timezone
	 *
	 * @link http://php.net/manual/datetime.settimezone
	 *
	 * @param   string|DateTimeZone $timezone   Converted to DateTimeZone
	 * @return  $this
	 */
	public function setTimezone($timezone)
	{
		if ( ! $timezone instanceof DateTimeZone)
		{
			$timezone = new DateTimeZone($timezone);
		}

		parent::setTimezone($timezone);

		return $this;
	}
}
