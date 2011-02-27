<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Insert_Test extends PHPUnit_Framework_TestCase
{
	public function provider_identity()
	{
		return array
		(
			array(NULL, NULL),
			array('a', new SQL_Column('a')),
			array(new SQL_Expression('b'), new SQL_Expression('b')),
			array(new SQL_Identifier('c'), new SQL_Identifier('c')),
		);
	}

	/**
	 * @covers  Database_Insert::identity
	 * @dataProvider    provider_identity
	 *
	 * @param   mixed                       $identity   Argument
	 * @param   SQL_Expression|SQL_Identity $expected   Expected value
	 */
	public function test_identity($identity, $expected)
	{
		$insert = new Database_Insert;

		$this->assertSame($insert, $insert->identity($identity), 'Chainable');
		$this->assertEquals($expected, $insert->identity);
	}
}
