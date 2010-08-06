<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.sqlite2
 */
class Database_SQLite2_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'SQLite2')
			$this->markTestSkipped('Database not configured for SQLite2');

		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (id INTEGER PRIMARY KEY, value INTEGER)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (60)');
	}

	public function tearDown()
	{
		if ( ! $this->_db)
			return;

		$this->_db->disconnect();
	}

	public function test_execute_command_query()
	{
		$this->assertSame(0, $this->_db->execute_command('SELECT * FROM '.$this->_table), 'Always zero');
	}

	public function test_execute_compound_command()
	{
		$this->assertSame(2, $this->_db->execute_command('DELETE FROM '.$this->_table.' WHERE "id" = 1; DELETE FROM '.$this->_table.' WHERE "id" = 2'), 'Total number of rows');
	}

	public function test_execute_compound_command_mixed()
	{
		$this->assertSame(3, $this->_db->execute_command('SELECT * FROM '.$this->_table.' WHERE value < 60; DELETE FROM '.$this->_table.' WHERE 1 = 1'), 'Count of last statement');
	}

	public function test_execute_compound_query()
	{
		$result = $this->_db->execute_query('SELECT * FROM '.$this->_table.' WHERE value < 60; SELECT * FROM '.$this->_table.' WHERE value < 70');

		$this->assertType('Database_Result', $result);
		$this->assertSame(2, count($result), 'First result');
		$this->assertEquals(array(50, 55), $result->as_array(NULL, 'value'), 'First result');

		$this->assertType('Database_Result', $this->_db->execute_query('SELECT * FROM '.$this->_table.' WHERE value < 60; DELETE FROM '.$this->_table));
		$this->assertEquals(3, $this->_db->execute_query('SELECT COUNT(*) FROM '.$this->_table)->get(), 'Second statement is not executed');

		$this->assertNull($this->_db->execute_query('DELETE FROM '.$this->_table.' WHERE value = 50; DELETE FROM '.$this->_table.' WHERE value = 55; SELECT * FROM '.$this->_table));
		$this->assertEquals(2, $this->_db->execute_query('SELECT COUNT(*) FROM '.$this->_table)->get(), 'Only the first statement is executed');
	}

	public function test_execute_compound_query_mixed()
	{
		$this->assertType('Database_Result', $this->_db->execute_query('SELECT * FROM '.$this->_table.' WHERE value < 60; DELETE FROM '.$this->_table));

		$this->assertEquals(3, $this->_db->execute_query('SELECT COUNT(*) FROM '.$this->_table)->get(), 'Second statement is not executed');
	}

	public function test_execute_insert()
	{
		$this->assertSame(array(0,3), $this->_db->execute_insert(''), 'Prior identity');
		$this->assertSame(array(1,4), $this->_db->execute_insert('INSERT INTO '.$this->_table.' (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$query = $this->_db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_Command_Insert_Multiple);

		$query->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(2,5), $query->execute($this->_db), 'INTEGER PRIMARY KEY of the last row');

		$query->values(NULL)->values(array('75'));

		$this->assertEquals(array(1,6), $query->execute($this->_db));
	}
}
