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
	public function test_constructor()
	{
		$this->assertSame('2009-11-17 12:34:56+00:00', (string) new Database_DateTime(1258461296));

		$this->assertSame('2009-11-17 00:00:00'.date('P', 1258461296), (string) new Database_DateTime('2009-11-17'));
		$this->assertSame('2009-11-17 00:00:00+00:00', (string) new Database_DateTime('2009-11-17', 'UTC'));
		$this->assertSame('2009-11-17 00:00:00+00:00', (string) new Database_DateTime('2009-11-17', new DateTimeZone('UTC')));

		$this->assertSame('2009-11-17 12:34:56-06:00', (string) new Database_DateTime('2009-11-17T12:34:56', 'US/Central'));

		$this->assertSame('2009-11-17', (string) new Database_DateTime('2009-11-17T12:34:56', 'Asia/Shanghai', 'Y-m-d'));
	}

	public function test_setFormat()
	{
		$dt = new Database_DateTime('2009-11-17T12:34:56');

		$this->assertSame($dt, $dt->setFormat('Y-m-d'));
		$this->assertSame('2009-11-17', (string) $dt);

		$this->assertSame('12:34:56', (string) $dt->setFormat('H:i:s'));
	}

	public function test_setTimezone()
	{
		$dt = new Database_DateTime('2009-11-17T12:34:56+00');

		$this->assertSame($dt, $dt->setTimezone('US/Central'), 'string');
		$this->assertSame('2009-11-17 06:34:56-06:00', (string) $dt);

		$this->assertSame($dt, $dt->setTimezone(new DateTimeZone('UTC')), 'DateTimeZone');
		$this->assertSame('2009-11-17 12:34:56+00:00', (string) $dt);
	}
}
