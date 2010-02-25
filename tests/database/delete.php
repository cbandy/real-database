<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.delete
 */
class Database_Delete_Test extends PHPUnit_Framework_TestCase
{
	public function test_from()
	{
		$db = new Database_Delete_Test_DB;
		$query = new Database_Query_Delete;

		$this->assertSame($query, $query->from('one', 'a'));

		$this->assertSame('DELETE FROM "pre_one" AS "a"', $db->quote($query));
	}

	public function test_using()
	{
		$db = new Database_Delete_Test_DB;
		$query = new Database_Query_Delete('one');

		$this->assertSame($query, $query->using('two', 'b'));
		$this->assertSame('DELETE FROM "pre_one" USING "pre_two" AS "b"', $db->quote($query));

		$this->assertSame($query, $query->using('three'));
		$this->assertSame('DELETE FROM "pre_one" USING "pre_two" AS "b", "pre_three"', $db->quote($query));

		$query->join('four');
		$this->assertSame('DELETE FROM "pre_one" USING "pre_two" AS "b", "pre_three" JOIN "pre_four"', $db->quote($query));

		$query->on(new Database_Query_Conditions(new Database_Column('four.x'), '=', 0));
		$this->assertSame('DELETE FROM "pre_one" USING "pre_two" AS "b", "pre_three" JOIN "pre_four" ON ("pre_four"."x" = 0)', $db->quote($query));
	}

	public function test_where()
	{
		$db = new Database_Delete_Test_DB;
		$query = new Database_Query_Delete('one');

		$this->assertSame($query, $query->where(new Database_Query_Conditions(new Database_Column('one.x'), '=', 0)));

		$this->assertSame('DELETE FROM "pre_one" WHERE "pre_one"."x" = 0', $db->quote($query));
	}
}

class Database_Delete_Test_DB extends Database
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
