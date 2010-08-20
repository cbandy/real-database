<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.sqlite2
 */
class Database_SQLite2_Database_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' (id INTEGER PRIMARY KEY, value INTEGER)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (50)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (55)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (60)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_execute_command_query()
	{
		$db = $this->sharedFixture;

		$this->assertSame(0, $db->execute_command('SELECT * FROM '.$db->quote_table($this->_table)), 'Always zero');
	}

	public function test_execute_compound_command()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertSame(2, $db->execute_command('DELETE FROM '.$table.' WHERE "id" = 1; DELETE FROM '.$table.' WHERE "id" = 2'), 'Total number of rows');
	}

	public function test_execute_compound_command_mixed()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertSame(3, $db->execute_command('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table.' WHERE 1 = 1'), 'Count of last statement');
	}

	public function test_execute_compound_query()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$result = $db->execute_query('SELECT * FROM '.$table.' WHERE value < 60; SELECT * FROM '.$table.' WHERE value < 70');

		$this->assertType('Database_Result', $result);
		$this->assertSame(2, count($result), 'First result');
		$this->assertEquals(array(50, 55), $result->as_array(NULL, 'value'), 'First result');

		$this->assertType('Database_Result', $db->execute_query('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table));
		$this->assertEquals(3, $db->execute_query('SELECT COUNT(*) FROM '.$table)->get(), 'Second statement is not executed');

		$this->assertNull($db->execute_query('DELETE FROM '.$table.' WHERE value = 50; DELETE FROM '.$table.' WHERE value = 55; SELECT * FROM '.$table));
		$this->assertEquals(2, $db->execute_query('SELECT COUNT(*) FROM '.$table)->get(), 'Only the first statement is executed');
	}

	public function test_execute_compound_query_mixed()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertType('Database_Result', $db->execute_query('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table));

		$this->assertEquals(3, $db->execute_query('SELECT COUNT(*) FROM '.$table)->get(), 'Second statement is not executed');
	}

	public function test_execute_insert()
	{
		$db = $this->sharedFixture;

		$this->assertSame(array(0,3), $db->execute_insert(''), 'Prior identity');
		$this->assertSame(array(1,4), $db->execute_insert('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$db = $this->sharedFixture;

		$query = $db->insert('temp_test_table', array('value'));

		$this->assertType('Database_SQLite_Insert', $query);

		$query->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(2,5), $query->execute($db), 'INTEGER PRIMARY KEY of the last row');

		$query->values(NULL)->values(array('75'));

		$this->assertEquals(array(1,6), $query->execute($db));
	}
}
