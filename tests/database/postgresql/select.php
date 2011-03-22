<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Select_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	public function provider_distinct()
	{
		return array
		(
			array(FALSE, 'SELECT '),
			array(TRUE, 'SELECT DISTINCT '),
			array(array('value'), 'SELECT DISTINCT ON ("value") '),
			array(new SQL_Expression('expr'), 'SELECT DISTINCT ON (expr) '),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Select::distinct
	 * @dataProvider    provider_distinct
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_distinct($value, $expected)
	{
		$db = Database::factory();
		$query = new Database_PostgreSQL_Select;

		$this->assertSame($query, $query->distinct($value), 'Chainable');
		$this->assertSame($expected, $db->quote($query));
	}

	/**
	 * @covers  Database_PostgreSQL_Select::distinct
	 */
	public function test_distinct_void()
	{
		$db = Database::factory();
		$query = new Database_PostgreSQL_Select;

		$this->assertSame($query, $query->distinct(), 'Chainable (void)');
		$this->assertSame('SELECT DISTINCT ', $db->quote($query), 'Distinct (void)');
	}
}
