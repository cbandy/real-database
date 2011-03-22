<?php

require_once dirname(dirname(__FILE__)).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Database_Dataset_Test extends Database_PostgreSQL_TestCase
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

	public function provider_copy_from()
	{
		$entire = array(
			array('id' => 1, 'value' => 50),
			array('id' => 2, 'value' => 55),
			array('id' => 3, 'value' => 60),
			array('id' => 4, 'value' => 60),
			array('id' => 5, 'value' => 65),
			array('id' => 6, 'value' => 65),
			array('id' => 7, 'value' => 65),
		);

		return array
		(
			array(array("20\t\\N", "22\t75"), array_merge($entire, array(
				array('id' => 20, 'value' => NULL),
				array('id' => 22, 'value' => 75)
			))),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::copy_from
	 * @dataProvider    provider_copy_from
	 *
	 * @param   array   $rows
	 * @param   array   $expected
	 */
	public function test_copy_from($rows, $expected)
	{
		$db = Database::factory();
		$db->copy_from($this->_table, $rows);

		$this->assertEquals($expected, $db->execute_query('SELECT * FROM '.$db->quote_table($this->_table))->as_array());
	}

	/**
	 * @covers  Database_PostgreSQL::copy_to
	 */
	public function test_copy_to()
	{
		$db = Database::factory();

		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t60\n", "5\t65\n", "6\t65\n", "7\t65\n"), $db->copy_to($this->_table));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_command_expression()
	{
		$db = Database::factory();

		$this->assertSame(7, $db->execute_command(new SQL_Expression('DELETE FROM ?', array(new SQL_Table($this->_table)))));
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_command
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_command_query()
	{
		$db = Database::factory();

		$this->assertSame(7, $db->execute_command('SELECT * FROM '.$db->quote_table($this->_table)), 'Number of returned rows');
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_command
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_compound_command()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertSame(2, $db->execute_command('DELETE FROM '.$table.' WHERE "id" = 3; DELETE FROM '.$table.' WHERE "id" = 5'), 'Total number of rows');

		try
		{
			// Connection should have no pending results
			$db->execute_query('SELECT * FROM '.$table);
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_command
	 */
	public function test_execute_copy_command()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertSame(0, $db->execute_command('COPY '.$table.' TO STDOUT'));
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_query
	 */
	public function test_execute_copy_query()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertNull($db->execute_query('COPY '.$table.' TO STDOUT'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 */
	public function test_execute_insert()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$sql = 'INSERT INTO '.$table.' (value) VALUES (65)';

		$this->assertEquals(array(1,8), $db->execute_insert($sql, 'id'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 */
	public function test_execute_insert_expression()
	{
		$db = Database::factory();

		$sql = new SQL_Expression(
			'INSERT INTO ? (value) VALUES (65)',
			array(new SQL_Table($this->_table))
		);

		$this->assertEquals(array(1,8), $db->execute_insert($sql, 'id'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 */
	public function test_execute_insert_ireturning()
	{
		$db = Database::factory();

		$delete = new Database_PostgreSQL_Delete($this->_table);
		$delete->where('value', '=', 60)->returning(array('id'));

		$this->assertEquals(array(2,3), $db->execute_insert($delete, 'id'));
	}

	public function provider_execute_prepared_command()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array
		(
			array('UPDATE '.$table.' SET "value" = 20 WHERE "value" = 65', array(), 3),
			array('UPDATE '.$table.' SET "value" = $1 WHERE "value" = $2', array(20, 50), 1),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_command
	 * @dataProvider    provider_execute_prepared_command
	 *
	 * @param   string  $statement
	 * @param   array   $parameters
	 * @param   integer $expected
	 */
	public function test_execute_prepared_command($statement, $parameters, $expected)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, $statement);

		$this->assertSame($expected, $db->execute_prepared_command($name, $parameters));
	}

	/**
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_command
	 * @expectedException   Database_Exception
	 */
	public function test_execute_prepared_command_error()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'UPDATE '.$table.' SET "value" = $1 WHERE "value" = $2');

		// Lacking parameters
		$db->execute_prepared_command($name);
	}

	public function provider_execute_prepared_query()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array
		(
			array('SELECT * FROM '.$table.' WHERE "value" = 60', array(), FALSE, array(
				array('id' => 3, 'value' => 60),
				array('id' => 4, 'value' => 60),
			)),

			array('SELECT * FROM '.$table.' WHERE "value" = 60', array(), TRUE, array(
				(object) array('id' => 3, 'value' => 60),
				(object) array('id' => 4, 'value' => 60),
			)),

			array('SELECT * FROM '.$table.' WHERE "value" = $1', array(65), FALSE, array(
				array('id' => 5, 'value' => 65),
				array('id' => 6, 'value' => 65),
				array('id' => 7, 'value' => 65),
			)),

			array('SELECT * FROM '.$table.' WHERE "value" = $1', array(65), TRUE, array(
				(object) array('id' => 5, 'value' => 65),
				(object) array('id' => 6, 'value' => 65),
				(object) array('id' => 7, 'value' => 65),
			)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_query
	 * @dataProvider    provider_execute_prepared_query
	 *
	 * @param   string          $statement
	 * @param   array           $parameters
	 * @param   string|boolean  $as_object
	 * @param   array           $expected
	 */
	public function test_execute_prepared_query($statement, $parameters, $as_object, $expected)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, $statement);

		$result = $db->execute_prepared_query($name, $parameters, $as_object);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals($expected, $result->as_array());
	}

	/**
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_query
	 * @expectedException   Database_Exception
	 */
	public function test_execute_prepared_query_error()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table.' WHERE "value" = $1');

		// Lacking parameters
		$db->execute_prepared_query($name);
	}

	/**
	 * @covers  Database_PostgreSQL::execute_query
	 */
	public function test_execute_query_expression()
	{
		$db = Database::factory();

		$result = $db->execute_query(new SQL_Expression('SELECT ?', array(1)));

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertSame(1, count($result));
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
	 * @covers  Database_PostgreSQL::begin
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
	 * @covers  Database_PostgreSQL::rollback
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
	 * @covers  Database_PostgreSQL::commit
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

	/**
	 * @covers  Database_PostgreSQL::savepoint
	 */
	public function test_transaction_savepoint()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$delete = 'DELETE FROM '.$table;
		$select = 'SELECT * FROM '.$table;

		$this->assertSame(7, $db->execute_query($select)->count(), 'Initial');

		$db->begin();
		$db->execute_command($delete.' WHERE "value" = 65');

		$this->assertSame(4, $db->execute_query($select)->count(), 'Deleted 65');

		$this->assertNull($db->savepoint('test_savepoint'));

		$db->execute_command($delete.' WHERE "value" = 55');

		$this->assertSame(3, $db->execute_query($select)->count(), 'Deleted 55');

		$this->assertNull($db->rollback('test_savepoint'));

		$this->assertSame(4, $db->execute_query($select)->count(), 'Rollback 55');

		$this->assertNull($db->rollback());

		$this->assertSame(7, $db->execute_query($select)->count(), 'Rollback 65');
	}
}
