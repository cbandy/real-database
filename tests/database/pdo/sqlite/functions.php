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
class Database_PDO_SQLite_Functions_Test extends Database_PDO_SQLite_TestCase
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

	public function aggregate_count_odd_step($context, $row, $value)
	{
		$context += ($value % 2);

		return $context;
	}

	public function aggregate_count_odd_final($context, $row)
	{
		return $context;
	}

	public function provider_create_aggregate()
	{
		$table = Database::factory()->quote_table($this->_table);

		return array(
			array(
				array(
					'count_odd',
					array($this, 'aggregate_count_odd_step'),
					array($this, 'aggregate_count_odd_final'),
				),
				'SELECT count_odd(value) FROM '.$table, 4
			),
			array(
				array(
					'count_odd',
					array($this, 'aggregate_count_odd_step'),
					array($this, 'aggregate_count_odd_final'),
					1,
				),
				'SELECT count_odd(value) FROM '.$table, 4
			),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::create_aggregate
	 *
	 * @dataProvider    provider_create_aggregate
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $sql        SQL query which uses the aggregate function
	 * @param   mixed   $expected   Expected value from the query
	 */
	public function test_create_aggregate($arguments, $sql, $expected)
	{
		$db = Database::factory();

		$this->assertTrue(
			call_user_func_array(array($db, 'create_aggregate'), $arguments)
		);

		$this->assertEquals($expected, $db->execute_query($sql)->get());
	}

	public function function_is_odd($value)
	{
		return ($value % 2);
	}

	public function provider_create_function()
	{
		$table = Database::factory()->quote_table($this->_table);

		return array(
			array(
				array('is_odd', array($this, 'function_is_odd')),
				'SELECT is_odd(value) FROM '.$table.' ORDER BY id',
				array(
					array('is_odd(value)' => 0),
					array('is_odd(value)' => 1),
					array('is_odd(value)' => 0),
					array('is_odd(value)' => 0),
					array('is_odd(value)' => 1),
					array('is_odd(value)' => 1),
					array('is_odd(value)' => 1),
				),
			),
			array(
				array('is_odd', array($this, 'function_is_odd'), 1),
				'SELECT is_odd(value) FROM '.$table.' ORDER BY id',
				array(
					array('is_odd(value)' => 0),
					array('is_odd(value)' => 1),
					array('is_odd(value)' => 0),
					array('is_odd(value)' => 0),
					array('is_odd(value)' => 1),
					array('is_odd(value)' => 1),
					array('is_odd(value)' => 1),
				),
			),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::create_function
	 *
	 * @dataProvider    provider_create_function
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $sql        SQL query which uses the function
	 * @param   mixed   $expected   Expected result set from the query
	 */
	public function test_create_function($arguments, $sql, $expected)
	{
		$db = Database::factory();

		$this->assertTrue(
			call_user_func_array(array($db, 'create_function'), $arguments)
		);

		$this->assertEquals($expected, $db->execute_query($sql)->as_array());
	}
}
