<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Statement_Test extends Database_MySQL_TestCase
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

	public function provider_constructor_name()
	{
		return array
		(
			array('a'),
			array('b'),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::__construct
	 * @covers  Database_MySQL_Statement::__toString
	 * @dataProvider    provider_constructor_name
	 *
	 * @param   string  $value      Statement name
	 */
	public function test_constructor_name($value)
	{
		$db = Database::factory();
		$statement = new Database_MySQL_Statement($db, $value);

		$this->assertSame($db->quote_identifier($value), (string) $statement);
	}

	/**
	 * @covers  Database_MySQL_Statement::deallocate
	 */
	public function test_deallocate()
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, 'SELECT 1');
		$statement = new Database_MySQL_Statement($db, $name);

		$this->assertNull($statement->deallocate());

		try
		{
			$statement->deallocate();
			$this->fail('Calling deallocate() twice should fail with a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	public function provider_execute_command()
	{
		return array
		(
			array(1, 'INSERT INTO $table (value) VALUES (10)', array()),
			array(3, 'DELETE FROM $table WHERE value = ?', array(65)),
			array(2, 'UPDATE $table SET value = ? WHERE value = 60', array(20)),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::_set_variables
	 * @covers  Database_MySQL_Statement::execute_command
	 * @dataProvider    provider_execute_command
	 *
	 * @param   integer $expected   Expected result
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 */
	public function test_execute_command($expected, $statement, $parameters)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, strtr($statement, array('$table' => $db->quote_table($this->_table))));
		$statement = new Database_MySQL_Statement($db, $name, $parameters);

		$this->assertSame($expected, $statement->execute_command());
	}

	public function provider_execute_insert()
	{
		return array
		(
			array(array(2, 8), 'INSERT INTO $table (value) VALUES (10), (20)', array()),
			array(array(1, 8), 'INSERT INTO $table (value) VALUES (?)', array(50)),
			array(array(2, 8), 'INSERT INTO $table (value) VALUES (?), (?)', array(70, 80)),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::_set_variables
	 * @covers  Database_MySQL_Statement::execute_insert
	 * @dataProvider    provider_execute_insert
	 *
	 * @param   array   $expected   Expected result
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 */
	public function test_execute_insert($expected, $statement, $parameters)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, strtr($statement, array('$table' => $db->quote_table($this->_table))));
		$statement = new Database_MySQL_Statement($db, $name, $parameters);

		$this->assertEquals($expected, $statement->execute_insert());
	}

	public function provider_execute_query()
	{
		return array
		(
			array('SELECT * FROM $table WHERE value < 60', array(), array(
				array('id' => 1, 'value' => 50),
				array('id' => 2, 'value' => 55),
			)),
			array('SELECT * FROM $table WHERE value < ?', array(60), array(
				array('id' => 1, 'value' => 50),
				array('id' => 2, 'value' => 55),
			)),
			array('SELECT * FROM $table WHERE value > ?', array(60), array(
				array('id' => 5, 'value' => 65),
				array('id' => 6, 'value' => 65),
				array('id' => 7, 'value' => 65),
			)),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::_set_variables
	 * @covers  Database_MySQL_Statement::execute_query
	 * @dataProvider    provider_execute_query
	 *
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 * @param   array   $expected   Expected result
	 */
	public function test_execute_query($statement, $parameters, $expected)
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);
		$name = $db->prepare(NULL, strtr($statement, array('$table' => $db->quote_table($this->_table))));
		$statement = new Database_MySQL_Statement($db, $name, $parameters);

		$result = $statement->execute_query();

		$this->assertType('Database_MySQL_Result', $result);
		$this->assertEquals($expected, $result->as_array());
	}
}
