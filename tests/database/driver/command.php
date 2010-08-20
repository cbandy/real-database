<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Driver_Command_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' (value integer)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (50)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_execute()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$command = new Database_Command('INSERT INTO '.$table.' (value) VALUES (100)');

		$this->assertSame(1, $command->execute($db));

		$command = new Database_Command('DELETE FROM '.$table.' WHERE 1 = 1');

		$this->assertSame(2, $command->execute($db));
	}

	public function test_prepare()
	{
		$db = $this->sharedFixture;

		$command = new Database_Command('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (100)');

		$prepared = $command->prepare($db);

		$this->assertType('Database_Prepared_Command', $prepared);
	}
}
