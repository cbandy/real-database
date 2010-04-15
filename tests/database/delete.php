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
		$query = new Database_Command_Delete;

		$this->assertSame($query, $query->from('one', 'a'));

		$this->assertSame('DELETE FROM "pre_one" AS "a"', $db->quote($query));
	}

	public function test_using()
	{
		$db = new Database_Delete_Test_DB;
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
		$db = new Database_Delete_Test_DB;
		$query = new Database_Command_Delete('one');

		$this->assertSame($query, $query->where(new Database_Conditions(new Database_Column('one.x'), '=', 0)));

		$this->assertSame('DELETE FROM "pre_one" WHERE "pre_one"."x" = 0', $db->quote($query));
	}

	public function test_prepare()
	{
		$db = new Database_Delete_Test_DB;
		$query = new Database_Command_Delete;

		$prepared = $query->prepare($db);

		$this->assertTrue($prepared instanceof Database_Prepared_Command);
	}
}

class Database_Delete_Test_DB extends Database
{
	public function __construct($name = NULL, $config = NULL) {}

	public function begin() {}

	public function commit() {}

	public function connect() {}

	public function disconnect() {}

	public function execute_command($statement) {}

	public function execute_query($statement, $as_object = FALSE) {}

	public function rollback() {}

	public function table_prefix()
	{
		return 'pre_';
	}
}
