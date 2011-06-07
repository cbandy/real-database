<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlserver
 */
class Database_PDO_SQLServer_Transactions_Test extends Database_PDO_SQLServer_TestCase
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
	 * Verify that a savepoint is also released during rollback.
	 *
	 * @link http://msdn.microsoft.com/en-us/library/ms181299.aspx
	 *
	 * @covers  PDO::exec
	 */
	public function test_rdbms_rollback_releases_savepoint()
	{
		$db = Database::factory();

		$db->begin();
		$db->execute('SAVE TRANSACTION a');
		$db->execute('ROLLBACK TRANSACTION a');

		$this->setExpectedException(
			'Database_Exception',
			'No transaction or savepoint of that name was found',
			'25000'
		);

		// The savepoint no longer exists
		$db->execute('ROLLBACK TRANSACTION a');
	}

	/**
	 * Verify that savepoint names can be reused.
	 *
	 * @link http://msdn.microsoft.com/en-us/library/ms178157.aspx
	 *
	 * @covers  Database_PDO_SQLServer::commit
	 * @covers  Database_PDO_SQLServer::rollback
	 * @covers  Database_PDO_SQLServer::savepoint
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
		$db->execute('SAVE TRANSACTION a');
		$db->execute($update->param(':value', 2));
		$db->execute('SAVE TRANSACTION a');
		$db->execute($update->param(':value', 3));

		// Rollback works
		$db->execute('ROLLBACK TRANSACTION a');
		$this->assertEquals(2, $db->execute_query($select)->get());

		// Rollback still works
		$db->execute('ROLLBACK TRANSACTION a');
		$this->assertEquals(1, $db->execute_query($select)->get());
	}

	public function provider_command()
	{
		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('INSERT INTO ? (value) VALUES (100)', array(new SQL_Table($this->_table))),
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('DELETE FROM ? WHERE value = 60', array(new SQL_Table($this->_table))),
			),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::rollback
	 *
	 * @dataProvider    provider_command
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 */
	public function test_rollback($query, $command)
	{
		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$db->begin();

		// Change the dataset
		$db->execute_command($command);

		$this->assertNull($db->rollback());
		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'Changes reverted');
	}

	/**
	 * @covers  Database_PDO_SQLServer::rollback
	 * @covers  Database_PDO_SQLServer::savepoint
	 */
	public function test_savepoint()
	{
		$db = Database::factory();

		$command = 'INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (100)';
		$query = 'SELECT * FROM '.$db->quote_table($this->_table);
		$savepoint = 'kohana_savepoint';

		$db->begin();

		// Change the dataset
		$db->execute_command($command);
		$before = $db->execute_query($query)->as_array();

		$this->assertSame($savepoint, $db->savepoint($savepoint));
		$this->assertSame($before, $db->execute_query($query)->as_array(), 'No change');

		// Change the dataset
		$db->execute_command($command);

		$this->assertNull($db->rollback($savepoint));
		$this->assertSame($before, $db->execute_query($query)->as_array(), 'Reverted');
	}

	/**
	 * Setting a savepoint when not in a transaction throws an exception.
	 *
	 * @covers  Database_PDO_SQLServer::savepoint
	 */
	public function test_savepoint_no_transaction()
	{
		$db = Database::factory();

		$this->setExpectedException(
			'Database_Exception', 'no active transaction', '25000'
		);

		$db->savepoint('kohana_savepoint');
	}

	/**
	 * Reverting a non-existent savepoint throws an exception.
	 *
	 * @covers  Database_PDO_SQLServer::rollback
	 */
	public function test_rollback_invalid_savepoint()
	{
		$db = Database::factory();
		$db->begin();

		$this->setExpectedException(
			'Database_Exception',
			'No transaction or savepoint of that name was found',
			'25000'
		);

		$db->rollback('kohana_savepoint');
	}

	/**
	 * @covers  Database_PDO_SQLServer::rollback
	 */
	public function test_rollback_savepoint_repeated()
	{
		$db = Database::factory();

		$command = 'INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (100)';
		$query = 'SELECT * FROM '.$db->quote_table($this->_table);
		$savepoint = 'kohana_savepoint';

		$db->begin();

		// Change the dataset, set a savepoint
		$db->execute_command($command);
		$before = $db->execute_query($query)->as_array();
		$db->savepoint($savepoint);

		// Change the dataset, revert
		$db->execute_command($command);
		$db->rollback($savepoint);

		// Change the dataset
		$db->execute_command($command);

		$this->assertNull($db->rollback($savepoint));
		$this->assertSame($before, $db->execute_query($query)->as_array(), 'Reverted');
	}
}
