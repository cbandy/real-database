<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.update
 */
class Database_Update_Test extends PHPUnit_Framework_TestCase
{
	public function test_table()
	{
		$db = new Database_Update_Test_DB;
		$query = new Database_Command_Update;

		$this->assertSame($query, $query->table('one', 'a'));

		$this->assertSame('UPDATE "pre_one" AS "a" SET ', $db->quote($query));
	}

	public function test_set()
	{
		$db = new Database_Update_Test_DB;
		$query = new Database_Command_Update('one');

		$this->assertSame($query, $query->set(array('x' => 0, 'y' => 1)));
		$this->assertSame('UPDATE "pre_one" SET "x" = 0, "y" = 1', $db->quote($query));

		$this->assertSame($query, $query->set(new Database_Expression('arbitrary')));
		$this->assertSame('UPDATE "pre_one" SET arbitrary', $db->quote($query));
	}

	public function test_value()
	{
		$db = new Database_Update_Test_DB;
		$query = new Database_Command_Update('one');

		$this->assertSame($query, $query->value('x', 0));
		$this->assertSame('UPDATE "pre_one" SET "x" = 0', $db->quote($query));

		$this->assertSame($query, $query->value('y', 1));
		$this->assertSame('UPDATE "pre_one" SET "x" = 0, "y" = 1', $db->quote($query));
	}

	public function test_from()
	{
		$db = new Database_Update_Test_DB;
		$query = new Database_Command_Update('one', 'a', array('x' => 0));

		$from = new Database_From('two', 'b');
		$from->join('three', 'c');

		$this->assertSame($query, $query->from($from));
		$this->assertSame('UPDATE "pre_one" AS "a" SET "x" = 0 FROM "pre_two" AS "b" JOIN "pre_three" AS "c"', $db->quote($query));
	}

	public function test_where()
	{
		$db = new Database_Update_Test_DB;
		$query = new Database_Command_Update('one', NULL, array('x' => 0));

		$this->assertSame($query, $query->where(new Database_Conditions(new Database_Column('y'), '=', 1)));

		$this->assertSame('UPDATE "pre_one" SET "x" = 0 WHERE "y" = 1', $db->quote($query));
	}
}

class Database_Update_Test_DB extends Database
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
