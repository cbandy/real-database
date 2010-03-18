<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.conditions
 */
class Database_Conditions_Test extends PHPUnit_Framework_TestCase
{
	public function test_between()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Conditions('2009-11-19', 'between', array('2009-11-1', '2009-12-1'));

		$this->assertSame("'2009-11-19' BETWEEN '2009-11-1' AND '2009-12-1'", $db->quote($conditions));
	}

	public function test_column()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->column('and', 'a', '=', 0));
		$this->assertSame('"a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_column('b', '<>', 'c'));
		$this->assertSame('"a" = 0 AND "b" <> \'c\'', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_column('d', 'IS', NULL));
		$this->assertSame('"a" = 0 AND "b" <> \'c\' OR "d" IS NULL', $db->quote($conditions));
	}

	public function test_columns()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->columns('and', 'a', '=', 'b'));
		$this->assertSame('"a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_columns('c', '<>', 'd'));
		$this->assertSame('"a" = "b" AND "c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_columns('e', '=', 'f'));
		$this->assertSame('"a" = "b" AND "c" <> "d" OR "e" = "f"', $db->quote($conditions));
	}

	public function test_in()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Conditions(new Database_Identifier('a'), 'in', array('x', 5, new Database_Identifier('z')));

		$this->assertSame('"a" IN (\'x\', 5, "z")', $db->quote($conditions));
	}

	public function test_mixed()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Conditions(new Database_Identifier('a'), 'is', NULL);

		$conditions
			->add('and', new Database_Identifier('b'), '=', 'literal')
			->add('or', new Database_Expression('c'), 'operator', new Database_Expression('d'));

		$this->assertSame('"a" IS NULL AND "b" = \'literal\' OR c OPERATOR d', $db->quote($conditions));
	}

	public function test_parentheses()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Conditions;

		$conditions->add('and', 0, '<>', 1);
		$this->assertSame('0 <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('and'));
		$this->assertSame('0 <> 1 AND (', $db->quote($conditions));

		$conditions->add('or', 2, '=', 2);
		$this->assertSame('0 <> 1 AND (2 = 2', $db->quote($conditions));

		$conditions->add('or', 2, '=', 2);
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('or'));
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (', $db->quote($conditions));

		$conditions->add('and', 3, '<>', 4);
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('and', 5, '<>', 6));
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('or', 7, '<>', 8));
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6) OR (7 <> 8', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6) OR (7 <> 8)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_column('and', 'a', 'is', NULL));
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6) OR (7 <> 8) AND ("a" IS NULL', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6) OR (7 <> 8) AND ("a" IS NULL)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_columns('or', 'a', '=', 'b'));
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6) OR (7 <> 8) AND ("a" IS NULL) OR ("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4) AND (5 <> 6) OR (7 <> 8) AND ("a" IS NULL) OR ("a" = "b")', $db->quote($conditions));
	}

	public function test_parentheses_helpers()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_open());
		$this->assertSame('(', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open());
		$this->assertSame('((', $db->quote($conditions));

		$conditions->add('and', 0, '<>', 1);
		$this->assertSame('((0 <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open(2, '=', 2));
		$this->assertSame('((0 <> 1 AND (2 = 2', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open(3, '<>', 4));
		$this->assertSame('((0 <> 1 AND (2 = 2 OR (3 <> 4', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open_column('a', 'is', NULL));
		$this->assertSame('((0 <> 1 AND (2 = 2 OR (3 <> 4 AND ("a" IS NULL', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open_column('a', 'is', NULL));
		$this->assertSame('((0 <> 1 AND (2 = 2 OR (3 <> 4 AND ("a" IS NULL OR ("a" IS NULL', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open_columns('b', '=', 'c'));
		$this->assertSame('((0 <> 1 AND (2 = 2 OR (3 <> 4 AND ("a" IS NULL OR ("a" IS NULL AND ("b" = "c"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open_columns('c', '<>', 'd'));
		$this->assertSame('((0 <> 1 AND (2 = 2 OR (3 <> 4 AND ("a" IS NULL OR ("a" IS NULL AND ("b" = "c" OR ("c" <> "d"', $db->quote($conditions));
	}
}

class Database_Conditions_Test_DB extends Database
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
