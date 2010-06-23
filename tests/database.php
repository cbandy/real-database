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
		$this->assertSame(0, $this->_db->execute_command(''));
		$this->assertSame(0, $this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)'));
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

	public function test_factories_static()
	{
		$this->assertType('Database_Command', Database::command(''));
		$this->assertType('Database_Command_Delete', Database::delete());
		$this->assertType('Database_Command_Insert', Database::insert());
		$this->assertType('Database_Command_Update', Database::update());

		$this->assertType('Database_Query', Database::query(''));
		$this->assertType('Database_Query_Set', Database::query_set());
		$this->assertType('Database_Query_Select', Database::select());

		$this->assertType('Database_Column', Database::column(''));
		$this->assertType('Database_Identifier', Database::identifier(''));
		$this->assertType('Database_Table', Database::table(''));

		$this->assertType('Database_Conditions', Database::conditions());
		$this->assertType('Database_Expression', Database::expression(''));
		$this->assertType('Database_From', Database::from());
	}

	public function test_prepare_command()
	{
		$this->assertTrue($this->_db->prepare_command('') instanceof Database_Prepared_Command);
	}

	public function test_prepare_query()
	{
		$this->assertTrue($this->_db->prepare_query('') instanceof Database_Prepared_Query);
	}

	public function test_transactions()
	{
		$this->_create_table();

		$delete = 'DELETE FROM '.$this->_table.' WHERE value = 100';
		$insert = 'INSERT INTO '.$this->_table.' (value) VALUES (100)';
		$select = 'SELECT * FROM '.$this->_table;

		$expected = array
		(
			array('value' => 50),
			array('value' => 55),
			array('value' => 60),
		);

		$this->assertNull($this->_db->begin());
		$this->assertEquals($expected, $this->_db->execute_query($select)->as_array());
		$this->assertSame(1, $this->_db->execute_command($insert));

		$expected[] = array('value' => 100);

		$this->assertEquals($expected, $this->_db->execute_query($select)->as_array());
		$this->assertNull($this->_db->commit());
		$this->assertEquals($expected, $this->_db->execute_query($select)->as_array());

		$this->assertNull($this->_db->begin());
		$this->assertEquals($expected, $this->_db->execute_query($select)->as_array());
		$this->assertSame(1, $this->_db->execute_command($delete));

		$this->assertEquals(array_slice($expected, 0, -1), $this->_db->execute_query($select)->as_array());
		$this->assertNull($this->_db->rollback());
		$this->assertEquals($expected, $this->_db->execute_query($select)->as_array());
	}
}
