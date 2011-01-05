<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_Command_Drop_Table_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Command_Drop_Table::__construct
	 */
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('DROP TABLE "pre_a"', $db->quote(new Database_Command_Drop_Table('a')));
		$this->assertSame('DROP TABLE "pre_a" CASCADE', $db->quote(new Database_Command_Drop_Table('a', TRUE)));
		$this->assertSame('DROP TABLE "pre_a" RESTRICT', $db->quote(new Database_Command_Drop_Table('a', FALSE)));
	}

	/**
	 * @covers  Database_Command_Drop_Table::name
	 */
	public function test_name()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Drop_Table('a');

		$this->assertSame($command, $command->name('b'));
		$this->assertSame('DROP TABLE "pre_b"', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Drop_Table::names
	 */
	public function test_names()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Drop_Table;

		$this->assertSame($command, $command->names(array('a', 'b')));
		$this->assertSame('DROP TABLE "pre_a", "pre_b"', $db->quote($command));
	}
}
