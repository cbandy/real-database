<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Create_View_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('MySQL extension not installed');

		if ( ! Database::factory() instanceof Database_MySQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for MySQL');
	}

	/**
	 * @covers  Database_MySQL_Create_View::algorithm
	 */
	public function test_algorithm()
	{
		$db = Database::factory();
		$command = new Database_MySQL_Create_View('a', new Database_Query('b'));
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->algorithm('merge'));
		$this->assertSame("CREATE ALGORITHM = MERGE VIEW $table AS b", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_Create_View::check
	 */
	public function test_check()
	{
		$db = Database::factory();
		$command = new Database_MySQL_Create_View('a', new Database_Query('b'));
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->check('cascaded'));
		$this->assertSame("CREATE VIEW $table AS b WITH CASCADED CHECK OPTION", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_Create_View::__toString
	 */
	public function test_toString()
	{
		$command = new Database_MySQL_Create_View;
		$command
			->replace()
			->algorithm('a')
			->columns(array('b'))
			->check('c');

		$this->assertSame('CREATE OR REPLACE ALGORITHM = A VIEW :name (:columns) AS :query WITH C CHECK OPTION', (string) $command);
	}
}
