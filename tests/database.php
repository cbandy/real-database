<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	protected function _create_table()
	{
		if ($this->_db instanceof Database_MySQL)
		{
			// Ensure the storage engine supports transactions
			$this->_db->execute_command('SET storage_engine = InnoDB');
		}

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (60)');
	}

	public function setUp()
	{
		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_execute_command()
	{
		$this->assertSame(0, $this->_db->execute_command(''), 'Empty');
		$this->assertSame(0, $this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)'), 'DDL');
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_command_error()
	{
		$this->_db->execute_command('invalid command');
	}

	public function test_execute_query()
	{
		$this->_create_table();

		$this->assertNull($this->_db->execute_query(''), 'Empty');

		$result = $this->_db->execute_query('SELECT * FROM '.$this->_table);
		$this->assertTrue($result instanceof Database_Result, 'Query');

		$this->assertNull($this->_db->execute_query('DROP TABLE '.$this->_table), 'Command');
	}

	public function test_execute_query_command()
	{
		$this->_create_table();

		$this->assertNull($this->_db->execute_query('DROP TABLE '.$this->_table));
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_query_error()
	{
		$this->_db->execute_query('invalid query');
	}

	public function test_factories_dynamic()
	{
		$this->assertTrue($this->_db->binary('') instanceof Database_Binary);
		$this->assertTrue($this->_db->datetime() instanceof Database_DateTime);

		$this->assertTrue($this->_db->command('') instanceof Database_Command);
		$this->assertTrue($this->_db->delete() instanceof Database_Command_Delete);
		$this->assertTrue($this->_db->insert() instanceof Database_Command_Insert);
		$this->assertTrue($this->_db->update() instanceof Database_Command_Update);

		$this->assertTrue($this->_db->query('') instanceof Database_Query);
		$this->assertTrue($this->_db->query_set() instanceof Database_Query_Set);
		$this->assertTrue($this->_db->select() instanceof Database_Query_Select);

		$this->assertTrue($this->_db->column('') instanceof Database_Column);
		$this->assertTrue($this->_db->identifier('') instanceof Database_Identifier);
		$this->assertTrue($this->_db->table('') instanceof Database_Table);

		$this->assertTrue($this->_db->conditions() instanceof Database_Conditions);
		$this->assertTrue($this->_db->expression('') instanceof Database_Expression);
		$this->assertTrue($this->_db->from() instanceof Database_From);
	}

	public function test_prepare_command()
	{
		$this->assertTrue($this->_db->prepare_command('') instanceof Database_Prepared_Command);
	}

	public function test_prepare_query()
	{
		$this->assertTrue($this->_db->prepare_query('') instanceof Database_Prepared_Query);
	}

	public function test_reconnect()
	{
		$this->_db->disconnect();

		try
		{
			$this->_db->connect();
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}
}
