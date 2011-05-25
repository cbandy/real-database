<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Constraint_Unique_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'UNIQUE'),
			array(array(array('a')), 'UNIQUE ("a")'),
			array(array(array('a', 'b')), 'UNIQUE ("a", "b")'),
		);
	}

	/**
	 * @covers  SQL_DDL_Constraint_Unique::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = new SQL;

		$class = new ReflectionClass('SQL_DDL_Constraint_Unique');
		$constraint = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($constraint));
	}

	public function provider_columns()
	{
		return array(
			array(NULL, 'UNIQUE'),

			array(
				array('a'),
				'UNIQUE ("a")',
			),
			array(
				array('a', 'b'),
				'UNIQUE ("a", "b")',
			),

			array(
				array(new SQL_Column('a')),
				'UNIQUE ("a")',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'UNIQUE ("a", "b")',
			),

			array(
				array(new SQL_Expression('a')),
				'UNIQUE (a)',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'UNIQUE (a, b)',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Constraint_Unique::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_columns($value, $expected)
	{
		$db = new SQL;
		$constraint = new SQL_DDL_Constraint_Unique;

		$this->assertSame($constraint, $constraint->columns($value), 'Chainable');
		$this->assertSame($expected, $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Unique::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_columns_reset($value)
	{
		$db = new SQL;
		$constraint = new SQL_DDL_Constraint_Unique;
		$constraint->columns($value);

		$constraint->columns(NULL);

		$this->assertSame('UNIQUE', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Unique::__toString
	 */
	public function test_toString()
	{
		$constraint = new SQL_DDL_Constraint_Unique;
		$constraint
			->name('a')
			->columns(array('b'));

		$this->assertSame('CONSTRAINT :name UNIQUE (:columns)', (string) $constraint);
	}
}
