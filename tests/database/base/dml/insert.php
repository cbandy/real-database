<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_DML_Insert_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_DML_Insert::as_assoc
	 */
	public function test_as_assoc()
	{
		$statement = new Database_DML_Insert;

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
	 * @covers  Database_DML_Insert::as_object
	 *
	 * @dataProvider    provider_as_object
	 *
	 * @param   array           $arguments  Arguments to the method
	 * @param   string|boolean  $as_object  Expected $as_object value
	 * @param   array           $expected   Expected $arguments value
	 */
	public function test_as_object($arguments, $as_object, $expected)
	{
		$statement = new Database_DML_Insert;

		$this->assertSame($statement, call_user_func_array(array($statement, 'as_object'), $arguments), 'Chainable');
		$this->assertSame($as_object, $statement->as_object);
		$this->assertSame($expected, $statement->arguments);
	}

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
	 * @covers  Database_DML_Insert::identity
	 *
	 * @dataProvider    provider_identity
	 *
	 * @param   mixed                       $identity   Argument
	 * @param   SQL_Expression|SQL_Identity $expected   Expected value
	 */
	public function test_identity($identity, $expected)
	{
		$insert = new Database_DML_Insert;

		$this->assertSame($insert, $insert->identity($identity), 'Chainable');
		$this->assertEquals($expected, $insert->identity);
		$this->assertNull($insert->returning);
	}

	/**
	 * @covers  Database_DML_Insert::identity
	 *
	 * @dataProvider    provider_identity
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_identity_reset($value)
	{
		$statement = new Database_DML_Insert;
		$statement->identity($value);

		$this->assertSame($statement, $statement->identity(NULL), 'Chainable');
		$this->assertNull($statement->identity);
	}

	public function provider_identity_returning()
	{
		return array
		(
			array(NULL, NULL, NULL, NULL),
			array(NULL, array('a'), NULL, array(new SQL_Column('a'))),

			array('a', NULL, new SQL_Column('a'), NULL),
			array('a', array('b'), new SQL_Column('a'), NULL),
		);
	}

	/**
	 * @covers  Database_DML_Insert::identity
	 *
	 * @dataProvider    provider_identity_returning
	 *
	 * @param   mixed   $identity           Argument to identity()
	 * @param   mixed   $returning          Argument to returning()
	 * @param   mixed   $expected_identity
	 * @param   mixed   $expected_returning
	 */
	public function test_identity_returning($identity, $returning, $expected_identity, $expected_returning)
	{
		$statement = new Database_DML_Insert;
		$statement->returning($returning);

		$this->assertSame($statement, $statement->identity($identity), 'Chainable');
		$this->assertEquals($expected_identity, $statement->identity);
		$this->assertEquals($expected_returning, $statement->returning);
	}

	public function provider_returning()
	{
		return array
		(
			array(NULL, NULL),

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
	 * @covers  Database_DML_Insert::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value      Argument
	 * @param   mixed   $expected
	 */
	public function test_returning($value, $expected)
	{
		$statement = new Database_DML_Insert;

		$this->assertSame($statement, $statement->returning($value), 'Chainable');
		$this->assertEquals($expected, $statement->returning);
		$this->assertNull($statement->identity);
	}

	public function provider_returning_identity()
	{
		return array
		(
			array(NULL, NULL, NULL, NULL),
			array(NULL, 'a', NULL, new SQL_Column('a')),

			array(array('a'), NULL, array(new SQL_Column('a')), NULL),
			array(array('a'), 'b', array(new SQL_Column('a')), NULL),
		);
	}

	/**
	 * @covers  Database_DML_Insert::returning
	 *
	 * @dataProvider    provider_returning_identity
	 *
	 * @param   mixed   $returning          Argument to returning()
	 * @param   mixed   $identity           Argument to identity()
	 * @param   mixed   $expected_returning
	 * @param   mixed   $expected_identity
	 */
	public function test_returning_identity($returning, $identity, $expected_returning, $expected_identity)
	{
		$statement = new Database_DML_Insert;
		$statement->identity($identity);

		$this->assertSame($statement, $statement->returning($returning), 'Chainable');
		$this->assertEquals($expected_returning, $statement->returning);
		$this->assertEquals($expected_identity, $statement->identity);
	}

	/**
	 * @covers  Database_DML_Insert::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_returning_reset($value)
	{
		$statement = new Database_DML_Insert;
		$statement->returning($value);

		$this->assertSame($statement, $statement->returning(NULL), 'Chainable');
		$this->assertNull($statement->returning);
	}
}
