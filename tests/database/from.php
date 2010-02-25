<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.from
 */
class Database_From_Test extends PHPUnit_Framework_TestCase
{
	public function test_many()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_Query_From('one', 'a');

		$this->assertSame('"pre_one" AS "a"', $db->quote($from));

		$this->assertSame($from, $from->add('two', 'b'));
		$this->assertSame('"pre_one" AS "a", "pre_two" AS "b"', $db->quote($from));
	}

	public function test_join()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_Query_From('one', 'a');

		$this->assertSame($from, $from->join('two', 'b'));
		$this->assertSame('"pre_one" AS "a" JOIN "pre_two" AS "b"', $db->quote($from));

		$conditions = new Database_Query_Conditions;
		$conditions->add('and', new Database_Column('one.x'), '=', new Database_Column('two.x'));

		$this->assertSame($from, $from->on($conditions));
		$this->assertSame('"pre_one" AS "a" JOIN "pre_two" AS "b" ON ("pre_one"."x" = "pre_two"."x")', $db->quote($from));
	}

	public function test_parentheses()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_Query_From;

		$this->assertSame($from, $from->open());
		$this->assertSame('(', $db->quote($from));

		$from->add('one', 'a');
		$this->assertSame('("pre_one" AS "a"', $db->quote($from));

		$this->assertSame($from, $from->open());
		$this->assertSame('("pre_one" AS "a", (', $db->quote($from));

		$from->add('two');
		$this->assertSame('("pre_one" AS "a", ("pre_two"', $db->quote($from));

		$from->join('three');
		$this->assertSame('("pre_one" AS "a", ("pre_two" JOIN "pre_three"', $db->quote($from));

		$this->assertSame($from, $from->close());
		$this->assertSame('("pre_one" AS "a", ("pre_two" JOIN "pre_three")', $db->quote($from));

		$this->assertSame($from, $from->close());
		$this->assertSame('("pre_one" AS "a", ("pre_two" JOIN "pre_three"))', $db->quote($from));
	}
}

class Database_From_Test_DB extends Database
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
