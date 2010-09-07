<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
class Database_PostgreSQL_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Database_PostgreSQL_TestSuite;

		Kohana_Tests::addTests($suite, Kohana::list_files('tests/database/postgresql'));

		return $suite;
	}

	protected function setUp()
	{
		if ( ! extension_loaded('pgsql'))
			$this->markTestSuiteSkipped('PostgreSQL extension not installed');

		if ( ! $name = Kohana::config('unittest')->db_connection
			OR ! $config = Kohana::config('database')->get($name)
			OR $config['type'] !== 'PostgreSQL')
		{
			$this->markTestSuiteSkipped('Database not configured for PostgreSQL');
		}

		$this->sharedFixture = Database::instance($name);
	}
}
