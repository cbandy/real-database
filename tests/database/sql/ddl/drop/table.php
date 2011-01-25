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
	 * @covers  SQL_DDL_Drop_Table::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('DROP TABLE "pre_a"', $db->quote(new SQL_DDL_Drop_Table('a')));
		$this->assertSame('DROP TABLE "pre_a" CASCADE', $db->quote(new SQL_DDL_Drop_Table('a', TRUE)));
		$this->assertSame('DROP TABLE "pre_a" RESTRICT', $db->quote(new SQL_DDL_Drop_Table('a', FALSE)));
	}

	/**
	 * @covers  SQL_DDL_Drop_Table::name
	 */
	public function test_name()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Drop_Table('a');

		$this->assertSame($command, $command->name('b'));
		$this->assertSame('DROP TABLE "pre_b"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Drop_Table::names
	 */
	public function test_names()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Drop_Table;

		$this->assertSame($command, $command->names(array('a', 'b')));
		$this->assertSame('DROP TABLE "pre_a", "pre_b"', $db->quote($command));
	}
}
