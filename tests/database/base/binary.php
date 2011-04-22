<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.literals
 */
class Database_Base_Binary_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array
		(
			array(''),
			array('asdf'),

			array("\0"),
			array("\0\0\0\0\0\0\0\0"),
		);
	}

	/**
	 * @covers  Database_Binary::__construct
	 * @covers  Database_Binary::__toString
	 * @dataProvider    provider_constructor
	 */
	public function test_constructor($value)
	{
		$this->assertSame($value, (string) new Database_Binary($value));
	}
}
