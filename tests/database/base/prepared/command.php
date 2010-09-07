<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Prepared_Command_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$command = new Database_Prepared_Command($db, 'a', array('b'));

		$this->assertSame('a', (string) $command);
		$this->assertSame(array('b'), $command->parameters);
	}

	public function test_execute()
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_command'));
		$command = new Database_Prepared_Command($db, 'a', array('b'));

		$db->expects($this->once())
			->method('execute_command')
			->with($this->equalTo('a'));

		$command->execute();
	}
}
