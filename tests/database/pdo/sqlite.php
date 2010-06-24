<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'PDO_SQLite')
			$this->markTestSkipped('Database not configured for SQLite using PDO');

		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (id INTEGER PRIMARY KEY, value INTEGER)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (60)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_execute_command_query()
	{
		$this->assertSame(1, $this->_db->execute_command('SELECT * FROM '.$this->_table), 'Always one');
	}

	public function test_execute_insert()
	{
		$this->assertEquals(array(0,3), $this->_db->execute_insert(''), 'Prior identity');
		$this->assertEquals(array(1,4), $this->_db->execute_insert('INSERT INTO '.$this->_table.' (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$query = $this->_db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_Command_Insert_Multiple);

		$query->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(1,5), $query->execute($this->_db), 'Count is always one. Identity is INTEGER PRIMARY KEY of the last row');

		$query->values(NULL)->values(array('75'));

		$this->assertEquals(array(1,6), $query->execute($this->_db));
	}
}
