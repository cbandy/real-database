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
class Database_MySQL_Execution_Test extends Database_MySQL_TestCase
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

	public function provider_execute_command_argument()
	{
		$db = Database::factory();
		$table = new SQL_Table($this->_table);

		return array(
			// String
			array(7, 'DELETE FROM '.$db->quote($table)),
			array(7, 'DELETE FROM '.$db->quote($table).' WHERE 1 = 1'),

			// Database_Statement
			array(7, new Database_Statement('DELETE FROM '.$db->quote($table))),
			array(
				7,
				new Database_Statement(
					'DELETE FROM '.$db->quote($table).' WHERE ? = ?', array(1,1)
				),
			),

			// SQL_Expression
			array(7, new SQL_Expression('DELETE FROM '.$db->quote($table))),
			array(7, new SQL_Expression('DELETE FROM ?', array($table))),
			array(
				7,
				new SQL_Expression(
					'DELETE FROM ? WHERE :a = :a', array($table, ':a' => 1)
				),
			),
		);
	}

	/**
	 * @covers  Database_MySQL::execute_command
	 *
	 * @dataProvider    provider_execute_command_argument
	 *
	 * @param   integer                                     $expected
	 * @param   string|Database_Statement|SQL_Expression    $value      Argument to the method
	 */
	public function test_execute_command_argument($expected, $value)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->execute_command($value));
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

		$this->assertSame(
			array(3,8), $result, 'AUTO_INCREMENT of the first row'
		);
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

		$this->assertSame(
			array(0,8), $result, 'First AUTO_INCREMENT of prior INSERT'
		);
	}

	public function provider_execute_query_argument()
	{
		$db = Database::factory();
		$table = new SQL_Table($this->_table);

		return array(
			// String
			array(
				'SELECT * FROM '.$db->quote($table).' WHERE value = 60',
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				'SELECT * FROM '.$db->quote($table).' WHERE value = 60',
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),

			// Database_Statement
			array(
				new Database_Statement(
					'SELECT * FROM '.$db->quote($table).' WHERE value = 60'
				),
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				new Database_Statement(
					'SELECT * FROM '.$db->quote($table).' WHERE value = 60'
				),
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),
			array(
				new Database_Statement(
					'SELECT * FROM '.$db->quote($table).' WHERE value = ?',
					array(65)
				),
				FALSE,
				array(
					array('id' => 5, 'value' => 65),
					array('id' => 6, 'value' => 65),
					array('id' => 7, 'value' => 65),
				),
			),
			array(
				new Database_Statement(
					'SELECT * FROM '.$db->quote($table).' WHERE value = ?',
					array(65)
				),
				TRUE,
				array(
					(object) array('id' => 5, 'value' => 65),
					(object) array('id' => 6, 'value' => 65),
					(object) array('id' => 7, 'value' => 65),
				),
			),

			// SQL_Expression
			array(
				new SQL_Expression(
					'SELECT * FROM '.$db->quote($table).' WHERE value = 60'
				),
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM '.$db->quote($table).' WHERE value = 60'
				),
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE value = 60', array($table)
				),
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE value = 60', array($table)
				),
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE value = ?', array($table, 60)
				),
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE value = ?', array($table, 60)
				),
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),
		);
	}

	/**
	 * @covers  Database_MySQL::execute_query
	 *
	 * @dataProvider    provider_execute_query_argument
	 *
	 * @param   string|Database_Statement|SQL_Expression    $statement  First argument to the method
	 * @param   boolean|string                              $as_object  Second argument to the method
	 * @param   array                                       $expected
	 */
	public function test_execute_query_argument($statement, $as_object, $expected)
	{
		$db = Database::factory();

		$result = $db->execute_query($statement, $as_object);

		$this->assertType('Database_MySQL_Result', $result);
		$this->assertEquals($expected, $result->as_array());
	}

}
