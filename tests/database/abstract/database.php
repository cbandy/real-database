<?php

require_once dirname(__FILE__).'/testcase'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
abstract class Database_Abstract_Database_Test extends Database_Abstract_TestCase
{
	/**
	 * @param   string  $method     Database method to call
	 * @param   array   $arguments  Method arguments
	 * @param   string  $expected   Expected type
	 */
	protected function _test_method_type($method, $arguments, $expected)
	{
		$db = $this->_database();

		$result = call_user_func_array(array($db, $method), $arguments);

		$this->assertType($expected, $result);
	}

	public function provider_alter_table()
	{
		return array
		(
			array(array('table')),
			array(array('table', 'a')),
		);
	}

	/**
	 * @covers  Database::alter
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array   $arguments
	 */
	public function test_alter_table($arguments)
	{
		$this->_test_method_type('alter', $arguments, 'SQL_DDL_Alter_Table');
	}

	public function provider_binary()
	{
		return array
		(
			array(array('a')),
		);
	}

	/**
	 * @covers  Database::binary
	 * @dataProvider    provider_binary
	 *
	 * @param   array   $arguments
	 */
	public function test_binary($arguments)
	{
		$this->_test_method_type('binary', $arguments, 'Database_Binary');
	}

	/**
	 * @covers  Database::column
	 */
	public function test_column()
	{
		$this->_test_method_type('column', array('a'), 'SQL_Column');
	}

	public function provider_conditions()
	{
		return array
		(
			array(array()),
			array(array('a')),
			array(array('a', '=')),
			array(array('a', '=', 'b')),
		);
	}

	/**
	 * @covers  Database::conditions
	 * @dataProvider    provider_conditions
	 *
	 * @param   array   $arguments
	 */
	public function test_condtitions($arguments)
	{
		$this->_test_method_type('conditions', $arguments, 'SQL_Conditions');
	}

	public function provider_create_index()
	{
		return array
		(
			array(array('index')),
			array(array('index', 'a')),
		);
	}

	/**
	 * @covers  Database::create
	 * @dataProvider    provider_create_index
	 *
	 * @param   array   $arguments
	 */
	public function test_create_index($arguments)
	{
		$this->_test_method_type('create', $arguments, 'SQL_DDL_Create_Index');
	}

	public function provider_create_table()
	{
		return array
		(
			array(array('table')),
			array(array('table', 'a')),
		);
	}

	/**
	 * @covers  Database::create
	 * @dataProvider    provider_create_table
	 *
	 * @param   array   $arguments
	 */
	public function test_create_table($arguments)
	{
		$this->_test_method_type('create', $arguments, 'SQL_DDL_Create_Table');
	}

	public function provider_create_view()
	{
		return array
		(
			array(array('view')),
			array(array('view', 'a')),
		);
	}

	/**
	 * @covers  Database::create
	 * @dataProvider    provider_create_view
	 *
	 * @param   array   $arguments
	 */
	public function test_create_view($arguments)
	{
		$this->_test_method_type('create', $arguments, 'SQL_DDL_Create_View');
	}

	public function provider_datetime()
	{
		return array
		(
			array(array()),
			array(array(1258461296)),
			array(array(1258461296, 'UTC')),
			array(array(1258461296, 'UTC', 'Y-m-d')),
		);
	}

	/**
	 * @covers  Database::datetime
	 * @dataProvider    provider_datetime
	 *
	 * @param   array   $arguments
	 */
	public function test_datetime($arguments)
	{
		$this->_test_method_type('datetime', $arguments, 'Database_DateTime');
	}

	public function provider_ddl_column()
	{
		return array
		(
			array(array()),
			array(array('a')),
			array(array('a', 'b')),
		);
	}

	/**
	 * @covers  Database::ddl_column
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array   $arguments
	 */
	public function test_ddl_column($arguments)
	{
		$this->_test_method_type('ddl_column', $arguments, 'SQL_DDL_Column');
	}

	public function provider_delete()
	{
		return array
		(
			array(array()),
			array(array('a')),
			array(array('a', 'b')),
		);
	}

	public function provider_ddl_constraint()
	{
		return array
		(
			array(array('check'), 'SQL_DDL_Constraint_Check'),
			array(array('foreign'), 'SQL_DDL_Constraint_Foreign'),
			array(array('primary'), 'SQL_DDL_Constraint_Primary'),
			array(array('unique'), 'SQL_DDL_Constraint_Unique'),
		);
	}

	/**
	 * @covers  Database::ddl_constraint
	 * @dataProvider    provider_ddl_constraint
	 *
	 * @param   array   $arguments
	 * @param   string  $expected
	 */
	public function test_ddl_constraint($arguments, $expected)
	{
		$this->_test_method_type('ddl_constraint', $arguments, $expected);
	}

	/**
	 * @covers  Database::delete
	 * @dataProvider    provider_delete
	 *
	 * @param   array   $arguments
	 */
	public function test_delete($arguments)
	{
		$this->_test_method_type('delete', $arguments, 'SQL_DML_Delete');
	}

