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

		$name = Kohana::config('unittest')->db_connection;
		$config = Kohana::config('database')->$name;

		if ($config['type'] !== 'PostgreSQL')
			$this->markTestSuiteSkipped('Database not configured for PostgreSQL');

		$this->sharedFixture = Database::instance($name);
	}
}
