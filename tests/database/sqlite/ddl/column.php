<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_DDL_Column_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('"a" b', $db->quote(new Database_SQLite_DDL_Column('a', 'b')));
	}

	public function test_identity()
	{
		$db = $this->sharedFixture;
		$column = new Database_SQLite_DDL_Column('a');

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" INTEGER NOT NULL PRIMARY KEY', $db->quote($column));
	}

	public function test_identity_prior_constraint()
	{
		$db = $this->sharedFixture;
		$column = new Database_SQLite_DDL_Column('a');
		$column->constraint(new Database_DDL_Constraint_Unique);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" INTEGER NOT NULL UNIQUE PRIMARY KEY', $db->quote($column));
	}

	public function test_identity_prior_primary_key()
	{
		$db = $this->sharedFixture;
		$column = new Database_SQLite_DDL_Column('a');
		$column->constraint(new Database_DDL_Constraint_Primary);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" INTEGER NOT NULL PRIMARY KEY', $db->quote($column));
	}
}
