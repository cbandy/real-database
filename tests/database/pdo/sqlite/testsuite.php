<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
class Database_PDO_SQLite_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Database_PDO_SQLite_TestSuite;

		$suite->addTestFile(dirname(dirname(__FILE__)).'/testsuite'.EXT);

		Kohana_Tests::addTests($suite, Kohana::list_files('tests/database/pdo/sqlite'));

		return $suite;
	}

	protected function setUp()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			$this->markTestSuiteSkipped('PDO SQLite extension not installed');

		$name = Kohana::config('unittest')->db_connection;
		$config = Kohana::config('database')->$name;

		if ($config['type'] !== 'PDO_SQLite')
			$this->markTestSuiteSkipped('Database not configured for SQLite using PDO');

		$this->sharedFixture = Database::instance($name);
	}
}
