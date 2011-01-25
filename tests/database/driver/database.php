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

			array('conditions', array(), 'SQL_Conditions'),
			array('conditions', array('a'), 'SQL_Conditions'),
			array('conditions', array('a', '='), 'SQL_Conditions'),
			array('conditions', array('a', '=', 'b'), 'SQL_Conditions'),

			array('expression', array('a'), 'SQL_Expression'),
			array('expression', array('a', array('b')), 'SQL_Expression'),

			array('reference', array(), 'SQL_Table_Reference'),
			array('reference', array('a'),'SQL_Table_Reference'),
			array('reference', array('a', 'b'), 'SQL_Table_Reference'),

			// Identifiers

			array('column', array('a'), 'SQL_Column'),
			array('identifier', array('a'), 'SQL_Identifier'),
			array('table', array('a'), 'SQL_Table'),

			// Commands

			array('command', array('a'), 'Database_Command'),
			array('command', array('a', array('b')), 'Database_Command'),

			array('delete', array(), 'SQL_DML_Delete'),
			array('delete', array('a'), 'SQL_DML_Delete'),
			array('delete', array('a', 'b'), 'SQL_DML_Delete'),

			array('insert', array(), 'SQL_DML_Insert'),
			array('insert', array('a'), 'SQL_DML_Insert'),
			array('insert', array('a', array('b')), 'SQL_DML_Insert'),

			array('update', array(), 'SQL_DML_Update'),
			array('update', array('a'), 'SQL_DML_Update'),
			array('update', array('a', 'b'), 'SQL_DML_Update'),
			array('update', array('a', 'b', array('c' => 'd')), 'SQL_DML_Update'),

			// Queries

			array('query', array('a'), 'Database_Query'),
			array('query', array('a', array('b')), 'Database_Query'),

			array('query_set', array(), 'Database_Query_Set'),
			array('query_set', array(new Database_Query('a')), 'Database_Query_Set'),

			array('select', array(), 'Database_Select'),
			array('select', array(array('a' => 'b')), 'Database_Select'),

			// DDL Commands

			array('alter', array('table'), 'SQL_DDL_Alter_Table'),
			array('alter', array('table', 'a'), 'SQL_DDL_Alter_Table'),

			array('create', array('index'), 'SQL_DDL_Create_Index'),
			array('create', array('index', 'a'), 'SQL_DDL_Create_Index'),

			array('create', array('table'), 'SQL_DDL_Create_Table'),
			array('create', array('table', 'a'), 'SQL_DDL_Create_Table'),

			array('create', array('view'), 'SQL_DDL_Create_View'),
			array('create', array('view', 'a'), 'SQL_DDL_Create_View'),

			array('drop', array('index'), 'SQL_DDL_Drop'),
			array('drop', array('index', 'a'), 'SQL_DDL_Drop'),

			array('drop', array('table'), 'SQL_DDL_Drop_Table'),
			array('drop', array('table', 'a'), 'SQL_DDL_Drop_Table'),

			// DDL Expressions

			array('ddl_column', array(), 'SQL_DDL_Column'),
			array('ddl_column', array('a'), 'SQL_DDL_Column'),
			array('ddl_column', array('a', 'b'), 'SQL_DDL_Column'),

			array('ddl_constraint', array('check'), 'SQL_DDL_Constraint_Check'),
			array('ddl_constraint', array('foreign'), 'SQL_DDL_Constraint_Foreign'),
			array('ddl_constraint', array('primary'), 'SQL_DDL_Constraint_Primary'),
			array('ddl_constraint', array('unique'), 'SQL_DDL_Constraint_Unique'),
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
