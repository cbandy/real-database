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
	public function provider_execute()
	{
		return array
		(
			array(''),
			array('DELETE FROM t1'),
		);
	}

	/**
	 * @covers  Database_Command::execute
	 * @dataProvider    provider_execute
	 *
	 * @param   string  $sql    Expected SQL
	 */
	public function test_execute($sql)
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_command'));
		$db->expects($this->once())
			->method('execute_command')
			->with($this->equalTo($sql));

		$command = new Database_Command($sql);
		$command->execute($db);
	}

	public function provider_prepare()
	{
		return array
		(
			array('a', array()),
			array('b', array('c')),
			array('d ?', array('e')),
		);
	}

	/**
	 * @covers  Database_Command::prepare
	 * @dataProvider    provider_prepare
	 *
	 * @param   string  $sql        Expected SQL
	 * @param   array   $parameters Expected parameters
	 */
	public function test_prepare($sql, $parameters)
	{
		$db = $this->sharedFixture;

		$command = new Database_Command($sql, $parameters);
		$result = $command->prepare($db);

		$this->assertType('Database_Prepared_Command', $result);
		$this->assertSame($parameters, $result->parameters);
		$this->assertSame($sql, (string) $result);
	}
}
