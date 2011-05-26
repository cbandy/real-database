<?php
/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_Identical_Test extends PHPUnit_Framework_TestCase
{
	public function provider_toString()
	{
		return array(
			array('a', '=', 'b', ':left IS :right'),
			array('a', '<>', 'b', ':left IS NOT :right'),
			array('a', '!=', 'b', ':left IS NOT :right'),
		);
	}

	/**
	 * @covers  Database_SQLite_Identical::__toString
	 *
	 * @dataProvider    provider_toString
	 *
	 * @param   mixed   $left       First argument to the constructor
	 * @param   string  $operator   Second argument to the constructor
	 * @param   mixed   $right      Third argument to the constructor
	 * @param   string  $expected
	 */
	public function test_toString($left, $operator, $right, $expected)
	{
		$expression = new Database_SQLite_Identical($left, $operator, $right);

		$this->assertSame($expected, (string) $expression);
	}
}
