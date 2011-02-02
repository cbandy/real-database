<?php

require_once dirname(dirname(dirname(__FILE__))).'/abstract/result/assoc'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Result_Assoc_Test extends Database_Abstract_Result_Assoc_Test
{
	protected $_table = 'temp_test_table';

	protected function _select_all()
	{
		$db = $this->sharedFixture;

		return $db->execute_query('SELECT * FROM '.$db->quote_table($this->_table).' ORDER BY value');
	}

	protected function _select_null()
	{
		$db = $this->sharedFixture;

		return $db->execute_query('SELECT NULL AS value FROM '.$db->quote_table($this->_table));
	}

	public function setUp()
	{
		$db = $this->sharedFixture;
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
}
