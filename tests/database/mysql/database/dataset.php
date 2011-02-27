<?php

require_once dirname(dirname(__FILE__)).'/testcase'.EXT;;
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
	 * @covers  Database_MySQL::execute_command
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_command()
	{
		$db = Database::factory();

		$db->execute_command(
			'DELETE FROM '.$db->quote_table($this->_table).';'
			.'DELETE FROM '.$db->quote_table($this->_table)
		);
	}

	/**
	 * @covers  Database_MySQL::execute_query
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_query()
	{
		$db = Database::factory();

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
}
