<?php

require_once dirname(dirname(__FILE__)).'/abstract/database'.EXT;

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
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

	protected $_table = 'kohana_test_table';

	public function provider_alter_table()
	{
		return array(
			array(array(), new Database_PostgreSQL_Alter_Table()),
			array(array('a'), new Database_PostgreSQL_Alter_Table('a')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::alter_table
	 *
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array                           $arguments
	 * @param   Database_PostgreSQL_Alter_Table $expected
	 */
	public function test_alter_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::alter_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	/**
	 * @covers  Database_PostgreSQL::charset
	 */
	public function test_charset()
	{
		$db = Database::factory();

		$this->assertNull($db->charset('utf8'));
	}

	/**
	 * @covers  Database_PostgreSQL::charset
	 */
	public function test_charset_invalid()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'invalid value');

		$db->charset('kohana-invalid-encoding');
	}

	/**
	 * @covers  Database_PostgreSQL::copy_from
	 * @expectedException   Database_Exception
	 */
	public function test_copy_from_error()
	{
		$db = Database::factory();

		$db->copy_from('kohana-nonexistent-table', array("8\t70"));
	}

	/**
	 * @covers  Database_PostgreSQL::copy_to
	 * @expectedException   Database_Exception
	 */
	public function test_copy_to_error()
	{
		$db = Database::factory();

		$db->copy_to('kohana-nonexistent-table');
	}

	public function provider_create_index()
	{
		return array(
			array(array(), new Database_PostgreSQL_Create_Index),
			array(array('a'), new Database_PostgreSQL_Create_Index('a')),
			array(array('a', 'b'), new Database_PostgreSQL_Create_Index('a', 'b')),
			array(array('a', 'b', array('c')), new Database_PostgreSQL_Create_Index('a', 'b', array('c'))),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::create_index
	 *
	 * @dataProvider    provider_create_index
	 *
	 * @param   array                               $arguments
	 * @param   Database_PostgreSQL_Create_Index    $expected
	 */
	public function test_create_index($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::create_index', $arguments);
		$this->assertEquals($expected, $statement);
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
		$db = Database::factory();

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
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table);

		$this->assertNotEquals('', $name, 'Returns a generated name');

		$result = $db->execute_query("SELECT * FROM pg_prepared_statements WHERE name = '$name'");
		$this->assertSame(1, $result->count(), 'Created successfully');
		$this->assertSame('f', $result->get('from_sql'), 'Definitely programmatic');

		$this->assertSame('asdf', $db->prepare('asdf', 'SELECT * FROM '.$table));
	}

	/**
	 * @covers  Database_PostgreSQL::prepare
	 */
	public function test_prepare_invalid()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'syntax error', 42601);

		$db->prepare(NULL, 'kohana-invalid-sql');
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
			array(
				'DELETE FROM $table WHERE "value" IN (:list)', array(':list' => array(60, 70, 80)),
				'DELETE FROM $table WHERE "value" IN ($1, $2, $3)', array(60, 70, 80),
			),
			array(
				'DELETE FROM $table WHERE "value" IN (:list) OR "value" IN (:list)', array(':list' => array(60, 70, 80)),
				'DELETE FROM $table WHERE "value" IN ($1, $2, $3) OR "value" IN ($1, $2, $3)', array(60, 70, 80),
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
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$input_sql = str_replace('$table', $table, $input_sql);
		$expected_sql = str_replace('$table', $table, $expected_sql);

		$statement = $db->prepare_statement(
			new SQL_Expression($input_sql, $input_params)
		);

		$this->assertType('Database_PostgreSQL_Statement', $statement);
		$this->assertSame($expected_sql, $statement->statement);
		$this->assertSame($expected_params, $statement->parameters());
	}

	/**
	 * @covers  Database_PostgreSQL::quote
	 */
	public function test_quote_binary()
	{
		$db = Database::factory();
		$binary = new Database_Binary("\200\0\350");

		$this->assertSame("'\\\\200\\\\000\\\\350'", $db->quote($binary));
	}

	/**
	 * @covers  Database_PostgreSQL::quote_expression
	 */
	public function test_quote_expression()
	{
		$db = Database::factory();
		$expression = new SQL_Expression("SELECT :value::interval, 'yes':::type", array(':value' => '1 week', ':type' => new SQL_Expression('boolean')));

		$this->assertSame("SELECT '1 week'::interval, 'yes'::boolean", $db->quote_expression($expression));
	}

	/**
	 * @covers  Database_PostgreSQL::quote_expression
	 */
	public function test_quote_expression_placeholder_first()
	{
		$db = Database::factory();

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
			array("multiple\nlines", "'multiple\nlines'"),
			array("single'quote", "'single''quote'"),
			array("double\"quote", "'double\"quote'"),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::escape
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
