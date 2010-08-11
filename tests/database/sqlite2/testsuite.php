<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
class Database_SQLite2_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Database_SQLite2_TestSuite;

		Kohana_Tests::addTests($suite, Kohana::list_files('tests/database/sqlite2'));

		return $suite;
	}

	protected function setUp()
	{
		if ( ! extension_loaded('SQLite'))
			$this->markTestSuiteSkipped('SQLite extension not installed');

		$name = Kohana::config('unittest')->db_connection;
		$config = Kohana::config('database')->$name;

		if ($config['type'] !== 'SQLite2')
			$this->markTestSuiteSkipped('Database not configured for SQLite2');

		$this->sharedFixture = Database::instance($name);
	}
}
