<?php

class Database_Base_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Database_Base_TestSuite;

		Kohana_Tests::addTests($suite, Kohana::list_files('tests/database/base'));

		return $suite;
	}

	protected function setUp()
	{
		$this->sharedFixture = new Database_Base_TestSuite_Database;
	}
}

class Database_Base_TestSuite_Database extends Database
{
	public function __construct($name = NULL, $config = NULL) {}

	public function begin() {}

	public function commit() {}

	public function connect() {}

	public function disconnect() {}

	public function execute_command($statement) {}

	public function execute_query($statement, $as_object = FALSE) {}

	public function rollback() {}

	public function table_prefix()
	{
		return 'pre_';
	}
}
