<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.sqlserver
 */
class Database_SQLServer_DDL_Column_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLServer_DDL_Column::identity
	 */
	public function test_identity_void()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => array('[', ']'),
		)));
		$column = new Database_SQLServer_DDL_Column('a', 'b');

		$this->assertSame($column, $column->identity(), 'Chainable');
		$this->assertSame('[a] b IDENTITY (1, 1) PRIMARY KEY', $db->quote($column));
	}

	public function provider_identity_one()
	{
		return array
		(
			array(1, '[a] b IDENTITY (1, 1) PRIMARY KEY'),
			array(5, '[a] b IDENTITY (5, 1) PRIMARY KEY'),
			array(30, '[a] b IDENTITY (30, 1) PRIMARY KEY'),
		);
	}

	/**
	 * @covers  Database_SQLServer_DDL_Column::identity
	 *
	 * @dataProvider    provider_identity_one
	 *
	 * @param   integer $value      Argument
	 * @param   string  $expected
	 */
	public function test_identity_one($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => array('[', ']'),
		)));
		$column = new Database_SQLServer_DDL_Column('a', 'b');

		$this->assertSame($column, $column->identity($value), 'Chainable');
		$this->assertSame($expected, $db->quote($column));
	}

	public function provider_identity_two()
	{
		return array
		(
			array(1, 1, '[a] b IDENTITY (1, 1) PRIMARY KEY'),
			array(1, 5, '[a] b IDENTITY (1, 5) PRIMARY KEY'),
			array(7, 3, '[a] b IDENTITY (7, 3) PRIMARY KEY'),
			array(9, 1, '[a] b IDENTITY (9, 1) PRIMARY KEY'),
		);
	}

	/**
	 * @covers  Database_SQLServer_DDL_Column::identity
	 *
	 * @dataProvider    provider_identity_two
	 *
	 * @param   integer $first      First argument
	 * @param   integer $second     Second argument
	 * @param   sring   $expected
	 */
	public function test_identity_two($first, $second, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => array('[', ']'),
		)));
		$column = new Database_SQLServer_DDL_Column('a', 'b');

		$this->assertSame($column, $column->identity($first, $second), 'Chainable');
		$this->assertSame($expected, $db->quote($column));
	}

	public function provider_identity_with_constraint()
	{
		return array
		(
			array(1, 1, new SQL_DDL_Constraint_Primary, '[a] b IDENTITY (1, 1) PRIMARY KEY'),
			array(1, 1, new SQL_DDL_Constraint_Unique, '[a] b IDENTITY (1, 1) UNIQUE PRIMARY KEY'),

			array(7, 3, new SQL_DDL_Constraint_Primary, '[a] b IDENTITY (7, 3) PRIMARY KEY'),
			array(7, 3, new SQL_DDL_Constraint_Unique, '[a] b IDENTITY (7, 3) UNIQUE PRIMARY KEY'),
		);
	}

	/**
	 * @covers  Database_SQLServer_DDL_Column::identity
	 *
	 * @dataProvider    provider_identity_with_constraint
	 *
	 * @param   integer $first      First argument
	 * @param   integer $second     Second argument
	 * @param   mixed   $constraint
	 * @param   sring   $expected
	 */
	public function test_identity_with_constraint($first, $second, $constraint, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => array('[', ']'),
		)));
		$column = new Database_SQLServer_DDL_Column('a', 'b');

		$column->constraint($constraint);

		$this->assertSame($column, $column->identity($first, $second), 'Chainable');
		$this->assertSame($expected, $db->quote($column));
	}

	/**
	 * @covers  Database_SQLServer_DDL_Column::__toString
	 */
	public function test_toString()
	{
		$column = new Database_SQLServer_DDL_Column;
		$column
			->name('a')
			->type('b')
			->not_null()
			->set_default(NULL);

		$this->assertSame(':name :type NOT NULL DEFAULT :default', (string) $column);

		$column
			->identity();

		$this->assertSame(':name :type NOT NULL IDENTITY (:identity) :constraints', (string) $column);
	}
}