	public function provider_drop()
	{
		return array
		(
			array(array('index'), 'SQL_DDL_Drop'),
			array(array('index', 'a'), 'SQL_DDL_Drop'),

			array(array('table'), 'SQL_DDL_Drop_Table'),
			array(array('table', 'a'), 'SQL_DDL_Drop_Table'),
		);
	}

	/**
	 * @covers  Database::drop
	 * @dataProvider    provider_drop
	 *
	 * @param   array   $arguments
	 * @param   string  $expected
	 */
	public function test_drop($arguments, $expected)
	{
		$this->_test_method_type('drop', $arguments, $expected);
	}

	/**
	 * @covers  Database::execute_command
	 */
	public function test_execute_command_empty()
	{
		$db = $this->_database();

		$this->assertSame(0, $db->execute_command(''));
	}

	/**
	 * @covers  Database::execute_command
	 * @expectedException Database_Exception
	 */
	public function test_execute_command_error()
	{
		$db = $this->_database();

		$db->execute_command('invalid command');
	}

	/**
	 * @covers  Database::execute_query
	 */
	public function test_execute_query_empty()
	{
		$db = $this->_database();

		$this->assertNull($db->execute_query(''));
	}

	/**
	 * @covers  Database::execute_query
	 * @expectedException Database_Exception
	 */
	public function test_execute_query_error()
	{
		$db = $this->_database();

		$db->execute_query('invalid query');
	}

	public function provider_expression()
	{
		return array
		(
			array(array('a')),
			array(array('a', array('b'))),
		);
	}

	/**
	 * @covers  Database::expression
	 * @dataProvider    provider_expression
	 *
	 * @param   array   $arguments
	 */
	public function test_expression($arguments)
	{
		$this->_test_method_type('expression', $arguments, 'SQL_Expression');
	}

	/**
	 * @covers  Database::identifier
	 */
	public function test_identifier()
	{
		$this->_test_method_type('identifier', array('a'), 'SQL_Identifier');
	}

	public function provider_insert()
	{
		return array
		(
			array(array()),
			array(array('a')),
			array(array('a', array('b'))),
		);
	}

	/**
	 * @covers  Database::insert
	 * @dataProvider    provider_insert
	 *
	 * @param   array   $arguments
	 */
	public function test_insert($arguments)
	{
		$this->_test_method_type('insert', $arguments, 'Database_Insert');
	}

	public function provider_query()
	{
		return array
		(
			array(array('a')),
			array(array('a', array('b'))),
		);
	}

	/**
	 * @covers  Database::query
	 * @dataProvider    provider_query
	 *
	 * @param   array   $arguments
	 */
	public function test_query($arguments)
	{
		$this->_test_method_type('query', $arguments, 'Database_Query');
	}

	public function provider_query_set()
	{
		return array
		(
			array(array()),
			array(array(new SQL_Expression('a'))),
		);
	}

	/**
	 * @covers  Database::query_set
	 * @dataProvider    provider_query_set
	 *
	 * @param   array   $arguments
	 */
	public function test_query_set($arguments)
	{
		$this->_test_method_type('query_set', $arguments, 'Database_Query_Set');
	}

	/**
	 * @covers  Database::connect
	 */
	public function test_reconnect()
	{
		$db = $this->_database();

		$db->connect();
		$db->disconnect();

		try
		{
			$db->connect();
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	public function provider_reference()
	{
		return array
		(
			array(array()),
			array(array('a')),
			array(array('a', 'b')),
		);
	}

	/**
	 * @covers  Database::reference
	 * @dataProvider    provider_reference
	 *
	 * @param   array   $arguments
	 */
	public function test_reference($arguments)
	{
		$this->_test_method_type('reference', $arguments, 'SQL_Table_Reference');
	}

	public function provider_select()
	{
		return array
		(
			array(array()),
			array(array(array('a' => 'b'))),
		);
	}

	/**
	 * @covers  Database::select
	 * @dataProvider    provider_select
	 *
	 * @param   array   $arguments
	 */
	public function test_select($arguments)
	{
		$this->_test_method_type('select', $arguments, 'Database_Select');
	}

	/**
	 * @covers  Database::table
	 */
	public function test_table()
	{
		$this->_test_method_type('table', array('a'), 'SQL_Table');
	}

	/**
	 * @covers  Database_iIntrospect::table_columns
	 */
	public function test_table_columns_no_table()
	{
		$db = $this->_database();

		if ( ! $db instanceof Database_iIntrospect)
			$this->markTestSkipped('Connection does not implement Database_iIntrospect');

		$this->assertSame(array(), $db->table_columns('table-does-not-exist'));
	}

	public function provider_update()
	{
		return array
		(
			array(array()),
			array(array('a')),
			array(array('a', 'b')),
			array(array('a', 'b', array('c' => 'd'))),
		);
	}

	/**
	 * @covers  Database::update
	 * @dataProvider    provider_update
	 *
	 * @param   array   $arguments
	 */
	public function test_update($arguments)
	{
		$this->_test_method_type('update', $arguments, 'SQL_DML_Update');
	}
}
