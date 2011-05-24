<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Update_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Update::as_assoc
	 */
	public function test_as_assoc()
	{
		$statement = new Database_Update;

		$this->assertSame($statement, $statement->as_assoc(), 'Chainable');
		$this->assertSame(FALSE, $statement->as_object);
	}

	public function provider_as_object()
	{
		return array
		(
			array(array(FALSE), FALSE, array()),
			array(array(TRUE), TRUE, array()),
			array(array('b'), 'b', array()),
			array(array('b', array('c')), 'b', array('c')),
		);
	}

	/**
	 * @covers  Database_Update::as_object
	 *
	 * @dataProvider    provider_as_object
	 *
	 * @param   array           $arguments  Arguments to the method
	 * @param   string|boolean  $as_object  Expected $as_object value
	 * @param   array           $expected   Expected $arguments value
	 */
	public function test_as_object($arguments, $as_object, $expected)
	{
		$statement = new Database_Update;

		$this->assertSame($statement, call_user_func_array(array($statement, 'as_object'), $arguments), 'Chainable');
		$this->assertSame($as_object, $statement->as_object);
		$this->assertSame($expected, $statement->arguments);
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

			array(
				array(new SQL_Expression('a')),
				array(new SQL_Expression('a')),
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				array(new SQL_Expression('a'), new SQL_Expression('b')),
			),
		);
	}

	/**
	 * @covers  Database_Update::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value      Argument
	 * @param   mixed   $expected
	 */
	public function test_returning($value, $expected)
	{
		$statement = new Database_Update;

		$this->assertSame($statement, $statement->returning($value), 'Chainable');
		$this->assertEquals($expected, $statement->returning);
	}
}
