<?php
/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Identical_Test extends PHPUnit_Framework_TestCase
{
	public function provider_toString()
	{
		return array(
			array('a', '=', 'b', ':left IS NOT DISTINCT FROM :right'),
			array('a', '<>', 'b', ':left IS DISTINCT FROM :right'),
			array('a', '!=', 'b', ':left IS DISTINCT FROM :right'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Identical::__toString
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
		$expression = new Database_PostgreSQL_Identical($left, $operator, $right);

		$this->assertSame($expected, (string) $expression);
	}
}
