<?php
/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DDL_Create_Index_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_MySQL_DDL_Create_Index::type
	 */
	public function test_type()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Create_Index('a', 'b');

		$this->assertSame($command, $command->type('fulltext'));
		$this->assertSame("CREATE FULLTEXT INDEX `a` ON `b` ()", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_DDL_Create_Index::__toString
	 * @covers  Database_MySQL_DDL_Create_Index::using
	 */
	public function test_using()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Create_Index('a', 'b');

		$this->assertSame($command, $command->using('btree'));
		$this->assertSame("CREATE INDEX `a` ON `b` () USING BTREE", $db->quote($command));
	}
}
