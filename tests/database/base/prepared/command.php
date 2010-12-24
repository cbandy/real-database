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
	public function provider_constructor()
	{
		return array
		(
			array('',   array()),
			array('a',  array()),
			array('',   array('b')),
			array('c',  array('d')),
			array('e',  array(1 => 'f')),
			array('g',  array('h' => 2)),
			array('i',  array('j' => 'k')),
		);
	}

	/**
	 * @covers  Database_Prepared_Command::__construct
	 * @dataProvider    provider_constructor
	 *
	 * @param   mixed   $statement  SQL
	 * @param   array   $parameters Unquoted parameters
	 */
	public function test_constructor($statement, $parameters)
	{
		$db = $this->sharedFixture;

		$command = new Database_Prepared_Command($db, $statement, $parameters);

		$this->assertSame($statement, (string) $command);
		$this->assertSame($parameters, $command->parameters);
	}

	public function provider_execute()
	{
		return array
		(
			array('a', array(),         'a'),
			array('b', array('c'),      'b'),
			array('d ?', array('e'),    "d 'e'"),
		);
	}

	/**
	 * @covers  Database_Prepared_Command::execute
	 * @dataProvider provider_execute
	 *
	 * @param   string  $sql        SQL
	 * @param   array   $parameters Unquoted parameters
	 * @param   array   $expected   Expected SQL
	 */
	public function test_execute($sql, $parameters, $expected)
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_command'));
		$db->expects($this->once())
			->method('execute_command')
			->with($this->equalTo($expected));

		$command = new Database_Prepared_Command($db, $sql, $parameters);
		$command->execute();
	}
}
