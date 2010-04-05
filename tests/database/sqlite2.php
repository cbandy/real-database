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

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'SQLite2')
			$this->markTestSkipped('Database not configured for SQLite2');

		$this->_db = Database::instance('testing');
		$this->_db->execute_command('CREATE TEMPORARY TABLE temp_test_table (id INTEGER PRIMARY KEY, value INTEGER)');
		$this->_db->execute_command('INSERT INTO temp_test_table (value) VALUES (50)');
		$this->_db->execute_command('INSERT INTO temp_test_table (value) VALUES (55)');
		$this->_db->execute_command('INSERT INTO temp_test_table (value) VALUES (60)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_execute_command_query()
	{
		$this->assertSame(0, $this->_db->execute_command('SELECT * FROM temp_test_table'), 'Always zero');
	}

	public function test_execute_insert()
	{
		$this->assertSame(array(0,3), $this->_db->execute_insert(''), 'Prior identity');
		$this->assertSame(array(1,4), $this->_db->execute_insert('INSERT INTO temp_test_table (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$query = $this->_db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_SQLite2_Insert);

		$query->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(2,5), $query->execute($this->_db), 'INTEGER PRIMARY KEY of the last row');

		$query->values(NULL)->values(array('75'));

		$this->assertEquals(array(1,6), $query->execute($this->_db));
	}
}