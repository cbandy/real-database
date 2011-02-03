<?php

require_once dirname(dirname(__FILE__)).'/abstract/transaction'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Transaction_Test extends Database_Abstract_Transaction_Test
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('MySQL extension not installed');

		if ( ! Database::factory() instanceof Database_MySQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for MySQL');
	}

	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();

		// Ensure the storage engine supports transactions
		$db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer) ENGINE = InnoDB');
		$db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_command_provider()
	{
		return array
		(
			array('SELECT * FROM '.$this->_table, 'INSERT INTO '.$this->_table.' (value) VALUES (100)', array(array('value' => 50), array('value' => 100))),
			array('SELECT * FROM '.$this->_table, 'DELETE FROM '.$this->_table.' WHERE VALUE = 50', array()),
		);
	}
}
