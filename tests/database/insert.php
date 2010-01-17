<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.insert
 */
class Database_Insert_Test extends PHPUnit_Framework_TestCase
{
	public function test_into()
	{
		$db = new Database_Insert_Test_DB;
		$query = new Database_Query_Insert;

		$query->into('one');

		$this->assertSame('INSERT INTO "pre_one" DEFAULT VALUES', $db->quote($query));
	}

	public function test_columns()
	{
		$db = new Database_Insert_Test_DB;
		$query = new Database_Query_Insert('one');

		$query->columns(array('a', new Database_Expression('b'), new Database_Column('c')));

		$this->assertSame('INSERT INTO "pre_one" ("a", b, "c") DEFAULT VALUES', $db->quote($query));
	}

	public function test_values()
	{
		$db = new Database_Insert_Test_DB;
		$query = new Database_Query_Insert('one', array('a','b'));

		$query->values(array(0,1), array(2,3));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") VALUES (0, 1), (2, 3)', $db->quote($query));

		$query->values(new Database_Expression('SELECT query'));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") SELECT query', $db->quote($query));

		$query->values(NULL);
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") DEFAULT VALUES', $db->quote($query));
	}
}

class Database_Insert_Test_DB extends Database
{
	public function escape($value)
	{
		return "'$value'";
	}

	public function table_prefix()
	{
		return 'pre_';
	}
}
