<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.datatypes
 */
class Database_Base_Binary_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider    provider_constructor
	 */
	public function test_constructor($value)
	{
		$this->assertSame($value, (string) new Database_Binary($value));
	}

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
}
