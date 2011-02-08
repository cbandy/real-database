<?php

require_once dirname(dirname(__FILE__)).'/abstract/database'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Database_Test extends Database_Abstract_Database_Test
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();
		$table = $db->quote_table($this->_table);

		$db->execute_command(implode('; ', array(
			'CREATE TEMPORARY TABLE '.$table.' ("id" bigserial PRIMARY KEY, "value" integer)',
			'INSERT INTO '.$table.' ("value") VALUES (50)',
			'INSERT INTO '.$table.' ("value") VALUES (55)',
			'INSERT INTO '.$table.' ("value") VALUES (60)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
		)));
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @covers  Database_PostgreSQL::alter
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array   $arguments
	 */
	public function test_alter_table($arguments)
	{
		$this->_test_method_type('alter', $arguments, 'Database_PostgreSQL_Alter_Table');
	}

	/**
	 * @covers  Database_PostgreSQL::copy_from
	 */
	public function test_copy_from()
	{
		$db = $this->sharedFixture;
		$db->copy_from($this->_table, array("8\t\\N", "9\t75"));

		$this->assertEquals(array(
			array('id' => 1, 'value' => 50),
			array('id' => 2, 'value' => 55),
			array('id' => 3, 'value' => 60),
			array('id' => 4, 'value' => 65),
			array('id' => 5, 'value' => 65),
			array('id' => 8, 'value' => NULL),
			array('id' => 9, 'value' => 75),
		), $db->execute_query('SELECT * FROM '.$db->quote_table($this->_table).' ORDER BY "id"')->as_array());
	}

	/**
	 * @covers  Database_PostgreSQL::copy_from
	 * @expectedException   Database_Exception
	 */
	public function test_copy_from_error()
	{
		$db = $this->sharedFixture;

		$db->copy_from('kohana-nonexistent-table', array("8\t70"));
	}

	/**
	 * @covers  Database_PostgreSQL::copy_to
	 */
	public function test_copy_to()
	{
		$db = $this->sharedFixture;
		$db->execute_command('INSERT INTO '.$db->quote_table($this->_table).' ("value") VALUES (NULL)');

		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t65\n", "5\t65\n", "6\t\\N\n"), $db->copy_to($this->_table));
	}

	/**
	 * @covers  Database_PostgreSQL::copy_to
	 * @expectedException   Database_Exception
	 */
	public function test_copy_to_error()
	{
		$db = $this->sharedFixture;

		$db->copy_to('kohana-nonexistent-table');
	}

	/**
	 * @covers  Database_PostgreSQL::create
	 * @dataProvider    provider_create_index
	 *
	 * @param   array   $arguments
	 */
	public function test_create_index($arguments)
	{
		$this->_test_method_type('create', $arguments, 'Database_PostgreSQL_Create_Index');
	}

	/**
	 * @covers  Database_PostgreSQL::create
	 * @dataProvider    provider_create_table
	 *
	 * @param   array   $arguments
	 */
	public function test_create_table($arguments)
	{
		return parent::test_create_table($arguments);
	}

	/**
	 * @covers  Database_PostgreSQL::create
	 * @dataProvider    provider_create_view
	 *
	 * @param   array   $arguments
	 */
	public function test_create_view($arguments)
	{
		return parent::test_create_view($arguments);
	}

	public function provider_datatype()
	{
		return array
		(
			array('money', 'exact', TRUE),
			array('bytea', NULL, array('type' => 'binary')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::datatype
	 * @dataProvider provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	/**
	 * @covers  Database_PostgreSQL::ddl_column
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array   $arguments
	 */
	public function test_ddl_column($arguments)
	{
		$this->_test_method_type('ddl_column', $arguments, 'Database_PostgreSQL_DDL_Column');
	}

	/**
	 * @covers  Database_PostgreSQL::delete
	 * @dataProvider    provider_delete
	 *
	 * @param   array   $arguments
	 */
	public function test_delete($arguments)
	{
		$this->_test_method_type('delete', $arguments, 'Database_PostgreSQL_Delete');
	}

	/**
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_command_expression()
	{
		$db = $this->sharedFixture;

		$this->assertSame(5, $db->execute_command(new SQL_Expression('DELETE FROM ?', array(new SQL_Table($this->_table)))));
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_command
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_command_query()
	{
		$db = $this->sharedFixture;

		$this->assertSame(5, $db->execute_command('SELECT * FROM '.$db->quote_table($this->_table)), 'Number of returned rows');
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_command
	 * @covers  Database_PostgreSQL::execute_command
	 */
	public function test_execute_compound_command()
	{
		$db = $this->sharedFixture;
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

	public function test_execute_copy()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertSame(0, $db->execute_command('COPY '.$table.' TO STDOUT'));

		$this->assertNull($db->execute_query('COPY '.$table.' TO STDOUT'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 */
	public function test_execute_insert()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$sql = 'INSERT INTO '.$table.' (value) VALUES (65)';

		$this->assertEquals(array(1,6), $db->execute_insert($sql, 'id'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 */
	public function test_execute_insert_expression()
	{
		$db = $this->sharedFixture;

		$sql = new SQL_Expression(
			'INSERT INTO ? (value) VALUES (65)',
			array(new SQL_Table($this->_table))
		);

		$this->assertEquals(array(1,6), $db->execute_insert($sql, 'id'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 */
	public function test_execute_insert_ireturning()
	{
		$db = $this->sharedFixture;

		$delete = new Database_PostgreSQL_Delete($this->_table);
		$delete->where('value', '=', 60)->returning(array('id'));

		$this->assertEquals(array(1,3), $db->execute_insert($delete, 'id'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_prepared_command
	 */
	public function test_execute_prepared_command()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'UPDATE '.$table.' SET "value" = 20 WHERE "value" = 65');

		$this->assertSame(2, $db->execute_prepared_command($name));

		$name = $db->prepare(NULL, 'UPDATE '.$table.' SET "value" = $1 WHERE "value" = $2');

		$this->assertSame(1, $db->execute_prepared_command($name, array(20, 50)));
		$this->assertSame(3, $db->execute_prepared_command($name, array(30, 20)));

		try
		{
			$db->execute_prepared_command($name);
			$this->fail('Executing without the required parameters should raise a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	/**
	 * @covers  Database_PostgreSQL::execute_prepared_insert
	 */
	public function test_execute_prepared_insert()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'INSERT INTO '.$table.' ("value") VALUES (10) RETURNING "id"');

		$this->assertSame(array(1, '6'), $db->execute_prepared_insert($name, 'id'));

		$name = $db->prepare(NULL, 'INSERT INTO '.$table.' ("value") VALUES ($1) RETURNING "id"');

		$this->assertSame(array(1, '7'), $db->execute_prepared_insert($name, 'id', array(20)));
		$this->assertSame(array(1, '8'), $db->execute_prepared_insert($name, 'id', array(30)));

		try
		{
			$db->execute_prepared_insert($name, 'id');
			$this->fail('Executing without the required parameters should raise a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	/**
	 * @covers  Database_PostgreSQL::execute_prepared_query
	 */
	public function test_execute_prepared_query()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table.' WHERE "value" = $1');

		$result = $db->execute_prepared_query($name, array(60));

		$this->assertType('Database_PostgreSQL_Result', $result, 'Parameters (1)');
		$this->assertSame(1, $result->count(), 'Parameters (1)');
		$this->assertEquals(60, $result->get('value'));

		$result = $db->execute_prepared_query($name, array(50));

		$this->assertType('Database_PostgreSQL_Result', $result, 'Parameters (2)');
		$this->assertSame(1, $result->count(), 'Parameters (2)');
		$this->assertEquals(50, $result->get('value'));

		try
		{
			$db->execute_prepared_query($name);
			$this->fail('Executing without the required parameters should raise a Database_Exception');
		}
		catch (Database_Exception $e) {}

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table);

		$result = $db->execute_prepared_query($name);

		$this->assertType('Database_PostgreSQL_Result', $result, 'No parameters');
		$this->assertType('array', $result->current(), 'No parameters');

		$result = $db->execute_prepared_query($name, array(), FALSE);

		$this->assertType('Database_PostgreSQL_Result', $result, 'Result type (FALSE)');
		$this->assertType('array', $result->current(), 'Result type (FALSE)');

		$result = $db->execute_prepared_query($name, array(), TRUE);

		$this->assertType('Database_PostgreSQL_Result', $result, 'Result type (TRUE)');
		$this->assertType('stdClass', $result->current(), 'Result type (TRUE)');

		$result = $db->execute_prepared_query($name, array(), 'Database_PostgreSQL_Database_Test_Class');

		$this->assertType('Database_PostgreSQL_Result', $result, 'Result type (Database_PostgreSQL_Database_Test_Class)');
		$this->assertType('Database_PostgreSQL_Database_Test_Class', $result->current(), 'Result type (Database_PostgreSQL_Database_Test_Class)');
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

	/**
	 * @covers  Database_PostgreSQL::insert
	 * @dataProvider    provider_insert
	 *
	 * @param   array   $arguments
	 */
	public function test_insert($arguments)
	{
		$this->_test_method_type('insert', $arguments, 'Database_PostgreSQL_Insert');
	}

	/**
	 * @covers  Database_PostgreSQL::prepare
	 */
	public function test_prepare()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table);

		$this->assertNotEquals('', $name, 'Returns a generated name');

		$result = $db->execute_query("SELECT * FROM pg_prepared_statements WHERE name = '$name'");
		$this->assertSame(1, $result->count(), 'Created successfully');
		$this->assertSame('f', $result->get('from_sql'), 'Definitely programmatic');

		$this->assertSame('asdf', $db->prepare('asdf', 'SELECT * FROM '.$table));
	}

	public function provider_prepare_statement()
	{
		return array
		(
			array(
				'DELETE FROM $table', array(),
				'DELETE FROM $table', array(),
			),
			array(
				'DELETE FROM ?', array(new SQL_Table($this->_table)),
				'DELETE FROM $table', array(),
			),
			array(
				'DELETE FROM :table', array(':table' => new SQL_Table($this->_table)),
				'DELETE FROM $table', array(),
			),
			array(
				'DELETE FROM $table WHERE ?', array(new SQL_Conditions(new SQL_Column('value'), '=', 60)),
				'DELETE FROM $table WHERE "value" = $1', array(60),
			),
			array(
				'DELETE FROM $table WHERE :condition', array(':condition' => new SQL_Conditions(new SQL_Column('value'), '=', 60)),
				'DELETE FROM $table WHERE "value" = $1', array(60),
			),
			array(
				'DELETE FROM $table WHERE :condition AND :condition', array(':condition' => new SQL_Conditions(new SQL_Column('value'), '=', 60)),
				'DELETE FROM $table WHERE "value" = $1 AND "value" = $1', array(60),
			),
			array(
				'DELETE FROM $table WHERE "value" = ?', array(60),
				'DELETE FROM $table WHERE "value" = $1', array(60),
			),
			array(
				'DELETE FROM $table WHERE "value" = :value', array(':value' => 60),
				'DELETE FROM $table WHERE "value" = $1', array(60),
			),
			array(
				'DELETE FROM $table WHERE "value" = :value AND "value" = :value', array(':value' => 60),
				'DELETE FROM $table WHERE "value" = $1 AND "value" = $1', array(60),
			),
			array(
				'DELETE FROM $table WHERE "value" IN (?)', array(array(60, 70, 80)),
				'DELETE FROM $table WHERE "value" IN ($1, $2, $3)', array(60, 70, 80),
			),
			array(
				'DELETE FROM $table WHERE "value" IN (?)', array(array(60, 70, array(80))),
				'DELETE FROM $table WHERE "value" IN ($1, $2, $3)', array(60, 70, 80),
			),
			array(
				'DELETE FROM $table WHERE "value" IN (?)', array(array(60, new SQL_Expression(':name', array(':name' => 70)), 80)),
				'DELETE FROM $table WHERE "value" IN ($1, $2, $3)', array(60, 70, 80),
			),
			array(
				'DELETE FROM $table WHERE "value" IN (?)', array(array(new SQL_Identifier('value'), 70, 80)),
				'DELETE FROM $table WHERE "value" IN ("value", $1, $2)', array(70, 80),
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_parse
	 * @covers  Database_PostgreSQL::_parse_array
	 * @covers  Database_PostgreSQL::prepare_statement
	 * @dataProvider    provider_prepare_statement
	 */
	public function test_prepare_statement($input_sql, $input_params, $expected_sql, $expected_params)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$input_sql = str_replace('$table', $table, $input_sql);
		$expected_sql = str_replace('$table', $table, $expected_sql);

		$statement = $db->prepare_statement(
			new SQL_Expression($input_sql, $input_params)
		);

		$this->assertType('Database_PostgreSQL_Statement', $statement);
		$this->assertSame($expected_sql, (string) $statement);
		$this->assertSame($expected_params, $statement->parameters);
	}

	public function test_quote_binary()
	{
		$db = $this->sharedFixture;
		$binary = new Database_Binary("\200\0\350");

		$this->assertSame("'\\\\200\\\\000\\\\350'", $db->quote($binary));
	}

	/**
	 * @covers  Database_PostgreSQL::quote_expression
	 */
	public function test_quote_expression()
	{
		$db = $this->sharedFixture;
		$expression = new SQL_Expression("SELECT :value::interval, 'yes':::type", array(':value' => '1 week', ':type' => new SQL_Expression('boolean')));

		$this->assertSame("SELECT '1 week'::interval, 'yes'::boolean", $db->quote_expression($expression));
	}

	/**
	 * @covers  Database_PostgreSQL::quote_expression
	 */
	public function test_quote_expression_placeholder_first()
	{
		$db = $this->sharedFixture;

		$this->assertSame('1', $db->quote_expression(new SQL_Expression('?', array(1))));
		$this->assertSame('2', $db->quote_expression(new SQL_Expression(':param', array(':param' => 2))));
	}

	public function provider_quote_literal()
	{
		return array
		(
			array(NULL, 'NULL'),
			array(FALSE, "'0'"),
			array(TRUE, "'1'"),

			array(0, '0'),
			array(-1, '-1'),
			array(51678, '51678'),
			array(12.345, '12.345000'),

			array('string', "'string'"),
			array("multiline\nstring", "'multiline\nstring'"),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::quote_literal
	 * @dataProvider    provider_quote_literal
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_quote_literal($value, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->quote_literal($value));
	}

	public function test_savepoint_transactions()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$delete = 'DELETE FROM '.$table;
		$select = 'SELECT * FROM '.$table;

		$this->assertSame(5, $db->execute_query($select)->count(), 'Initial');

		$db->begin();
		$db->execute_command($delete.' WHERE "value" = 65');

		$this->assertSame(3, $db->execute_query($select)->count(), 'Deleted 65');

		$this->assertNull($db->savepoint('test_savepoint'));

		$db->execute_command($delete.' WHERE "value" = 55');

		$this->assertSame(2, $db->execute_query($select)->count(), 'Deleted 55');

		$this->assertNull($db->rollback('test_savepoint'));

		$this->assertSame(3, $db->execute_query($select)->count(), 'Rollback 55');

		$this->assertNull($db->rollback());

		$this->assertSame(5, $db->execute_query($select)->count(), 'Rollback 65');
	}

	/**
	 * @covers  Database_PostgreSQL::select
	 * @dataProvider    provider_select
	 *
	 * @param   array   $arguments
	 */
	public function test_select($arguments)
	{
		$this->_test_method_type('select', $arguments, 'Database_PostgreSQL_Select');
	}

	/**
	 * @covers  Database_PostgreSQL::update
	 * @dataProvider    provider_update
	 *
	 * @param   array   $arguments
	 */
	public function test_update($arguments)
	{
		$this->_test_method_type('update', $arguments, 'Database_PostgreSQL_Update');
	}
}

class Database_PostgreSQL_Database_Test_Class {}
