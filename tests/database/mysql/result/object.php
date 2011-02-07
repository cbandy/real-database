<?php

require_once dirname(dirname(dirname(__FILE__))).'/abstract/result/object'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Result_Object_Test extends Database_Abstract_Result_Object_Test
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('MySQL extension not installed');

		if ( ! Database::factory() instanceof Database_MySQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for MySQL');
	}

	protected $_table = 'temp_test_table';

	protected function _select_all()
	{
		$db = $this->sharedFixture;

		return $db->execute_query('SELECT * FROM '.$db->quote_table($this->_table).' ORDER BY value', TRUE);
	}

	protected function _select_null()
	{
		$db = $this->sharedFixture;

		return $db->execute_query('SELECT NULL AS value FROM '.$db->quote_table($this->_table), TRUE);
	}

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();
		$table = $db->quote_table($this->_table);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' (value integer)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (50)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (55)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (60)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @covers  Database_MySQL_Result::current
	 */
	public function test_current()
	{
		parent::test_current();
	}
}
