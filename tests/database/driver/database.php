<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Driver_Database_Test extends PHPUnit_Framework_TestCase
{
	public function test_execute_command_empty()
	{
		$db = $this->sharedFixture;

		$this->assertSame(0, $db->execute_command(''));
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_command_error()
	{
		$db = $this->sharedFixture;

		$db->execute_command('invalid command');
	}

	public function test_execute_query_empty()
	{
		$db = $this->sharedFixture;

		$this->assertNull($db->execute_query(''));
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_query_error()
	{
		$db = $this->sharedFixture;

		$db->execute_query('invalid query');
	}

	public function test_reconnect()
	{
		$db = $this->sharedFixture;

		$db->disconnect();

		try
		{
			$db->connect();
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}
}
