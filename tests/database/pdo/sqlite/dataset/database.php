<?php

require_once dirname(dirname(__FILE__)).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Dataset_Database_Test extends Database_PDO_SQLite_TestCase
{
	protected $_table = 'kohana_test_table';

	protected function getDataSet()
	{
		$dataset = new PHPUnit_Extensions_Database_DataSet_CsvDataSet;
		$dataset->addTable(
			Database::factory()->table_prefix().$this->_table,
			dirname(dirname(dirname(dirname(__FILE__)))).'/datasets/values.csv'
		);

		return $dataset;
	}

	public function provider_execute_command_query()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			// Always zero
			array(0, 'SELECT 1'),
			array(0, 'SELECT 1 UNION SELECT 2'),
			array(0, 'SELECT * FROM '.$table),
		);
	}

	/**
	 * @covers  Database_PDO::execute_command
	 *
	 * @dataProvider    provider_execute_command_query
	 *
	 * @param   integer                 $expected
	 * @param   string|SQL_Expression   $statement
	 */
	public function test_execute_command_query($expected, $statement)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->execute_command($statement));
	}

	public function provider_execute_compound_command()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(6, 'DELETE FROM '.$table.' WHERE id = 1; DELETE FROM '.$table),
			array(1, 'DELETE FROM '.$table.' WHERE id > 1; DELETE FROM '.$table),
			array(5, new SQL_Expression(
				'DELETE FROM :table WHERE id > 5; DELETE FROM :table',
				array(':table' => new SQL_Table($this->_table))
			))
		);
	}

	/**
	 * All statements are executed. The affected rows from the last statement
	 * are returned.
	 *
	 * @covers  Database_PDO_SQLite::execute_command
	 *
	 * @dataProvider    provider_execute_compound_command
	 *
	 * @param   integer                 $expected
	 * @param   string|SQL_Expression   $statements
	 */
	public function test_execute_compound_command($expected, $statements)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->execute_command($statements));
	}

	public function provider_execute_compound_command_mixed()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(7, 'SELECT * FROM '.$table.' WHERE id < 3; DELETE FROM '.$table),
			array(2, 'DELETE FROM '.$table.' WHERE id < 3; SELECT * FROM '.$table),
			array(4,
				'SELECT * FROM '.$table.';'
				.'DELETE FROM '.$table.' WHERE id < 3;'
				.'DELETE FROM '.$table.' WHERE id < 7;'
			),
			array(4,
				'DELETE FROM '.$table.' WHERE id < 3;'
				.'SELECT * FROM '.$table.';'
				.'DELETE FROM '.$table.' WHERE id < 7;'
			),
			array(4,
				'DELETE FROM '.$table.' WHERE id < 3;'
				.'DELETE FROM '.$table.' WHERE id < 7;'
				.'SELECT * FROM '.$table.';'
			),
		);
	}

	/**
	 * The affected rows from the last command (not statement) are returned.
	 *
	 * @covers  Database_PDO_SQLite::execute_command
	 *
	 * @dataProvider    provider_execute_compound_command_mixed
	 *
	 * @param   integer                 $expected
	 * @param   string|SQL_Expression   $statements
	 */
	public function test_execute_compound_command_mixed($expected, $statements)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->execute_command($statements));
	}

	/**
	 * When executing multiple queries, only the first result is returned.
	 *
	 * @covers  Database_PDO::execute_query
	 */
	public function test_execute_compound_query()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$result = $db->execute_query(
			'SELECT * FROM '.$table.' WHERE id < 3; SELECT * FROM '.$table
		);

		$this->assertType('Database_Result', $result);
		$this->assertSame(2, count($result));
	}

	public function provider_execute_compound_query_mixed()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(2, 7, 'SELECT * FROM '.$table.' WHERE id < 3; DELETE FROM '.$table),
			array(0, 5, 'DELETE FROM '.$table.' WHERE id < 3; SELECT * FROM '.$table),
			array(0, 5,
				'DELETE FROM '.$table.' WHERE id < 3;'
				.'DELETE FROM '.$table.' WHERE id < 6;'
				.'SELECT * FROM '.$table
			),
		);
	}

	/**
	 * When executing multiple statements, only the first statement is executed.
	 *
	 * @covers  Database_PDO::execute_query
	 *
	 * @dataProvider    provider_execute_compound_query_mixed
	 *
	 * @param   integer                 $expected_result    Number of rows in the result
	 * @param   integer                 $expected_table     Number of rows remaining in the table
	 * @param   string|SQL_Expression   $statements
	 */
	public function test_execute_compound_query_mixed($expected_result, $expected_table, $statements)
	{
		$db = Database::factory();

		$result = $db->execute_query($statements);

		$this->assertSame($expected_result, count($result));
		$this->assertSame(
			$expected_table,

			// https://github.com/sebastianbergmann/dbunit/issues/34
			// $this->getConnection()->getRowCount($db->table_prefix().$this->_table)

			(int) $this->getConnection()->getConnection()->query(
				'SELECT COUNT(*) FROM '.$db->quote_table($this->_table)
			)->fetchColumn()
		);
	}

	public function provider_execute_insert()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(array(0,0), ''),
			array(array(1,8), 'INSERT INTO '.$table.' (value) VALUES (99)'),
			array(
				array(1,9),
				$db->insert($this->_table, array('value'))
					->identity('id')
					->values(array('99'), array('100'))
			),
		);
	}

	/**
	 * @covers  Database_PDO::execute_insert
	 *
	 * @dataProvider    provider_execute_insert
	 *
	 * @param   integer                 $expected
	 * @param   string|SQL_Expression   $statement
	 */
	public function test_execute_insert($expected, $statement)
	{
		$db = Database::factory();

		$this->assertEquals($expected, $db->execute_insert($statement, NULL));
	}

	/**
	 * Executing a blank insert retrieves the last inserted ID.
	 *
	 * @covers  Database_PDO::execute_insert
	 */
	public function test_execute_insert_prior()
	{
		$db = Database::factory();

		$db->execute_command(
			'INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (99)'
		);

		$this->assertEquals(array(0,8), $db->execute_insert('', NULL));
	}
}
