<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Constraint_Primary_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'PRIMARY KEY'),
			array(array(array('a')), 'PRIMARY KEY ("a")'),
			array(array(array('a', 'b')), 'PRIMARY KEY ("a", "b")'),
		);
	}

	/**
	 * @covers  SQL_DDL_Constraint_Primary::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = new SQL;

		$class = new ReflectionClass('SQL_DDL_Constraint_Primary');
		$constraint = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($constraint));
	}

	public function provider_columns()
	{
		return array(
			array(NULL, 'PRIMARY KEY'),

			array(
				array('a'),
				'PRIMARY KEY ("a")',
			),
			array(
				array('a', 'b'),
				'PRIMARY KEY ("a", "b")',
			),

			array(
				array(new SQL_Column('a')),
				'PRIMARY KEY ("a")',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'PRIMARY KEY ("a", "b")',
			),

			array(
				array(new SQL_Expression('a')),
				'PRIMARY KEY (a)',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'PRIMARY KEY (a, b)',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Constraint_Primary::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_columns($value, $expected)
	{
		$db = new SQL;
		$constraint = new SQL_DDL_Constraint_Primary;

		$this->assertSame($constraint, $constraint->columns($value), 'Chainable');
		$this->assertSame($expected, $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Primary::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_columns_reset($value)
	{
		$db = new SQL;
		$constraint = new SQL_DDL_Constraint_Primary;
		$constraint->columns($value);

		$constraint->columns(NULL);

		$this->assertSame('PRIMARY KEY', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Primary::__toString
	 */
	public function test_toString()
	{
		$constraint = new SQL_DDL_Constraint_Primary;
		$constraint
			->name('a')
			->columns(array('b'));

		$this->assertSame('CONSTRAINT :name PRIMARY KEY (:columns)', (string) $constraint);
	}
}
