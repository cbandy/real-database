<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Command_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 * @dataProvider    provider_execute
	 */
	public function test_execute($sql)
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_command'));

		$db->expects($this->once())
			->method('execute_command')
			->with($this->equalTo($sql));

		$query = new Database_Command($sql);

		$query->execute($db);
	}

	public function provider_execute()
	{
		return array
		(
			array(''),
			array('DELETE FROM t1'),
		);
	}
}
