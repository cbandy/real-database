<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Command_Insert_Test extends PHPUnit_Framework_TestCase
{
	public function test_into()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Insert;

		$this->assertSame($query, $query->into('one'));

		$this->assertSame('INSERT INTO "pre_one" DEFAULT VALUES', $db->quote($query));
	}

	public function test_columns()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Insert('one');

		$this->assertSame($query, $query->columns(array('a', new Database_Expression('b'), new Database_Column('c'))));

		$this->assertSame('INSERT INTO "pre_one" ("a", b, "c") DEFAULT VALUES', $db->quote($query));
	}

	public function test_values()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Insert('one', array('a','b'));

		$this->assertSame($query, $query->values(array(0,1), array(2,3)));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") VALUES (0, 1), (2, 3)', $db->quote($query));

		$this->assertSame($query, $query->values(new Database_Expression('SELECT query')));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") SELECT query', $db->quote($query));

		$this->assertSame($query, $query->values(NULL));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") DEFAULT VALUES', $db->quote($query));
	}
}