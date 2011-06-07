<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Transactions_Test extends Database_PDO_SQLite_TestCase
{
	protected $_table = 'kohana_test_table';

	protected function getDataSet()
	{
		$dataset = new PHPUnit_Extensions_Database_DataSet_CsvDataSet;
		$dataset->addTable(
			Database::factory()->table_prefix().$this->_table,
			dirname(dirname(dirname(__FILE__))).'/datasets/values.csv'
		);

		return $dataset;
	}

	/**
	 * Verify that savepoint names can be reused.
	 *
	 * @link http://www.sqlite.org/lang_savepoint.html
	 *
	 * @covers  Database_PDO_SQLite::commit
	 * @covers  Database_PDO_SQLite::rollback
	 * @covers  Database_PDO_SQLite::savepoint
	 */
	public function test_rdbms_savepoint_names()
	{
		$table = new SQL_Table($this->_table);
		$select = new SQL_Expression('SELECT value FROM ?', array($table));
		$update = new SQL_Expression('UPDATE ? SET value = :value', array($table));

		$db = Database::factory();

		$db->execute($update->param(':value', 1));
		$db->begin();

		// Use the same savepoint name twice
		$db->execute('SAVEPOINT a');
		$db->execute($update->param(':value', 2));
		$db->execute('SAVEPOINT a');
		$db->execute($update->param(':value', 3));

		// Rollback works
		$db->execute('ROLLBACK TO a');
		$this->assertEquals(2, $db->execute_query($select)->get());

		// Rollback works repeatedly
		$db->execute('ROLLBACK TO a');
		$this->assertEquals(2, $db->execute_query($select)->get());

		// Release works
		$db->execute('RELEASE SAVEPOINT a');

		// Rollback still works
		$db->execute('ROLLBACK TO a');
		$this->assertEquals(1, $db->execute_query($select)->get());

		// Release still works
		$db->execute('RELEASE SAVEPOINT a');
	}
}
