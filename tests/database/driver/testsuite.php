<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
class Database_Driver_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Database_Driver_TestSuite;

		$name = Kohana::config('unittest')->db_connection;

		if ($config = Kohana::config('database')->$name)
		{
			Kohana_Tests::addTests($suite, Kohana::list_files('tests/database/driver'));

			$suite->addTestFile('tests/database/'.str_replace('_', '/', strtolower($config['type'])).'/testsuite.php');
		}

		return $suite;
	}

	protected function setUp()
	{
		$name = Kohana::config('unittest')->db_connection;

		if ( ! Kohana::config('database')->get($name))
			$this->markTestSuiteSkipped('No test connection configured');

		$this->sharedFixture = Database::instance($name);
	}
}
