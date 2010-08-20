<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Update_Test extends PHPUnit_Framework_TestCase
{
	public function test_table()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Update;

		$this->assertSame($query, $query->table('one', 'a'));

		$this->assertSame('UPDATE "pre_one" AS "a" SET ', $db->quote($query));
	}

	public function test_set()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Update('one');

		$this->assertSame($query, $query->set(array('x' => 0, 'y' => 1)));
		$this->assertSame('UPDATE "pre_one" SET "x" = 0, "y" = 1', $db->quote($query));

		$this->assertSame($query, $query->set(new Database_Expression('arbitrary')));
		$this->assertSame('UPDATE "pre_one" SET arbitrary', $db->quote($query));
	}

	public function test_value()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Update('one');

		$this->assertSame($query, $query->value('x', 0));
		$this->assertSame('UPDATE "pre_one" SET "x" = 0', $db->quote($query));

		$this->assertSame($query, $query->value('y', 1));
		$this->assertSame('UPDATE "pre_one" SET "x" = 0, "y" = 1', $db->quote($query));
	}

	public function test_from()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Update('one', 'a', array('x' => 0));

		$this->assertSame($query, $query->from('two', 'b'), 'Chainable (table)');
		$this->assertSame('UPDATE "pre_one" AS "a" SET "x" = 0 FROM "pre_two" AS "b"', $db->quote($query));

		$from = new Database_From('two', 'b');
		$from->join('three', 'c');

		$this->assertSame($query, $query->from($from), 'Chainable (from)');
		$this->assertSame('UPDATE "pre_one" AS "a" SET "x" = 0 FROM "pre_two" AS "b" JOIN "pre_three" AS "c"', $db->quote($query));
	}

	public function test_where()
	{
		$db = $this->sharedFixture;
		$query = new Database_Command_Update('one', NULL, array('x' => 0));

		$this->assertSame($query, $query->where(new Database_Conditions(new Database_Column('y'), '=', 1)), 'Chainable (conditions)');
		$this->assertSame('UPDATE "pre_one" SET "x" = 0 WHERE "y" = 1', $db->quote($query));

		$this->assertSame($query, $query->where('y', '=', 0), 'Chainable (operands)');
		$this->assertSame('UPDATE "pre_one" SET "x" = 0 WHERE "y" = 0', $db->quote($query));

		$conditions = new Database_Conditions;
		$conditions->open(NULL)->add(NULL, new Database_Column('y'), '=', 0)->close();

		$this->assertSame($query, $query->where($conditions, '=', TRUE), 'Chainable (conditions as operand)');
		$this->assertSame('UPDATE "pre_one" SET "x" = 0 WHERE ("y" = 0) = \'1\'', $db->quote($query));
	}
}
