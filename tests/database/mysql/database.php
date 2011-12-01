<?php

/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Database_Test extends PHPUnit_Framework_TestCase
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
			array(array(), new Database_MySQL_DDL_Alter_Table),
			array(array('a'), new Database_MySQL_DDL_Alter_Table('a')),
		);
	}

	/**
	 * @covers  Database_MySQL::alter_table
	 *
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array                           $arguments
	 * @param   Database_MySQL_DDL_Alter_Table  $expected
	 */
	public function test_alter_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database_MySQL::alter_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	/**
	 * @covers  Database_MySQL::charset
	 */
	public function test_charset()
	{
		$db = Database::factory();

		$this->assertNull($db->charset('ascii'));
	}

	/**
	 * @covers  Database_MySQL::charset
	 */
	public function test_charset_error()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'character set', 2019);

		$this->assertNull($db->charset('kohana-invalid-charset'));
	}

	public function provider_create_index()
	{
		return array(
			array(array(), new Database_MySQL_DDL_Create_Index),
			array(array('a'), new Database_MySQL_DDL_Create_Index('a')),
			array(array('a', 'b'), new Database_MySQL_DDL_Create_Index('a', 'b')),
			array(array('a', 'b', array('c')), new Database_MySQL_DDL_Create_Index('a', 'b', array('c'))),
		);
	}

	/**
	 * @covers  Database_MySQL::create_index
	 *
	 * @dataProvider    provider_create_index
	 *
	 * @param   array                           $arguments
	 * @param   Database_MySQL_DDL_Create_Index $expected
	 */
	public function test_create_index($arguments, $expected)
	{
		$statement = call_user_func_array('Database_MySQL::create_index', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_create_table()
	{
		return array(
			array(array(), new Database_MySQL_DDL_Create_Table),
			array(array('a'), new Database_MySQL_DDL_Create_Table('a')),
		);
	}

	/**
	 * @covers  Database_MySQL::create_table
	 *
	 * @dataProvider    provider_create_table
	 *
	 * @param   array                           $arguments
	 * @param   Database_MySQL_DDL_Create_Table $expected
	 */
	public function test_create_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database_MySQL::create_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_create_view()
	{
		return array(
			array(array(), new Database_MySQL_DDL_Create_View),
			array(array('a'), new Database_MySQL_DDL_Create_View('a')),
			array(array('a', new SQL_Expression('b')), new Database_MySQL_DDL_Create_View('a', new SQL_Expression('b'))),
		);
	}

	/**
	 * @covers  Database_MySQL::create_view
	 *
	 * @dataProvider    provider_create_view
	 *
	 * @param   array                           $arguments
	 * @param   Database_MySQL_DDL_Create_View  $expected
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

	public function provider_ddl_column()
	{
		return array(
			array(array(), new Database_MySQL_DDL_Column),
			array(array('a'), new Database_MySQL_DDL_Column('a')),
			array(array('a', 'b'), new Database_MySQL_DDL_Column('a', 'b')),
		);
	}

	/**
	 * @covers  Database_MySQL::ddl_column
	 *
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_DDL_Column   $expected
	 */
	public function test_ddl_column($arguments, $expected)
	{
		$column = call_user_func_array('Database_MySQL::ddl_column', $arguments);
		$this->assertEquals($expected, $column);
	}

	public function provider_ddl_enum()
	{
		return array(
			array(array(), new Database_MySQL_DDL_Enum),
			array(array(array()), new Database_MySQL_DDL_Enum(array())),
			array(array(array('a')), new Database_MySQL_DDL_Enum(array('a'))),
			array(array(array('a', 'b')), new Database_MySQL_DDL_Enum(array('a', 'b'))),
		);
	}

	/**
	 * @covers  Database_MySQL::ddl_enum
	 *
	 * @dataProvider    provider_ddl_enum
	 *
	 * @param   array                   $arguments
	 * @param   Database_MySQL_DDL_Enum $expected
	 */
	public function test_ddl_enum($arguments, $expected)
	{
		$column = call_user_func_array('Database_MySQL::ddl_enum', $arguments);
		$this->assertEquals($expected, $column);
	}

	public function provider_ddl_set()
	{
		return array(
			array(array(), new Database_MySQL_DDL_Set),
			array(array(array()), new Database_MySQL_DDL_Set(array())),
			array(array(array('a')), new Database_MySQL_DDL_Set(array('a'))),
			array(array(array('a', 'b')), new Database_MySQL_DDL_Set(array('a', 'b'))),
		);
	}

	/**
	 * @covers  Database_MySQL::ddl_set
	 *
	 * @dataProvider    provider_ddl_set
	 *
	 * @param   array                   $arguments
	 * @param   Database_MySQL_DDL_Set  $expected
	 */
	public function test_ddl_set($arguments, $expected)
	{
		$column = call_user_func_array('Database_MySQL::ddl_set', $arguments);
		$this->assertEquals($expected, $column);
	}

	public function provider_delete()
	{
		return array(
			array(array(), new Database_MySQL_DML_Delete),
			array(array('a'), new Database_MySQL_DML_Delete('a')),
			array(array('a', 'b'), new Database_MySQL_DML_Delete('a', 'b')),
		);
	}

	/**
	 * @covers  Database_MySQL::delete
	 *
	 * @dataProvider    provider_delete
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_DML_Delete   $expected
	 */
	public function test_delete($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database_MySQL::delete', $arguments)
		);
	}

	public function provider_execute_command_empty()
	{
		return array(
			array(''),
			array(new SQL_Expression('')),
		);
	}

	/**
	 * @covers  Database_MySQL::execute_command
	 *
	 * @dataProvider  provider_execute_command_empty
	 *
	 * @param   string|SQL_Expression   $value  Empty statement
	 */
	public function test_execute_command_empty($value)
	{
		$db = Database::factory();

		$this->assertSame(0, $db->execute_command($value));
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

	public function provider_execute_query_empty()
	{
		return array(
			array(''),
			array(new SQL_Expression('')),
		);
	}

	/**
	 * @covers  Database::execute_query
	 *
	 * @dataProvider  provider_execute_query_empty
	 *
	 * @param   string|SQL_Expression   $value  Empty statement
	 */
	public function test_execute_query_empty($value)
	{
		$db = Database::factory();

		$this->assertNull($db->execute_query($value));
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

		$this->assertInstanceOf('Database_MySQL_Result', $result);
		$this->assertSame(1, count($result));
	}

	public function provider_identical()
	{
		return array(
			array(array('a', '=', 'b'), new Database_MySQL_Identical('a', '=', 'b')),
			array(array('a', '<>', 'b'), new Database_MySQL_Identical('a', '<>', 'b')),
		);
	}

	/**
	 * @covers  Database_MySQL::identical
	 *
	 * @dataProvider    provider_identical
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_Identical    $expected
	 */
	public function test_identical($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database_MySQL::identical', $arguments)
		);
	}

	/**
	 * @covers  Database_MySQL::ping
	 */
	public function test_ping_initial()
	{
		$db = Database::factory();

		$this->assertFalse($db->ping());
	}

	/**
	 * @covers  Database_MySQL::ping
	 */
	public function test_ping_connected()
	{
		$db = Database::factory();
		$db->connect();

		$this->assertTrue($db->ping());
	}

	/**
	 * @covers  Database_MySQL::ping
	 */
	public function test_ping_disconnected()
	{
		$db = Database::factory();
		$db->connect();
		$db->disconnect();

		$this->assertFalse($db->ping());
	}

	public function provider_prepare()
	{
		return array(
			array(NULL, 'SELECT ?', 'kohana_d41673f80456e40552a6a2e81e99e85efa487721'),
			array('kohana-stmt', 'SELECT ?', 'kohana-stmt'),
		);
	}

	/**
	 * @covers  Database_MySQL::prepare
	 *
	 * @dataProvider    provider_prepare
	 *
	 * @param   string  $name       First argument to the method
	 * @param   string  $statement  Second argument to the method
	 * @param   string  $expected   Expected
	 */
	public function test_prepare($name, $statement, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->prepare($name, $statement));
	}

	public function provider_prepare_statement()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

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
				'DELETE FROM '.$table,
				array()
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE ?', array(
					new SQL_Table($this->_table),
					new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_84d89cb534a118f8b879af39a27a27e06a62fcb5',
				'DELETE FROM '.$table.' WHERE `value` = ?',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE :a', array(
					new SQL_Table($this->_table),
					':a' => new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_84d89cb534a118f8b879af39a27a27e06a62fcb5',
				'DELETE FROM '.$table.' WHERE `value` = ?',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE :a AND :a', array(
					new SQL_Table($this->_table),
					':a' => new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_7b91fbba52445c2254274e800d93f907cb11b33c',
				'DELETE FROM '.$table.' WHERE `value` = ? AND `value` = ?',
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

		$expected = new Database_MySQL_Statement($db, $name, $params);
		$expected->statement = $sql;

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

			array(new Database_Binary("\x0"), "'\\0'"),
		);
	}

	/**
	 * @covers  Database_MySQL::escape_literal
	 * @covers  Database_MySQL::quote_literal
	 *
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

	public function provider_select()
	{
		return array(
			array(array(), new Database_MySQL_DML_Select),
			array(array(array('a' => 'b')), new Database_MySQL_DML_Select(array('a' => 'b'))),
		);
	}

	/**
	 * @covers  Database_MySQL::select
	 *
	 * @dataProvider    provider_select
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_DML_Select   $expected
	 */
	public function test_select($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database_MySQL::select', $arguments)
		);
	}

	public function provider_update()
	{
		return array(
			array(array(), new Database_MySQL_DML_Update),
			array(array('a'), new Database_MySQL_DML_Update('a')),
			array(array('a', 'b'), new Database_MySQL_DML_Update('a', 'b')),
			array(array('a', 'b', array('c' => 'd')), new Database_MySQL_DML_Update('a', 'b', array('c' => 'd'))),
		);
	}

	/**
	 * @covers  Database_MySQL::update
	 *
	 * @dataProvider    provider_update
	 *
	 * @param   array                       $arguments
	 * @param   Database_MySQL_DML_Update   $expected
	 */
	public function test_update($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database_MySQL::update', $arguments)
		);
	}
}
