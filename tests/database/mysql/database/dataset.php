<?php

require_once dirname(dirname(__FILE__)).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Database_DataSet_Test extends Database_MySQL_TestCase
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
	 * @covers  Database_MySQL::execute_command
	 */
	public function test_execute_command_expression()
	{
		$db = Database::factory();

		$result = $db->execute_command(
			new SQL_Expression(
				'DELETE FROM ?',
				array(new SQL_Table($this->_table))
			)
		);

		$this->assertSame(7, $result);
	}

	/**
	 * @covers  Database_MySQL::execute_command
	 */
	public function test_execute_command_query()
	{
		$db = Database::factory();

		$result = $db->execute_command(
			'SELECT * FROM '.$db->quote_table($this->_table)
		);

		$this->assertSame(7, $result, 'Number of returned rows');
	}

	/**
	 * Throws an exception when executing a compound statement.
	 *
	 * @covers  Database_MySQL::execute_command
	 */
	public function test_execute_compound_command()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'SQL syntax', 1064);

		$db->execute_command(
			'DELETE FROM '.$db->quote_table($this->_table).';'
			.'DELETE FROM '.$db->quote_table($this->_table)
		);
	}

	/**
	 * Throws an exception when executing a compound statement.
	 *
	 * @covers  Database_MySQL::execute_query
	 */
	public function test_execute_compound_query()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'SQL syntax', 1064);

		$db->execute_query(
			'SELECT * FROM '.$db->quote_table($this->_table).';'
			.'SELECT * FROM '.$db->quote_table($this->_table)
		);
	}

	/**
	 * @covers  Database_MySQL::execute_insert
	 */
	public function test_execute_insert()
	{
		$db = Database::factory();

		$result = $db->execute_insert(
			'INSERT INTO '.$db->quote_table($this->_table)
			.' (value) VALUES (90), (95), (100)',
			NULL
		);

		$this->assertSame(array(3,8), $result, 'AUTO_INCREMENT of the first row');
	}

	/**
	 * @covers  Database_MySQL::execute_insert
	 */
	public function test_execute_insert_empty()
	{
		$db = Database::factory();

		$db->execute_insert(
			'INSERT INTO '.$db->quote_table($this->_table)
			.' (value) VALUES (90), (95), (100)',
			NULL
		);

		$result = $db->execute_insert('', NULL);

		$this->assertSame(array(0,8), $result, 'First AUTO_INCREMENT of prior INSERT');
	}

	public function provider_transaction_command()
	{
		return array
		(
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
	 * @covers  Database_MySQL::begin
	 * @dataProvider    provider_transaction_command
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 */
	public function test_transaction_begin($query, $command)
	{
		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$this->assertNull($db->begin());

		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'No change');

		// Change the dataset
		$db->execute_command($command);

		$this->assertSame($initial, Database::factory()->execute_query($query)->as_array(), 'Other connection unaffected');
	}

	/**
	 * @covers  Database_MySQL::rollback
	 * @dataProvider    provider_transaction_command
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 */
	public function test_transaction_rollback($query, $command)
	{
		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		// Change the dataset
		$db->begin();
		$db->execute_command($command);

		$this->assertNull($db->rollback());

		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'Changes reverted');
	}

	public function provider_transaction_result()
	{
		return array
		(
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
	 * @dataProvider    provider_transaction_result
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   array           $expected   Expected result of the query after command is executed and after commit
	 */
	public function test_transaction_commit($query, $command, $expected)
	{
		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		// Change the dataset
		$db->begin();
		$db->execute_command($command);

		$this->assertSame($initial, $other->execute_query($query)->as_array(), 'Other connection unaffected');

		$this->assertNull($db->commit());

		$this->assertEquals($expected, $other->execute_query($query)->as_array(), 'Other connection affected');
	}
}
