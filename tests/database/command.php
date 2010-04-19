<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.command
 */
class Database_Command_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_execute()
	{
		$command = new Database_Command('INSERT INTO '.$this->_table.' (value) VALUES (100)');

		$this->assertSame(1, $command->execute($this->_db));

		$command = new Database_Command('DELETE FROM '.$this->_table);

		$this->assertSame(2, $command->execute($this->_db));
	}

	public function test_prepare()
	{
		$command = new Database_Command('INSERT INTO '.$this->_table.' (value) VALUES (100)');

		$prepared = $command->prepare($this->_db);

		$this->assertTrue($prepared instanceof Database_Prepared_Command);
	}
}
