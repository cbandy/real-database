<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Driver_Database_Test extends PHPUnit_Framework_TestCase
{
	public function test_execute_command_empty()
	{
		$db = $this->sharedFixture;

		$this->assertSame(0, $db->execute_command(''));
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_command_error()
	{
		$db = $this->sharedFixture;

		$db->execute_command('invalid command');
	}

	public function test_execute_query_empty()
	{
		$db = $this->sharedFixture;

		$this->assertNull($db->execute_query(''));
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_query_error()
	{
		$db = $this->sharedFixture;

		$db->execute_query('invalid query');
	}

	/**
	 * @dataProvider    provider_factories
	 */
	public function test_factories($method, $arguments, $expected)
	{
		$db = $this->sharedFixture;

		$result = call_user_func_array(array($db, $method), $arguments);
		$this->assertType($expected, $result);
	}

	public function provider_factories()
	{
		return array
		(
			// Datatypes

			array('binary', array('a'), 'Database_Binary'),

			array('datetime', array(), 'Database_DateTime'),
			array('datetime', array(1258461296), 'Database_DateTime'),
			array('datetime', array(1258461296, 'UTC'), 'Database_DateTime'),
			array('datetime', array(1258461296, 'UTC', 'Y-m-d'), 'Database_DateTime'),

			// Expressions

			array('conditions', array(), 'Database_Conditions'),
			array('conditions', array('a'), 'Database_Conditions'),
			array('conditions', array('a', '='), 'Database_Conditions'),
			array('conditions', array('a', '=', 'b'), 'Database_Conditions'),

			array('expression', array('a'), 'Database_Expression'),
			array('expression', array('a', array('b')), 'Database_Expression'),

			array('from', array(), 'Database_From'),
			array('from', array('a'),'Database_From'),
			array('from', array('a', 'b'), 'Database_From'),

			// Identifiers

			array('column', array('a'), 'Database_Column'),
			array('identifier', array('a'), 'Database_Identifier'),
			array('table', array('a'), 'Database_Table'),

			// Commands

			array('command', array('a'), 'Database_Command'),
			array('command', array('a', array('b')), 'Database_Command'),

			array('delete', array(), 'Database_Command_Delete'),
			array('delete', array('a'), 'Database_Command_Delete'),
			array('delete', array('a', 'b'), 'Database_Command_Delete'),

			array('insert', array(), 'Database_Command_Insert'),
			array('insert', array('a'), 'Database_Command_Insert'),
			array('insert', array('a', array('b')), 'Database_Command_Insert'),

			array('update', array(), 'Database_Command_Update'),
			array('update', array('a'), 'Database_Command_Update'),
			array('update', array('a', 'b'), 'Database_Command_Update'),
			array('update', array('a', 'b', array('c' => 'd')), 'Database_Command_Update'),

			// Queries

			array('query', array('a'), 'Database_Query'),
			array('query', array('a', array('b')), 'Database_Query'),

			array('query_set', array(), 'Database_Query_Set'),
			array('query_set', array(new Database_Query('a')), 'Database_Query_Set'),

			array('select', array(), 'Database_Query_Select'),
			array('select', array(array('a' => 'b')), 'Database_Query_Select'),

			// DDL Commands

			array('alter', array('table'), 'Database_Command_Alter_Table'),
			array('alter', array('table', 'a'), 'Database_Command_Alter_Table'),

			array('create', array('index'), 'Database_Command_Create_Index'),
			array('create', array('index', 'a'), 'Database_Command_Create_Index'),

			array('create', array('table'), 'Database_Command_Create_Table'),
			array('create', array('table', 'a'), 'Database_Command_Create_Table'),

			array('create', array('view'), 'Database_Command_Create_View'),
			array('create', array('view', 'a'), 'Database_Command_Create_View'),

			array('drop', array('index'), 'Database_Command_Drop'),
			array('drop', array('index', 'a'), 'Database_Command_Drop'),

			array('drop', array('table'), 'Database_Command_Drop_Table'),
			array('drop', array('table', 'a'), 'Database_Command_Drop_Table'),

			// DDL Expressions

			array('ddl_column', array(), 'Database_DDL_Column'),
			array('ddl_column', array('a'), 'Database_DDL_Column'),
			array('ddl_column', array('a', 'b'), 'Database_DDL_Column'),

			array('ddl_constraint', array('check'), 'Database_DDL_Constraint_Check'),
			array('ddl_constraint', array('foreign'), 'Database_DDL_Constraint_Foreign'),
			array('ddl_constraint', array('primary'), 'Database_DDL_Constraint_Primary'),
			array('ddl_constraint', array('unique'), 'Database_DDL_Constraint_Unique'),
		);
	}

	public function test_reconnect()
	{
		$db = $this->sharedFixture;

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
}
