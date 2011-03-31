<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Delete_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Delete::as_assoc
	 */
	public function test_as_assoc()
	{
		$statement = new Database_Delete;

		$this->assertSame($statement, $statement->as_assoc(), 'Chainable');
		$this->assertSame(FALSE, $statement->as_object);
	}

	public function provider_as_object()
	{
		return array
		(
			array(FALSE),
			array(TRUE),
			array('b'),
		);
	}

	/**
	 * @covers  Database_Delete::as_object
	 *
	 * @dataProvider    provider_as_object
	 *
	 * @param   string|boolean  $as_object  Expected value
	 */
	public function test_as_object($as_object)
	{
		$statement = new Database_Delete;

		$this->assertSame($statement, $statement->as_object($as_object), 'Chainable');
		$this->assertSame($as_object, $statement->as_object);
	}

	public function provider_returning()
	{
		return array
		(
			array(NULL, array()),

			array(array('a'), array(new SQL_Column('a'))),
			array(
				array('a', 'b'),
				array(new SQL_Column('a'), new SQL_Column('b')),
			),

			array(array(new SQL_Column('a')), array(new SQL_Column('a'))),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				array(new SQL_Column('a'), new SQL_Column('b')),
			),

			array(new SQL_Expression('expr'), new SQL_Expression('expr')),
		);
	}

	/**
	 * @covers  Database_Delete::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value      Argument
	 * @param   mixed   $expected
	 */
	public function test_returning($value, $expected)
	{
		$statement = new Database_Delete;

		$this->assertSame($statement, $statement->returning($value), 'Chainable');
		$this->assertEquals($expected, $statement->returning);
	}
}
