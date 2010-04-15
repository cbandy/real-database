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
		$query = new Database_Command_Insert;

		$this->assertSame($query, $query->into('one'));

		$this->assertSame('INSERT INTO "pre_one" DEFAULT VALUES', $db->quote($query));
	}

	public function test_columns()
	{
		$db = new Database_Insert_Test_DB;
		$query = new Database_Command_Insert('one');

		$this->assertSame($query, $query->columns(array('a', new Database_Expression('b'), new Database_Column('c'))));

		$this->assertSame('INSERT INTO "pre_one" ("a", b, "c") DEFAULT VALUES', $db->quote($query));
	}

	public function test_values()
	{
		$db = new Database_Insert_Test_DB;
		$query = new Database_Command_Insert('one', array('a','b'));

		$this->assertSame($query, $query->values(array(0,1), array(2,3)));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") VALUES (0, 1), (2, 3)', $db->quote($query));

		$this->assertSame($query, $query->values(new Database_Expression('SELECT query')));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") SELECT query', $db->quote($query));

		$this->assertSame($query, $query->values(NULL));
		$this->assertSame('INSERT INTO "pre_one" ("a", "b") DEFAULT VALUES', $db->quote($query));
	}

	public function test_prepare()
	{
		$db = new Database_Insert_Test_DB;
		$query = new Database_Command_Insert;

		$prepared = $query->prepare($db);

		$this->assertTrue($prepared instanceof Database_Prepared_Command);
	}
}

class Database_Insert_Test_DB extends Database
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
