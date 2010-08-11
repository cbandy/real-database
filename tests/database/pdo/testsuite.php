<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
class Database_PDO_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Database_PDO_TestSuite;

		$suite->addTestFile('tests/database/pdo/database'.EXT);

		return $suite;
	}

	protected function setUp()
	{
		if ( ! extension_loaded('pdo'))
			$this->markTestSuiteSkipped('PDO extension not installed');

		$name = Kohana::config('unittest')->db_connection;
		$config = Kohana::config('database')->$name;

		if (strncmp($config['type'], 'PDO', 3))
			$this->markTestSuiteSkipped('Database not configured for PDO');

		$this->sharedFixture = Database::instance($name);
	}
}
