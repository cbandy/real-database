<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_Base_Conditions_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Conditions::__construct
	 */
	public function test_constructor()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::add
	 */
	public function test_add()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->add('and', 'a', '=', 0));
		$this->assertSame("'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->add('or', 'b', '<>', 1));
		$this->assertSame("'a' = 0 OR 'b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->add('and', 'c', '>', 2));
		$this->assertSame("'a' = 0 OR 'b' <> 1 AND 'c' > 2", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::_add_rhs
	 */
	public function test_add_between()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions('2009-11-19', 'between', array('2009-11-1', '2009-12-1'));

		$this->assertSame("'2009-11-19' BETWEEN '2009-11-1' AND '2009-12-1'", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::_add_rhs
	 */
	public function test_add_in()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions(new Database_Identifier('a'), 'in', array('x', 5, new Database_Identifier('z')));

		$this->assertSame('"a" IN (\'x\', 5, "z")', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_column
	 */
	public function test_and_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_column('a', '=', 0));
		$this->assertSame('"a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_column('b', '<>', 1));
		$this->assertSame('"a" = 0 AND "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_columns
	 */
	public function test_and_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_columns('a', '=', 'b'));
		$this->assertSame('"a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_columns('c', '<>', 'd'));
		$this->assertSame('"a" = "b" AND "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_not
	 */
	public function test_and_not()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_not('a', '=', 0));
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not('b', '<>', 1));
		$this->assertSame("NOT 'a' = 0 AND NOT 'b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_not_column
	 */
	public function test_and_not_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_not_column('a', '=', 0));
		$this->assertSame('NOT "a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_column('b', '<>', 1));
		$this->assertSame('NOT "a" = 0 AND NOT "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_not_columns
	 */
	public function test_and_not_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_not_columns('a', '=', 'b'));
		$this->assertSame('NOT "a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_columns('c', '<>', 'd'));
		$this->assertSame('NOT "a" = "b" AND NOT "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_not_open
	 */
	public function test_and_not_open()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_not_open());
		$this->assertSame("NOT (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open('a', '=', 0));
		$this->assertSame("NOT (NOT ('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open('b', '<>', 1));
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_not_open_column
	 */
	public function test_and_not_open_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_not_open_column('a', '=', 0));
		$this->assertSame('NOT ("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open_column('b', '<>', 1));
		$this->assertSame('NOT ("a" = 0 AND NOT ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_not_open_columns
	 */
	public function test_and_not_open_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_not_open_columns('a', '=', 'b'));
		$this->assertSame('NOT ("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open_columns('c', '<>', 'd'));
		$this->assertSame('NOT ("a" = "b" AND NOT ("c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_open
	 */
	public function test_and_open()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_open());
		$this->assertSame("(", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open('a', '=', 0));
		$this->assertSame("(('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open('b', '<>', 1));
		$this->assertSame("(('a' = 0 AND ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_open_column
	 */
	public function test_and_open_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_open_column('a', '=', 0));
		$this->assertSame('("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open_column('b', '<>', 1));
		$this->assertSame('("a" = 0 AND ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::and_open_columns
	 */
	public function test_and_open_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->and_open_columns('a', '=', 'b'));
		$this->assertSame('("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open_columns('c', '<>', 'd'));
		$this->assertSame('("a" = "b" AND ("c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::close
	 */
	public function test_close()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame(')', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('))', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::column
	 */
	public function test_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->column('and', 'a', '=', 0));
		$this->assertSame('"a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->column('and', 'b', '<>', 1));
		$this->assertSame('"a" = 0 AND "b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->column('or', 'c', 'is', NULL));
		$this->assertSame('"a" = 0 AND "b" <> 1 OR "c" IS NULL', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::columns
	 */
	public function test_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->columns('and', 'a', '=', 'b'));
		$this->assertSame('"a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->columns('and', 'c', '<>', 'd'));
		$this->assertSame('"a" = "b" AND "c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->columns('or', 'e', '=', 'f'));
		$this->assertSame('"a" = "b" AND "c" <> "d" OR "e" = "f"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::not
	 */
	public function test_not()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->not('and', 'a', '=', 0));
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not('or', 'b', '<>', 1));
		$this->assertSame("NOT 'a' = 0 OR NOT 'b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not('and', 'c', '>', 2));
		$this->assertSame("NOT 'a' = 0 OR NOT 'b' <> 1 AND NOT 'c' > 2", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::not_column
	 */
	public function test_not_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->not_column('and', 'a', '=', 0));
		$this->assertSame('NOT "a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_column('or', 'b', '<>', 1));
		$this->assertSame('NOT "a" = 0 OR NOT "b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_column('and', 'c', '>', 2));
		$this->assertSame('NOT "a" = 0 OR NOT "b" <> 1 AND NOT "c" > 2', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::not_columns
	 */
	public function test_not_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->not_columns('and', 'a', '=', 'b'));
		$this->assertSame('NOT "a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_columns('or', 'c', '<>', 'd'));
		$this->assertSame('NOT "a" = "b" OR NOT "c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_columns('and', 'e', '>', 'f'));
		$this->assertSame('NOT "a" = "b" OR NOT "c" <> "d" AND NOT "e" > "f"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::not_open
	 */
	public function test_not_open()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->not_open('and'));
		$this->assertSame("NOT (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open('or', 'a', '=', 0));
		$this->assertSame("NOT (NOT ('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open('and', 'b', '<>', 1));
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open('or', 'c', '>', 2));
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1 OR NOT ('c' > 2", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::not_open_column
	 */
	public function test_not_open_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->not_open_column('and', 'a', '=', 0));
		$this->assertSame('NOT ("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_column('or', 'b', '<>', 1));
		$this->assertSame('NOT ("a" = 0 OR NOT ("b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_column('and', 'c', '>', 2));
		$this->assertSame('NOT ("a" = 0 OR NOT ("b" <> 1 AND NOT ("c" > 2', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::not_open_columns
	 */
	public function test_not_open_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->not_open_columns('and', 'a', '=', 'b'));
		$this->assertSame('NOT ("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_columns('or', 'c', '<>', 'd'));
		$this->assertSame('NOT ("a" = "b" OR NOT ("c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_columns('and', 'e', '>', 'f'));
		$this->assertSame('NOT ("a" = "b" OR NOT ("c" <> "d" AND NOT ("e" > "f"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::open
	 */
	public function test_open()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->open('and'));
		$this->assertSame("(", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('or', 'a', '=', 0));
		$this->assertSame("(('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('and', 'b', '<>', 1));
		$this->assertSame("(('a' = 0 AND ('b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('or', 'c', '>', 2));
		$this->assertSame("(('a' = 0 AND ('b' <> 1 OR ('c' > 2", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::open_column
	 */
	public function test_open_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->open_column('and', 'a', '=', 0));
		$this->assertSame('("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_column('or', 'b', '<>', 1));
		$this->assertSame('("a" = 0 OR ("b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_column('and', 'c', '>', 2));
		$this->assertSame('("a" = 0 OR ("b" <> 1 AND ("c" > 2', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::open_columns
	 */
	public function test_open_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->open_columns('and', 'a', '=', 'b'));
		$this->assertSame('("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_columns('or', 'c', '<>', 'd'));
		$this->assertSame('("a" = "b" OR ("c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_columns('and', 'e', '>', 'f'));
		$this->assertSame('("a" = "b" OR ("c" <> "d" AND ("e" > "f"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_column
	 */
	public function test_or_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_column('a', '=', 0));
		$this->assertSame('"a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_column('b', '<>', 1));
		$this->assertSame('"a" = 0 OR "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_columns
	 */
	public function test_or_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_columns('a', '=', 'b'));
		$this->assertSame('"a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_columns('c', '<>', 'd'));
		$this->assertSame('"a" = "b" OR "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_not
	 */
	public function test_or_not()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_not('a', '=', 0));
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not('b', '<>', 1));
		$this->assertSame("NOT 'a' = 0 OR NOT 'b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_not_column
	 */
	public function test_or_not_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_not_column('a', '=', 0));
		$this->assertSame('NOT "a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_column('b', '<>', 1));
		$this->assertSame('NOT "a" = 0 OR NOT "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_not_columns
	 */
	public function test_or_not_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_not_columns('a', '=', 'b'));
		$this->assertSame('NOT "a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_columns('c', '<>', 'd'));
		$this->assertSame('NOT "a" = "b" OR NOT "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_not_open
	 */
	public function test_or_not_open()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_not_open());
		$this->assertSame("NOT (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open('a', '=', 0));
		$this->assertSame("NOT (NOT ('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open('b', '<>', 1));
		$this->assertSame("NOT (NOT ('a' = 0 OR NOT ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_not_open_column
	 */
	public function test_or_not_open_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_not_open_column('a', '=', 0));
		$this->assertSame('NOT ("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open_column('b', '<>', 1));
		$this->assertSame('NOT ("a" = 0 OR NOT ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_not_open_columns
	 */
	public function test_or_not_open_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_not_open_columns('a', '=', 'b'));
		$this->assertSame('NOT ("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open_columns('c', '<>', 'd'));
		$this->assertSame('NOT ("a" = "b" OR NOT ("c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_open
	 */
	public function test_or_open()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_open());
		$this->assertSame("(", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open('a', '=', 0));
		$this->assertSame("(('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open('b', '<>', 1));
		$this->assertSame("(('a' = 0 OR ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_open_column
	 */
	public function test_or_open_column()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_open_column('a', '=', 0));
		$this->assertSame('("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open_column('b', '<>', 1));
		$this->assertSame('("a" = 0 OR ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  Database_Conditions::or_open_columns
	 */
	public function test_or_open_columns()
	{
		$db = $this->sharedFixture;
		$conditions = new Database_Conditions;

		$this->assertSame($conditions, $conditions->or_open_columns('a', '=', 'b'));
		$this->assertSame('("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open_columns('c', '<>', 'd'));
		$this->assertSame('("a" = "b" OR ("c" <> "d"', $db->quote($conditions));
	}
}
