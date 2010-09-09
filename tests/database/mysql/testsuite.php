<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
class Database_MySQL_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Database_MySQL_TestSuite;

		Kohana_Tests::addTests($suite, Kohana::list_files('tests/database/mysql'));

		return $suite;
	}

	protected function setUp()
	{
		if ( ! extension_loaded('mysql'))
			$this->markTestSuiteSkipped('MySQL extension not installed');

		if ( ! $name = Kohana::config('unittest')->db_connection
			OR ! $config = Kohana::config('database')->get($name)
			OR $config['type'] !== 'MySQL')
		{
			$this->markTestSuiteSkipped('Database not configured for MySQL');
		}

		$this->sharedFixture = Database::instance($name);
	}
}