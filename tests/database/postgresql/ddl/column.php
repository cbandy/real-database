<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_DDL_Column_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity()
	{
		$db = Database::factory();
		$column = new Database_PostgreSQL_DDL_Column('a');

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" SERIAL PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity_bigint()
	{
		$db = Database::factory();
		$column = new Database_PostgreSQL_DDL_Column('a', 'bigint');

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" BIGSERIAL PRIMARY KEY', $db->quote($column));

		// Repeated call should stay the same
		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" BIGSERIAL PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity_prior_constraint()
	{
		$db = Database::factory();
		$column = new Database_PostgreSQL_DDL_Column('a');
		$column->constraint(new SQL_DDL_Constraint_Unique);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" SERIAL UNIQUE PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity_prior_primary_key()
	{
		$db = Database::factory();
		$column = new Database_PostgreSQL_DDL_Column('a');
		$column->constraint(new SQL_DDL_Constraint_Primary);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" SERIAL PRIMARY KEY', $db->quote($column));
	}
}
