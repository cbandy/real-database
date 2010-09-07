<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
class Database_PDO_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$dir = dirname(__FILE__);
		$suite = new Database_PDO_TestSuite;

		$suite->addTestFile($dir.'/database'.EXT);

		return $suite;
	}

	protected function setUp()
	{
		if ( ! extension_loaded('pdo'))
			$this->markTestSuiteSkipped('PDO extension not installed');

		if ( ! $name = Kohana::config('unittest')->db_connection
			OR ! $config = Kohana::config('database')->get($name)
			OR strncmp($config['type'], 'PDO', 3) !== 0)
		{
			$this->markTestSuiteSkipped('Database not configured for PDO');
		}

		$this->sharedFixture = Database::instance($name);
	}
}
