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
	 *
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

		$this->assertSame(
			array(
				"1\t50\n",
				"2\t55\n",
				"3\t60\n",
				"4\t60\n",
				"5\t65\n",
				"6\t65\n",
				"7\t65\n",
			),
			$db->copy_to($this->_table)
		);
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
					'DELETE FROM '.$db->quote($table).' WHERE $1 = $1', array(1)
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
	 * @covers  Database_PostgreSQL::_execute
	 * @covers  Database_PostgreSQL::_execute_parameters
	 * @covers  Database_PostgreSQL::execute_command
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
	 * COPY statements will block unless ended.
	 *
	 * @covers  Database_PostgreSQL::_evaluate_command
	 */
	public function test_execute_command_copy()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertSame(0, $db->execute_command('COPY '.$table.' TO STDOUT'));
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

	/**
	 * @covers  Database_PostgreSQL::_evaluate_command
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_command_query()
	{
		$db = Database::factory();

		$result = $db->execute_command(
			'SELECT * FROM '.$db->quote_table($this->_table)
		);

		$this->assertSame(7, $result, 'Number of returned rows');
	}

	public function provider_execute_compound_command()
	{
		$db = Database::factory();
		$table = new SQL_Table($this->_table);

		return array(
			// String
			array(
				2,
				'DELETE FROM '.$db->quote($table).' WHERE "id" = 3;'
				.' DELETE FROM '.$db->quote($table).' WHERE "id" = 5',
			),

			// Database_Statement
			array(
				2,
				new Database_Statement(
					'DELETE FROM '.$db->quote($table).' WHERE "id" = 3;'
					.' DELETE FROM '.$db->quote($table).' WHERE "id" = 5'
				),
			),

			// SQL_Expression
			array(
				2,
				new SQL_Expression(
					'DELETE FROM :a WHERE "id" = 3;'
					.' DELETE FROM :a WHERE "id" = 5',
					array(':a' => $table)
				),
			),
			array(
				2,
				new SQL_Expression(
					'DELETE FROM :a WHERE "id" = ?;'
					.' DELETE FROM :a WHERE "id" = ?',
					array(':a' => $table, 3, 5)
				),
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_command
	 * @covers  Database_PostgreSQL::execute_command
	 *
	 * @dataProvider    provider_execute_compound_command
	 *
	 * @param   integer                                     $expected   Sum of number of rows affected
	 * @param   string|Database_Statement|SQL_Expression    $value      Argument to the method
	 */
	public function test_execute_compound_command($expected, $value)
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertSame($expected, $db->execute_command($value));

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
	 * Cannot execute parameterized compound statements.
	 *
	 * @covers  Database_PostgreSQL::_execute_parameters
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_compound_command_statement_parameters()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->setExpectedException(
			'Database_Exception', 'multiple commands', '42601'
		);

		$db->execute_command(
			new Database_Statement(
				'DELETE FROM '.$table.' WHERE "id" = $1;'
				.' DELETE FROM '.$table.' WHERE "id" = $2',
				array(3, 5)
			)
		);
	}

	public function provider_execute_insert_argument()
	{
		$db = Database::factory();
		$table = new SQL_Table($this->_table);

		$result = array(
			// String
			array(
				array(1, '8'),
				'INSERT INTO '.$db->quote($table).' (value) VALUES (65)',
				'id',
			),
			array(
				array(2, '8'),
				'INSERT INTO '.$db->quote($table).' (value) VALUES (70), (75)',
				'id',
			),
			array(
				array(1, '50'),
				'INSERT INTO '.$db->quote($table).' (value) VALUES (50)',
				'value',
			),
			array(
				array(2, '10'),
				'INSERT INTO '.$db->quote($table).' (value) VALUES (10), (30)',
				'value',
			),

			// Database_Statement
			array(
				array(1, '8'),
				new Database_Statement(
					'INSERT INTO '.$db->quote($table)
					.' (value) VALUES (65) RETURNING ("id")'
				),
				'id',
			),
			array(
				array(2, '8'),
				new Database_Statement(
					'INSERT INTO '.$db->quote($table)
					.' (value) VALUES (30), (40) RETURNING ("id")'
				),
				'id',
			),
			array(
				array(1, '8'),
				new Database_Statement(
					'INSERT INTO '.$db->quote($table)
					.' (value) VALUES ($1) RETURNING ("id")',
					array(75)
				),
				'id',
			),
			array(
				array(2, '8'),
				new Database_Statement(
					'INSERT INTO '.$db->quote($table)
					.' (value) VALUES ($1), ($1) RETURNING ("id")',
					array(75)
				),
				'id',
			),

			// SQL_Expression
			array(
				array(1, '8'),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (65)', array($table)
				),
				'id',
			),
			array(
				array(1, '8'),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (?)', array($table, 70)
				),
				'id',
			),
			array(
				array(2, '8'),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (75), (80)', array($table)
				),
				'id',
			),
			array(
				array(2, '8'),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (?), (?)', array($table, 1, 2)
				),
				'id',
			),
			array(
				array(1, '90'),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (90)', array($table)
				),
				'value',
			),
			array(
				array(2, '7'),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (7), (11)', array($table)
				),
				'value',
			),
		);

		// Database_iReturning
		$delete = new Database_PostgreSQL_Delete($this->_table);
		$delete->where('value', '=', 60)->returning(array('id'));
		$result[] = array(array(2, '3'), $delete, 'id');

		$delete = new Database_PostgreSQL_Delete($this->_table);
		$delete->where('value', '>', 60)->returning(array('value'));
		$result[] = array(array(3, '65'), $delete, 'value');

		$insert = new Database_PostgreSQL_Insert($this->_table);
		$insert->columns(array('value'))->values(array(100));
		$result[] = array(array(1, '8'), $insert, 'id');
		$result[] = array(array(1, '100'), $insert, 'value');

		$insert = new Database_PostgreSQL_Insert($this->_table);
		$insert->columns(array('value'))->values(array(50))->identity('id');
		$result[] = array(array(1, '8'), $insert, 'id');

		$insert = new Database_PostgreSQL_Insert($this->_table);
		$insert->columns(array('value'))->values(array(50))->identity('value');
		$result[] = array(array(1, '50'), $insert, 'value');

		return $result;
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 *
	 * @dataProvider    provider_execute_insert_argument
	 *
	 * @param   array                                       $expected
	 * @param   string|Database_Statement|SQL_Expression    $statement  First argument to the method
	 * @param   mixed                                       $identity   Second argument to the method
	 */
	public function test_execute_insert_argument($expected, $statement, $identity)
	{
		$db = Database::factory();

		$this->assertEquals(
			$expected, $db->execute_insert($statement, $identity)
		);
	}

	public function provider_execute_prepared_command()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(
				'UPDATE '.$table.' SET "value" = 20 WHERE "value" = 65',
				array(),
				3,
			),
			array(
				'UPDATE '.$table.' SET "value" = $1 WHERE "value" = $2',
				array(20, 50),
				1,
			),
			array(
				'SELECT * FROM '.$table.' WHERE "value" = 65',
				array(),
				3,
			),
			array(
				'SELECT * FROM '.$table.' WHERE "value" = $1',
				array(65),
				3,
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_command
	 *
	 * @dataProvider    provider_execute_prepared_command
	 *
	 * @param   string  $statement  Statement to prepare
	 * @param   array   $parameters Second argument to the method
	 * @param   integer $expected
	 */
	public function test_execute_prepared_command($statement, $parameters, $expected)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, $statement);

		$this->assertSame(
			$expected, $db->execute_prepared_command($name, $parameters)
		);
	}

	/**
	 * Throws an exception when executed without the right number of parameters.
	 *
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_command
	 */
	public function test_execute_prepared_command_error()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(
			NULL, 'UPDATE '.$table.' SET "value" = $1 WHERE "value" = $2'
		);

		$this->setExpectedException(
			'Database_Exception', 'parameters', '08P01'
		);

		// Lacking parameters
		$db->execute_prepared_command($name);
	}

	public function provider_execute_prepared_insert()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(
				'INSERT INTO '.$table.' (value) VALUES (65) RETURNING ("id")',
				'id',
				array(),
				array(1, '8'),
			),
			array(
				'INSERT INTO '.$table
				.' (value) VALUES (30), (40) RETURNING ("id")',
				'id',
				array(),
				array(2, '8'),
			),
			array(
				'INSERT INTO '.$table.' (value) VALUES ($1) RETURNING ("id")',
				'id',
				array(75),
				array(1, '8'),
			),
			array(
				'INSERT INTO '.$table
				.' (value) VALUES ($1), ($1) RETURNING ("id")',
				'id',
				array(75),
				array(2, '8'),
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_insert
	 *
	 * @dataProvider    provider_execute_prepared_insert
	 *
	 * @param   string  $statement  Statement to prepare
	 * @param   mixed   $identity   Second argument to the method
	 * @param   array   $parameters Third argument to the method
	 * @param   integer $expected
	 */
	public function test_execute_prepared_insert($statement, $identity, $parameters, $expected)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, $statement);

		$this->assertSame(
			$expected,
			$db->execute_prepared_insert($name, $identity, $parameters)
		);
	}

	/**
	 * Throws an exception when executed without the right number of parameters.
	 *
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_insert
	 */
	public function test_execute_prepared_insert_error()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(
			NULL, 'INSERT INTO '.$table.' (value) VALUES ($1) RETURNING ("id")'
		);

		$this->setExpectedException(
			'Database_Exception', 'parameters', '08P01'
		);

		// Lacking parameters
		$db->execute_prepared_insert($name, 'id');
	}

	public function provider_execute_prepared_query()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(
				'SELECT * FROM '.$table.' WHERE "value" = 60', array(), FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				'SELECT * FROM '.$table.' WHERE "value" = 60', array(), TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),
			array(
				'SELECT * FROM '.$table.' WHERE "value" = $1', array(65), FALSE,
				array(
					array('id' => 5, 'value' => 65),
					array('id' => 6, 'value' => 65),
					array('id' => 7, 'value' => 65),
				),
			),
			array(
				'SELECT * FROM '.$table.' WHERE "value" = $1', array(65), TRUE,
				array(
					(object) array('id' => 5, 'value' => 65),
					(object) array('id' => 6, 'value' => 65),
					(object) array('id' => 7, 'value' => 65),
				),
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_query
	 *
	 * @dataProvider    provider_execute_prepared_query
	 *
	 * @param   string          $statement  Statement to prepare
	 * @param   array           $parameters Second argument to the method
	 * @param   string|boolean  $as_object  Third argument to the method
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
	 * Throws an exception when executed without the right number of parameters.
	 *
	 * @covers  Database_PostgreSQL::_execute_prepared
	 * @covers  Database_PostgreSQL::execute_prepared_query
	 */
	public function test_execute_prepared_query_error()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(
			NULL, 'SELECT * FROM '.$table.' WHERE "value" = $1'
		);

		$this->setExpectedException(
			'Database_Exception', 'parameters', '08P01'
		);

		// Lacking parameters
		$db->execute_prepared_query($name);
	}

	public function provider_execute_query_argument()
	{
		$db = Database::factory();
		$table = new SQL_Table($this->_table);

		return array(
			// String
			array(
				'SELECT * FROM '.$db->quote($table).' WHERE "value" = 60',
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				'SELECT * FROM '.$db->quote($table).' WHERE "value" = 60',
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),

			// Database_Statement
			array(
				new Database_Statement(
					'SELECT * FROM '.$db->quote($table).' WHERE "value" = $1',
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
					'SELECT * FROM '.$db->quote($table).' WHERE "value" = $1',
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
					'SELECT * FROM '.$db->quote($table).' WHERE "value" = 60'
				),
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM '.$db->quote($table).' WHERE "value" = 60'
				),
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE "value" = 60', array($table)
				),
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE "value" = 60', array($table)
				),
				TRUE,
				array(
					(object) array('id' => 3, 'value' => 60),
					(object) array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE "value" = ?', array($table, 60)
				),
				FALSE,
				array(
					array('id' => 3, 'value' => 60),
					array('id' => 4, 'value' => 60),
				),
			),
			array(
				new SQL_Expression(
					'SELECT * FROM ? WHERE "value" = ?', array($table, 60)
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
	 * @covers  Database_PostgreSQL::_execute
	 * @covers  Database_PostgreSQL::_execute_parameters
	 * @covers  Database_PostgreSQL::execute_query
	 *
	 * @dataProvider    provider_execute_query_argument
	 *
	 * @param   string|Database_Statement|SQL_Expression    $statement  First argument to the method
	 * @param   boolean|string                              $as_object  Second argument to the method
	 * @param   integer                                     $expected
	 */
	public function test_execute_query_argument($statement, $as_object, $expected)
	{
		$db = Database::factory();

		$result = $db->execute_query($statement, $as_object);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals($expected, $result->as_array());
	}

	/**
	 * COPY statements will block unless ended.
	 *
	 * @covers  Database_PostgreSQL::_evaluate_query
	 */
	public function test_execute_query_copy()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertNull($db->execute_query('COPY '.$table.' TO STDOUT'));
	}
}
