<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.multiple
 */
class Database_Multiple_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$this->_db = Database::instance('testing');

		if ( ! $this->_db instanceof Database_iMultiple)
			$this->markTestSkipped('Database instance not Database_iMultiple');

		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (60)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (65)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (65)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_execute_commands()
	{
		$results = $this->_db->execute_multiple('DELETE FROM '.$this->_table.' WHERE value = 65; DELETE FROM '.$this->_table.' WHERE value = 50');

		$this->assertType('Database_Result_Iterator', $results);

		$this->assertTrue($results->valid(), 'Starts valid');
		$this->assertSame(0, $results->key(), 'First key');
		$this->assertSame(2, $results->current(), 'First result');

		$this->assertSame($results, $results->next(), 'Chainable (1)');
		$this->assertTrue($results->valid());
		$this->assertSame(1, $results->key(), 'Second key');
		$this->assertSame(1, $results->current(), 'Second result');

		$this->assertSame($results, $results->next(), 'Chainable (2)');
		$this->assertFalse($results->valid());
	}

	public function test_execute_command_query()
	{
		$results = $this->_db->execute_multiple('DELETE FROM '.$this->_table.' WHERE value = 65; SELECT * FROM '.$this->_table);

		$this->assertType('Database_Result_Iterator', $results);

		$this->assertTrue($results->valid(), 'Starts valid');
		$this->assertSame(0, $results->key(), 'First key');
		$this->assertSame(2, $results->current(), 'First result');

		$this->assertSame($results, $results->next(), 'Chainable (1)');
		$this->assertTrue($results->valid());
		$this->assertSame(1, $results->key(), 'Second key');

		$result = $results->current();

		$this->assertType('Database_Result', $result, 'Second result');
		$this->assertSame(3, count($result), 'Second result');

		$this->assertSame($results, $results->next(), 'Chainable (2)');
		$this->assertFalse($results->valid());
	}

	public function test_execute_queries()
	{
		$results = $this->_db->execute_multiple('SELECT * FROM '.$this->_table.'; SELECT * FROM '.$this->_table.' WHERE value = 50');

		$this->assertType('Database_Result_Iterator', $results);

		$this->assertTrue($results->valid(), 'Starts valid');
		$this->assertSame(0, $results->key(), 'First key');

		$result = $results->current();

		$this->assertType('Database_Result', $result, 'First result');
		$this->assertSame(5, count($result), 'First result');

		$this->assertSame($results, $results->next(), 'Chainable (1)');
		$this->assertTrue($results->valid());
		$this->assertSame(1, $results->key(), 'Second key');

		$result = $results->current();

		$this->assertType('Database_Result', $result, 'Second result');
		$this->assertSame(1, count($result), 'Second result');

		$this->assertSame($results, $results->next(), 'Chainable (2)');
		$this->assertFalse($results->valid());
	}

	public function test_execute_query_command()
	{
		$results = $this->_db->execute_multiple('SELECT * FROM '.$this->_table.'; DELETE FROM '.$this->_table.' WHERE value = 65');

		$this->assertType('Database_Result_Iterator', $results);

		$this->assertTrue($results->valid(), 'Starts valid');
		$this->assertSame(0, $results->key(), 'First key');

		$result = $results->current();

		$this->assertType('Database_Result', $result, 'First result');
		$this->assertSame(5, count($result), 'First result');

		$this->assertSame($results, $results->next(), 'Chainable (1)');
		$this->assertTrue($results->valid());
		$this->assertSame(1, $results->key(), 'Second key');
		$this->assertSame(2, $results->current(), 'Second result');

		$this->assertSame($results, $results->next(), 'Chainable (2)');
		$this->assertFalse($results->valid());
	}

	public function test_iterator_rewind()
	{
		$results = $this->_db->execute_multiple('SELECT * FROM '.$this->_table.'; SELECT * FROM '.$this->_table.' WHERE value = 50');

		$results->next();

		$this->assertSame($results, $results->rewind(), 'Chainable');
		$this->assertSame(1, $results->key(), 'Second key');
		$this->assertSame(1, count($results->current()), 'Second result');
	}
}
