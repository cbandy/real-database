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

		Kohana_Tests::addTests($suite, Kohana::list_files('tests/database/pdo/sqlite'));

		return $suite;
	}

	protected function setUp()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			$this->markTestSuiteSkipped('PDO SQLite extension not installed');
	}
}
