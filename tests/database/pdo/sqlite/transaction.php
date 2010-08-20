<?php

require_once dirname(dirname(dirname(__FILE__))).'/abstract/transaction'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Transaction_Test extends Database_Abstract_Transaction_Test
{
	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture;

		$db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)');
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
