<?php
/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_DDL_Column_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLite_DDL_Column::identity
	 */
	public function test_identity()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new Database_SQLite_DDL_Column('a');

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" INTEGER NOT NULL PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_SQLite_DDL_Column::identity
	 */
	public function test_identity_prior_constraint()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new Database_SQLite_DDL_Column('a');
		$column->constraint(new SQL_DDL_Constraint_Unique);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" INTEGER NOT NULL UNIQUE PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_SQLite_DDL_Column::identity
	 */
	public function test_identity_prior_primary_key()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new Database_SQLite_DDL_Column('a');
		$column->constraint(new SQL_DDL_Constraint_Primary);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" INTEGER NOT NULL PRIMARY KEY', $db->quote($column));
	}
}
