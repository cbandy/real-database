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
	public static function testsuite_generate_instance_name()
	{
		$config = Kohana::config('database');

		for ($i = 0; $i < 10; ++$i)
		{
			$name = sha1(mt_rand());

			if ( ! isset($config[$name]) AND ! isset(Database::$_instances[$name]))
				return $name;
		}

		return NULL;
	}

	public static function testsuite_unset_instance($name)
	{
		unset(Database::$_instances[$name]);
	}

	public function __construct($name = NULL, $config = NULL)
	{
		if ($name !== NULL)
			parent::__construct($name, $config);
	}

	public function begin() {}

	public function charset($charset) {}

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
