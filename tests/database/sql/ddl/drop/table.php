<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Drop_Table_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'DROP TABLE '),
			array(array('a'), 'DROP TABLE "pre_a"'),

			array(array('a', FALSE), 'DROP TABLE "pre_a" RESTRICT'),
			array(array('a', TRUE), 'DROP TABLE "pre_a" CASCADE'),
		);
	}

	/**
	 * @covers  SQL_DDL_Drop_Table::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = new SQL('pre_');

		$class = new ReflectionClass('SQL_DDL_Drop_Table');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($statement));
	}

	public function provider_name()
	{
		return array(
			array(NULL, 'DROP TABLE '),
			array('a', 'DROP TABLE "pre_a"'),
			array(new SQL_Identifier('b'), 'DROP TABLE "b"'),
			array(new SQL_Expression('expr'), 'DROP TABLE expr'),
		);
	}

	/**
	 * @covers  SQL_DDL_Drop_Table::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_name($value, $expected)
	{
		$db = new SQL('pre_');
		$statement = new SQL_DDL_Drop_Table;

		$this->assertSame($statement, $statement->name($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Drop_Table::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_name_reset($value)
	{
		$db = new SQL('pre_');
		$statement = new SQL_DDL_Drop_Table;
		$statement->name($value);

		$statement->name(NULL);

		$this->assertSame('DROP TABLE ', $db->quote($statement));
	}

	public function provider_names()
	{
		return array(
			array(NULL, 'DROP TABLE '),

			array(array('a'), 'DROP TABLE "pre_a"'),
			array(array('a', 'b'), 'DROP TABLE "pre_a", "pre_b"'),

			array(
				array(new SQL_Identifier('a')),
				'DROP TABLE "a"',
			),
			array(
				array(new SQL_Identifier('a'), new SQL_Identifier('b')),
				'DROP TABLE "a", "b"',
			),

			array(
				array(new SQL_Table('a')),
				'DROP TABLE "pre_a"',
			),
			array(
				array(new SQL_Table('a'), new SQL_Table('b')),
				'DROP TABLE "pre_a", "pre_b"',
			),

			array(
				array(new SQL_Expression('a')),
				'DROP TABLE a',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'DROP TABLE a, b',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Drop_Table::names
	 *
	 * @dataProvider    provider_names
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_names($value, $expected)
	{
		$db = new SQL('pre_');
		$statement = new SQL_DDL_Drop_Table;

		$this->assertSame($statement, $statement->names($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Drop_Table::names
	 *
	 * @dataProvider    provider_names
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_names_reset($value)
	{
		$db = new SQL('pre_');
		$statement = new SQL_DDL_Drop_Table;
		$statement->names($value);

		$statement->names(NULL);

		$this->assertSame('DROP TABLE ', $db->quote($statement));
	}
}
