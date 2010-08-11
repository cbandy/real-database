<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
abstract class Database_Abstract_Transaction_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 * @dataProvider    test_command_provider
	 *
	 * @param   string  $query      SQL query
	 * @param   string  $command    SQL command
	 * @param   array   $expected   Rows expected after command is executed and after commit
	 */
	public function test_command_commit($query, $command, $expected)
	{
		$db = $this->sharedFixture;
		$initial = $db->execute_query($query)->as_array();

		$this->assertNull($db->begin());
		$this->assertEquals($initial, $db->execute_query($query)->as_array());

		$this->assertSame(1, $db->execute_command($command));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		$this->assertNull($db->commit());
		$this->assertEquals($expected, $db->execute_query($query)->as_array());
	}

	/**
	 * @test
	 * @dataProvider    test_command_provider
	 *
	 * @param   string  $query      SQL query
	 * @param   string  $command    SQL command
	 * @param   array   $expected   Rows expected after command is executed but before rollback
	 */
	public function test_command_rollback($query, $command, $expected)
	{
		$db = $this->sharedFixture;
		$initial = $db->execute_query($query)->as_array();

		$this->assertNull($db->begin());
		$this->assertEquals($initial, $db->execute_query($query)->as_array());

		$this->assertSame(1, $db->execute_command($command));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		$this->assertNull($db->rollback());
		$this->assertEquals($initial, $db->execute_query($query)->as_array());
	}

	/**
	 * @see test_command_commit
	 * @see test_command_rollback
	 *
	 * @return  array
	 */
	abstract public function test_command_provider();
}
