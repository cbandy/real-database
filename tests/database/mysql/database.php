<?php

require_once dirname(dirname(__FILE__)).'/abstract/database'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Database_Test extends Database_Abstract_Database_Test
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('MySQL extension not installed');

		if ( ! Database::factory() instanceof Database_MySQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for MySQL');
	}

	protected $_table = 'kohana_test_table';

	public function provider_alter_table()
	{
		return array(
			array(array(), new Database_MySQL_Alter_Table()),
			array(array('a'), new Database_MySQL_Alter_Table('a')),
		);
	}

	/**
	 * @covers  Database_MySQL::alter_table
	 *
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_Alter_Table  $expected
	 */
	public function test_alter_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database_MySQL::alter_table', $arguments);
		$this->assertEquals($expected, $statement);
	}


	public function provider_create_index()
	{
		return array(
			array(array(), new Database_MySQL_Create_Index),
			array(array('a'), new Database_MySQL_Create_Index('a')),
			array(array('a', 'b'), new Database_MySQL_Create_Index('a', 'b')),
			array(array('a', 'b', array('c')), new Database_MySQL_Create_Index('a', 'b', array('c'))),
		);
	}

	/**
	 * @covers  Database_MySQL::create_index
	 *
	 * @dataProvider    provider_create_index
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_Create_Index $expected
	 */
	public function test_create_index($arguments, $expected)
	{
		$statement = call_user_func_array('Database_MySQL::create_index', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_create_table()
	{
		return array(
			array(array(), new Database_MySQL_Create_Table),
			array(array('a'), new Database_MySQL_Create_Table('a')),
		);
	}

	/**
	 * @covers  Database_MySQL::create_table
	 *
	 * @dataProvider    provider_create_table
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_Create_Table $expected
	 */
	public function test_create_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database_MySQL::create_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_create_view()
	{
		return array(
			array(array(), new Database_MySQL_Create_View),
			array(array('a'), new Database_MySQL_Create_View('a')),
			array(array('a', new SQL_Expression('b')), new Database_MySQL_Create_View('a', new SQL_Expression('b'))),
		);
	}

	/**
	 * @covers  Database_MySQL::create_view
	 *
	 * @dataProvider    provider_create_view
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_Create_View  $expected
	 */
	public function test_create_view($arguments, $expected)
	{
		$statement = call_user_func_array('Database_MySQL::create_view', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_datatype()
	{
		return array
		(
			array('tinyint unsigned zerofill', NULL, array('type' => 'integer', 'min' => '0', 'max' => '255')),
			array('point', NULL, array('type' => 'binary')),
		);
	}

	/**
	 * @covers  Database_MySQL::datatype
	 * @dataProvider provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	/**
	 * @covers  Database_MySQL::ddl_column
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array   $arguments
	 */
	public function test_ddl_column($arguments)
	{
		$this->_test_method_type('ddl_column', $arguments, 'Database_MySQL_DDL_Column');
	}

	public function provider_execute_command_error()
	{
		return array(
			array('kohana invalid command'),
			array(new SQL_Expression('kohana invalid command')),
		);
	}

	/**
	 * @covers  Database_MySQL::_execute
	 * @covers  Database_MySQL::execute_command
	 *
	 * @dataProvider  provider_execute_command_error
	 *
	 * @param   string|SQL_Expression   $value  Bad SQL statement
	 */
	public function test_execute_command_error($value)
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'SQL syntax', 1064);

		$db->execute_command($value);
	}

	/**
	 * @covers  Database_MySQL::execute_insert
	 */
	public function test_execute_insert_empty_disconnected()
	{
		$db = Database::factory();

		$result = $db->execute_insert('', NULL);

		$this->assertSame(array(0,0), $result);
	}

	/**
	 * @covers  Database_MySQL::execute_insert
	 */
	public function test_execute_insert_empty_first()
	{
		$db = Database::factory();
		$db->connect();

		$result = $db->execute_insert('', NULL);

		$this->assertSame(array(0,0), $result, 'No prior INSERT');
	}

	public function provider_execute_query_error()
	{
		return array
		(
			array('kohana invalid query'),
			array(new SQL_Expression('kohana invalid query')),
		);
	}

	/**
	 * @covers  Database_MySQL::_execute
	 * @covers  Database_MySQL::execute_query
	 *
	 * @dataProvider  provider_execute_query_error
	 *
	 * @param   string|SQL_Expression   $value  Bad SQL statement
	 */
	public function test_execute_query_error($value)
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'SQL syntax', 1064);

		$db->execute_query($value);
	}

	/**
	 * @covers  Database_MySQL::execute_query
	 */
	public function test_execute_query_expression()
	{
		$db = Database::factory();

		$result = $db->execute_query(new SQL_Expression('SELECT ?', array(1)));

		$this->assertType('Database_MySQL_Result', $result);
		$this->assertSame(1, count($result));
	}

	public function provider_prepare_statement()
	{
		return array(
			array(
				new Database_Statement('SELECT ?', array(60)),
				'kohana_d41673f80456e40552a6a2e81e99e85efa487721',
				'SELECT ?',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ?', array(
					new SQL_Table($this->_table)
				)),
				'kohana_e27e457b646db1d9aa7f6b5a2c014408d5f43c73',
				'DELETE FROM $table',
				array()
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE ?', array(
					new SQL_Table($this->_table),
					new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_84d89cb534a118f8b879af39a27a27e06a62fcb5',
				'DELETE FROM $table WHERE `value` = ?',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE :a', array(
					new SQL_Table($this->_table),
					':a' => new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_84d89cb534a118f8b879af39a27a27e06a62fcb5',
				'DELETE FROM $table WHERE `value` = ?',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE :a AND :a', array(
					new SQL_Table($this->_table),
					':a' => new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_7b91fbba52445c2254274e800d93f907cb11b33c',
				'DELETE FROM $table WHERE `value` = ? AND `value` = ?',
				array(60, 60)
			),
		);
	}

	/**
	 * @covers  Database_MySQL::prepare_statement
	 *
	 * @dataProvider    provider_prepare_statement
	 *
	 * @param   Database_Statement|SQL_Expression   $argument   Argument to the method
	 * @param   string  $name   Expected name
	 * @param   string  $sql    Expected sql
	 * @param   array   $params Expected parameters
	 */
	public function test_prepare_statement($argument, $name, $sql, $params)
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$expected = new Database_MySQL_Statement($db, $name, $params);
		$expected->statement = strtr($sql, array('$table' => $table));

		$this->assertEquals($expected, $db->prepare_statement($argument));
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
			array("multiple\nlines", "'multiple\\nlines'"),
			array("single'quote", "'single\\'quote'"),
			array("double\"quote", "'double\\\"quote'"),
		);
	}

	/**
	 * @covers  Database_MySQL::escape
	 * @covers  Database_MySQL::quote_literal
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
	 * @covers  Database_MySQL::connect
	 * @covers  Database_MySQL::disconnect
	 */
	public function test_reconnect()
	{
		$db = Database::factory();

		$db->connect();
		$db->disconnect();
		$db->connect();
	}

	public function provider_table_prefix()
	{
		return array
		(
			array('asdf', 'asdf'),
			array(NULL, ''),
		);
	}

	/**
	 * @covers  Database_MySQL::__construct
	 * @covers  Database_MySQL::table_prefix
	 * @dataProvider    provider_table_prefix
	 *
	 * @param   string  $value
	 * @param   string  $expected
	 */
	public function test_table_prefix($value, $expected)
	{
		$db = new Database_MySQL('name', array(
			'connection' => array(
				'hostname' => '',
				'username' => '',
				'password' => '',
			),
			'table_prefix' => $value,
		));

		$this->assertSame($expected, $db->table_prefix());
	}
}
