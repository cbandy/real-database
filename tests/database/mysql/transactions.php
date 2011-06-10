<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Transactions_Test extends Database_MySQL_TestCase
{
	protected $_table = 'kohana_test_table';

	protected function getDataSet()
	{
		$dataset = new PHPUnit_Extensions_Database_DataSet_CsvDataSet;
		$dataset->addTable(
			Database::factory()->table_prefix().$this->_table,
			dirname(dirname(__FILE__)).'/datasets/values.csv'
		);

		return $dataset;
	}

	/**
	 * Verify that savepoints are replaced when reusing the same name.
	 *
	 * @link http://dev.mysql.com/doc/en/savepoint.html
	 *
	 * @covers  Database_MySQL::commit
	 * @covers  Database_MySQL::rollback
	 * @covers  Database_MySQL::savepoint
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

		$this->setExpectedException(
			'Database_Exception', 'does not exist', 1305
		);

		// The first savepoint no longer exists
		$db->execute('ROLLBACK TO a');
	}

	public function provider_begin()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				NULL,
				'kohana_txn_0',
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 60', array($table)
				),
				'kohana_savepoint',
				'kohana_savepoint',
			),
		);
	}

	/**
	 * @covers  Database_MySQL::begin
	 *
	 * @dataProvider    provider_begin
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   string          $name       Transaction name
	 * @param   string          $expected   Expected transaction name
	 */
	public function test_begin($query, $command, $name, $expected)
	{
		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$this->assertSame($expected, $db->begin($name));
		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'No change');

		// Change the dataset
		$db->execute_command($command);
		$this->assertSame($initial, Database::factory()->execute_query($query)->as_array(), 'Other connection unaffected');
	}

	public function provider_rollback()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 60', array($table)
				),
			),
		);
	}

	/**
	 * @covers  Database_MySQL::rollback
	 *
	 * @dataProvider    provider_rollback
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

	public function provider_savepoint()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				NULL,
				'kohana_txn_1',
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 60', array($table)
				),
				'kohana_savepoint',
				'kohana_savepoint',
			),
		);
	}

	/**
	 * @covers  Database_MySQL::rollback
	 * @covers  Database_MySQL::savepoint
	 *
	 * @dataProvider    provider_savepoint
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   string          $name       Savepoint name
	 * @param   string          $expected   Expected savepoint name
	 */
	public function test_savepoint($query, $command, $name, $expected)
	{
		$db = Database::factory();
		$db->begin();

		// Change the dataset
		$db->execute_command($command);
		$before = $db->execute_query($query)->as_array();

		$this->assertSame($expected, $db->savepoint($name));
		$this->assertSame($before, $db->execute_query($query)->as_array(), 'No change');

		// Change the dataset
		$db->execute_command($command);

		$this->assertNull($db->rollback($expected));
		$this->assertSame($before, $db->execute_query($query)->as_array(), 'Reverted');
	}

	/**
	 * Setting a savepoint outside of a transaction succeeds.
	 *
	 * @covers  Database_MySQL::savepoint
	 */
	public function test_savepoint_no_transaction()
	{
		$db = Database::factory();
		$savepoint = 'kohana_savepoint';

		$this->assertSame($savepoint, $db->savepoint($savepoint));
	}

	/**
	 * Reverting a non-existent savepoint throws an exception.
	 *
	 * @covers  Database_MySQL::rollback
	 */
	public function test_rollback_invalid_savepoint()
	{
		$db = Database::factory();
		$db->begin();

		$this->setExpectedException(
			'Database_Exception', 'does not exist', 1305
		);

		$db->rollback('kohana_savepoint');
	}

	public function provider_result()
	{
		return array(
			array(
				new SQL_Expression('SELECT value FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('INSERT INTO ? (value) VALUES (100)', array(new SQL_Table($this->_table))),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 60),
					array('value' => 60),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
					array('value' => 100),
				),
			),
			array(
				new SQL_Expression('SELECT value FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('DELETE FROM ? WHERE value = 60', array(new SQL_Table($this->_table))),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
				),
			),
		);
	}

	/**
	 * @covers  Database_MySQL::commit
	 *
	 * @dataProvider    provider_result
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   array           $expected   Expected result of the query after command is executed and after commit
	 */
	public function test_commit($query, $command, $expected)
	{
		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$db->begin();

		// Change the dataset
		$db->execute_command($command);
		$this->assertSame($initial, $other->execute_query($query)->as_array(), 'Other connection unaffected');

		$this->assertNull($db->commit());
		$this->assertEquals($expected, $other->execute_query($query)->as_array(), 'Other connection affected');
	}

	/**
	 * A query that reads a dataset, two commands that alter the dataset and the
	 * dataset after both commands are executed.
	 */
	public function provider_nested_transaction_both()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 65', array($table)
				),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 60),
					array('value' => 60),
					array('value' => 100),
				),
			),
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value IN (60, 65)', array($table)
				),
				new SQL_Expression('UPDATE ? SET value = 10', array($table)),
				array(
					array('value' => 10),
					array('value' => 10),
				),
			),
		);
	}

	/**
	 * @covers  Database_MySQL::begin
	 * @covers  Database_MySQL::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed and after commit
	 */
	public function test_nested_transaction_commit_commit($query, $command1, $command2, $expected)
	{
		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit inner transaction
		$this->assertNull($db->commit($inner));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit outer transaction
		$this->assertNull($db->commit($outer));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection affected
		$this->assertEquals($expected, $other->execute_query($query)->as_array());
	}

	/**
	 * @covers  Database_MySQL::begin
	 * @covers  Database_MySQL::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed
	 */
	public function test_nested_transaction_commit_rollback($query, $command1, $command2, $expected)
	{
		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback inner transaction
		$this->assertNull($db->rollback($inner));
		$this->assertNotEquals($expected, $db->execute_query($query)->as_array());
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback outer transaction
		$this->assertNull($db->rollback($outer));
		$this->assertSame($initial, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());
	}

	/**
	 * A query that reads a dataset, two commands that alter the dataset and the
	 * dataset after the first command is executed.
	 */
	public function provider_nested_transaction_rollback_commit()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 65', array($table)
				),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 60),
					array('value' => 60),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
					array('value' => 100),
				),
			),
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value IN (60, 65)', array($table)
				),
				new SQL_Expression('UPDATE ? SET value = 10', array($table)),
				array(
					array('value' => 50),
					array('value' => 55),
				),
			),
		);
	}

	/**
	 * @covers  Database_MySQL::begin
	 * @covers  Database_MySQL::commit
	 *
	 * @dataProvider    provider_nested_transaction_rollback_commit
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after the first command is executed and after commit
	 */
	public function test_nested_transaction_rollback_commit($query, $command1, $command2, $expected)
	{
		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertNotEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback inner transaction
		$this->assertNull($db->rollback($inner));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit outer transaction
		$this->assertNull($db->commit($outer));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection affected
		$this->assertEquals($expected, $other->execute_query($query)->as_array());
	}

	/**
	 * Commit the named outer transaction when nested.
	 *
	 * @covers  Database_MySQL::begin
	 * @covers  Database_MySQL::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed and after commit
	 */
	public function test_nested_transaction_short_commit($query, $command1, $command2, $expected)
	{
		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit outer transaction
		$this->assertNull($db->commit($outer));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection affected
		$this->assertEquals($expected, $other->execute_query($query)->as_array());
	}

	/**
	 * Rollback the named outer transaction when nested.
	 *
	 * @covers  Database_MySQL::begin
	 * @covers  Database_MySQL::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed
	 */
	public function test_nested_transaction_short_rollback($query, $command1, $command2, $expected)
	{
		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback outer transaction
		$this->assertNull($db->rollback($outer));
		$this->assertSame($initial, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());
	}
}
