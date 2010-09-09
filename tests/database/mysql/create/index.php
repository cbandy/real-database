<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Create_Index_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table('b');

		$this->assertSame("CREATE INDEX `a` ON $table ()", $db->quote(new Database_MySQL_Create_Index('a', 'b')));
		$this->assertSame("CREATE INDEX `a` ON $table (`c`)", $db->quote(new Database_MySQL_Create_Index('a', 'b', array('c'))));
	}

	public function test_type()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->type('fulltext'));
		$this->assertSame("CREATE FULLTEXT INDEX `a` ON $table ()", $db->quote($command));
	}

	public function test_using()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->using('btree'));
		$this->assertSame("CREATE INDEX `a` ON $table () USING BTREE", $db->quote($command));
	}
}
