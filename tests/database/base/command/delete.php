<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Command_Delete_Test extends PHPUnit_Framework_TestCase
{
	public function test_from()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Delete;

		$this->assertSame($query, $query->from('one', 'a'));

		$this->assertSame('DELETE FROM "pre_one" AS "a"', $db->quote($query));
	}

	public function test_limit()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Delete('one');

		$this->assertSame($query, $query->limit(5));
		$this->assertSame('DELETE FROM "pre_one" LIMIT 5', $db->quote($query));

		$this->assertSame($query, $query->limit(NULL));
		$this->assertSame('DELETE FROM "pre_one"', $db->quote($query));

		$this->assertSame($query, $query->limit(0));
		$this->assertSame('DELETE FROM "pre_one" LIMIT 0', $db->quote($query));
	}

	public function test_using()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Delete('one');

		$this->assertSame($query, $query->using('two', 'b'), 'Chainable (table)');
		$this->assertSame('DELETE FROM "pre_one" USING "pre_two" AS "b"', $db->quote($query));

		$from = new Database_From('two', 'b');
		$from->add('three')->join('four');

		$this->assertSame($query, $query->using($from), 'Chainable (from)');
		$this->assertSame('DELETE FROM "pre_one" USING "pre_two" AS "b", "pre_three" JOIN "pre_four"', $db->quote($query));
	}

	public function test_where()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Delete('one');

		$this->assertSame($query, $query->where(new Database_Conditions(new Database_Column('one.x'), '=', 0)), 'Chainable (conditions)');
		$this->assertSame('DELETE FROM "pre_one" WHERE "pre_one"."x" = 0', $db->quote($query));

		$this->assertSame($query, $query->where('one.x', '=', 1), 'Chainable (operands)');
		$this->assertSame('DELETE FROM "pre_one" WHERE "pre_one"."x" = 1', $db->quote($query));

		$conditions = new Database_Conditions;
		$conditions->open(NULL)->add(NULL, new Database_Column('one.x'), '=', 0)->close();

		$this->assertSame($query, $query->where($conditions, '=', TRUE), 'Chainable (conditions as operand)');
		$this->assertSame('DELETE FROM "pre_one" WHERE ("pre_one"."x" = 0) = \'1\'', $db->quote($query));
	}
}
