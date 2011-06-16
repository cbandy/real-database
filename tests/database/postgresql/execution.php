<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Execution_Test extends Database_PostgreSQL_TestCase
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

	public function provider_execute_command_ok()
	{
		$table = Database::factory()->quote_table($this->_table);

		return array(
			array('DELETE FROM '.$table),
			array('START TRANSACTION'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::execute_command_ok
	 *
	 * @dataProvider    provider_execute_command_ok
	 *
	 * @param   string  $statement  Argument to the method
	 */
	public function test_execute_command_ok($statement)
	{
		$db = Database::factory();

		$this->assertNull($db->execute_command_ok($statement));
	}

	public function provider_execute_command_ok_error()
	{
		$table = Database::factory()->quote_table($this->_table);

		return array(
			array('kohana-invalid-sql', 'syntax error', '42601'),
			array('SELECT * FROM '.$table, '', NULL),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::execute_command_ok
	 *
	 * @dataProvider    provider_execute_command_ok_error
	 *
	 * @param   string  $statement  Argument to the method
	 * @param   string  $message    Expected exception message
	 * @param   string  $code       Expected exception code
	 */
	public function test_execute_command_ok_error($statement, $message, $code)
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', $message, $code);

		$db->execute_command_ok($statement);
	}
}
