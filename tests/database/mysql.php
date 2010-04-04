<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'MySQL')
			$this->markTestSkipped('Database not configured for MySQL');

		$this->_db = Database::instance('testing');
		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_db->quote_table('temp_test_table').' (id bigint unsigned AUTO_INCREMENT PRIMARY KEY, value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_db->quote_table('temp_test_table').' (value) VALUES (50), (55), (60)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_execute_command_query()
	{
		$this->assertSame(3, $this->_db->execute_command('SELECT * FROM '.$this->_db->quote_table('temp_test_table')), 'Number of returned rows');
	}

	public function test_execute_insert()
	{
		$this->assertSame(array(0,1), $this->_db->execute_insert(''), 'First identity from prior INSERT');
		$this->assertSame(array(1,4), $this->_db->execute_insert('INSERT INTO '.$this->_db->quote_table('temp_test_table').' (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$query = $this->_db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_Command_Insert_Identity);

		$query->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(2,4), $query->execute($this->_db), 'AUTO_INCREMENT of the first row');

		$query->values(NULL)->values(array('75'));

		$this->assertEquals(array(1,6), $query->execute($this->_db));
	}
}
