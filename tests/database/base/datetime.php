<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.datatypes
 */
class Database_Base_DateTime_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array
		(
			// Unix timestamp
			array(array(1258461296),    '2009-11-17 12:34:56+00:00'),
			array(array('@1258461296'), '2009-11-17 12:34:56+00:00'),

			// Date no timezone
			array(array('2009-11-17'),  '2009-11-17 00:00:00'.date('P', 1258461296)),

			// Date with timezone
			array(array('2009-11-17', 'UTC'),                   '2009-11-17 00:00:00+00:00'),
			array(array('2009-11-17', new DateTimeZone('UTC')), '2009-11-17 00:00:00+00:00'),

			// Timestamp with timezone
			array(array('2009-11-17T12:34:56', 'US/Central'),   '2009-11-17 12:34:56-06:00'),

			// Timestamp with format
			array(array('2009-11-17T12:34:56', NULL, 'Y-m-d'),              '2009-11-17'),
			array(array('2009-11-17T12:34:56', 'Asia/Shanghai', 'Y-m-d'),   '2009-11-17'),
		);
	}

	/**
	 * @covers  Database_DateTime::__construct
	 * @dataProvider  provider_constructor
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $expected   Expected value
	 */
	public function test_constructor($arguments, $expected)
	{
		if (count($arguments) === 1)
		{
			$datetime = new Database_DateTime(reset($arguments));
		}
		elseif (count($arguments) === 2)
		{
			$datetime = new Database_DateTime(reset($arguments), next($arguments));
		}
		elseif (count($arguments) === 3)
		{
			$datetime = new Database_DateTime(reset($arguments), next($arguments), next($arguments));
		}

		$this->assertSame($expected, (string) $datetime);
	}

	/**
	 * @covers  Database_DateTime::setFormat
	 * @covers  Database_DateTime::__toString
	 */
	public function test_setFormat()
	{
		$datetime = new Database_DateTime('2009-11-17T12:34:56');

		$this->assertSame($datetime, $datetime->setFormat('Y-m-d'), 'Chainable');
		$this->assertSame('2009-11-17', (string) $datetime);

		$this->assertSame('12:34:56', (string) $datetime->setFormat('H:i:s'));
	}

	/**
	 * @covers  Database_DateTime::setTimezone
	 */
	public function test_setTimezone()
	{
		$datetime = new Database_DateTime('2009-11-17T12:34:56+00');

		$this->assertSame($datetime, $datetime->setTimezone('US/Central'), 'string');
		$this->assertSame('2009-11-17 06:34:56-06:00', (string) $datetime);

		$this->assertSame($datetime, $datetime->setTimezone(new DateTimeZone('UTC')), 'DateTimeZone');
		$this->assertSame('2009-11-17 12:34:56+00:00', (string) $datetime);
	}
}
