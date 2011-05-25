<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_SQL_Conditions_Helpers_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_Conditions::and_column
	 */
	public function test_and_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_column('a', '=', 0));
		$this->assertSame('"a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_column('b', '<>', 1));
		$this->assertSame('"a" = 0 AND "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_columns
	 */
	public function test_and_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_columns('a', '=', 'b'));
		$this->assertSame('"a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_columns('c', '<>', 'd'));
		$this->assertSame('"a" = "b" AND "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_exists
	 */
	public function test_and_exists()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_exists('a'));
		$this->assertSame('EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_exists('b'));
		$this->assertSame('EXISTS (a) AND EXISTS (b)', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_not
	 */
	public function test_and_not()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_not('a', '=', 0));
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not('b', '<>', 1));
		$this->assertSame("NOT 'a' = 0 AND NOT 'b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_not_column
	 */
	public function test_and_not_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_not_column('a', '=', 0));
		$this->assertSame('NOT "a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_column('b', '<>', 1));
		$this->assertSame('NOT "a" = 0 AND NOT "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_not_columns
	 */
	public function test_and_not_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_not_columns('a', '=', 'b'));
		$this->assertSame('NOT "a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_columns('c', '<>', 'd'));
		$this->assertSame('NOT "a" = "b" AND NOT "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_not_exists
	 */
	public function test_and_not_exists()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_not_exists('a'));
		$this->assertSame('NOT EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_exists('b'));
		$this->assertSame('NOT EXISTS (a) AND NOT EXISTS (b)', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_not_open
	 */
	public function test_and_not_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_not_open());
		$this->assertSame("NOT (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open('a', '=', 0));
		$this->assertSame("NOT (NOT ('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open('b', '<>', 1));
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_not_open_column
	 */
	public function test_and_not_open_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_not_open_column('a', '=', 0));
		$this->assertSame('NOT ("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open_column('b', '<>', 1));
		$this->assertSame('NOT ("a" = 0 AND NOT ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_not_open_columns
	 */
	public function test_and_not_open_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_not_open_columns('a', '=', 'b'));
		$this->assertSame('NOT ("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_not_open_columns('c', '<>', 'd'));
		$this->assertSame('NOT ("a" = "b" AND NOT ("c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_open
	 */
	public function test_and_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_open());
		$this->assertSame("(", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open('a', '=', 0));
		$this->assertSame("(('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open('b', '<>', 1));
		$this->assertSame("(('a' = 0 AND ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_open_column
	 */
	public function test_and_open_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_open_column('a', '=', 0));
		$this->assertSame('("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open_column('b', '<>', 1));
		$this->assertSame('("a" = 0 AND ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::and_open_columns
	 */
	public function test_and_open_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->and_open_columns('a', '=', 'b'));
		$this->assertSame('("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->and_open_columns('c', '<>', 'd'));
		$this->assertSame('("a" = "b" AND ("c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_column
	 */
	public function test_or_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_column('a', '=', 0));
		$this->assertSame('"a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_column('b', '<>', 1));
		$this->assertSame('"a" = 0 OR "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_columns
	 */
	public function test_or_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_columns('a', '=', 'b'));
		$this->assertSame('"a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_columns('c', '<>', 'd'));
		$this->assertSame('"a" = "b" OR "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_exists
	 */
	public function test_or_exists()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_exists('a'));
		$this->assertSame('EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_exists('b'));
		$this->assertSame('EXISTS (a) OR EXISTS (b)', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_not
	 */
	public function test_or_not()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_not('a', '=', 0));
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not('b', '<>', 1));
		$this->assertSame("NOT 'a' = 0 OR NOT 'b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_not_column
	 */
	public function test_or_not_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_not_column('a', '=', 0));
		$this->assertSame('NOT "a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_column('b', '<>', 1));
		$this->assertSame('NOT "a" = 0 OR NOT "b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_not_columns
	 */
	public function test_or_not_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_not_columns('a', '=', 'b'));
		$this->assertSame('NOT "a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_columns('c', '<>', 'd'));
		$this->assertSame('NOT "a" = "b" OR NOT "c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_not_exists
	 */
	public function test_or_not_exists()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_not_exists('a'));
		$this->assertSame('NOT EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_exists('b'));
		$this->assertSame('NOT EXISTS (a) OR NOT EXISTS (b)', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_not_open
	 */
	public function test_or_not_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_not_open());
		$this->assertSame("NOT (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open('a', '=', 0));
		$this->assertSame("NOT (NOT ('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open('b', '<>', 1));
		$this->assertSame("NOT (NOT ('a' = 0 OR NOT ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_not_open_column
	 */
	public function test_or_not_open_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_not_open_column('a', '=', 0));
		$this->assertSame('NOT ("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open_column('b', '<>', 1));
		$this->assertSame('NOT ("a" = 0 OR NOT ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_not_open_columns
	 */
	public function test_or_not_open_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_not_open_columns('a', '=', 'b'));
		$this->assertSame('NOT ("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_not_open_columns('c', '<>', 'd'));
		$this->assertSame('NOT ("a" = "b" OR NOT ("c" <> "d"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_open
	 */
	public function test_or_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_open());
		$this->assertSame("(", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open('a', '=', 0));
		$this->assertSame("(('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open('b', '<>', 1));
		$this->assertSame("(('a' = 0 OR ('b' <> 1", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_open_column
	 */
	public function test_or_open_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_open_column('a', '=', 0));
		$this->assertSame('("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open_column('b', '<>', 1));
		$this->assertSame('("a" = 0 OR ("b" <> 1', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::or_open_columns
	 */
	public function test_or_open_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->or_open_columns('a', '=', 'b'));
		$this->assertSame('("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->or_open_columns('c', '<>', 'd'));
		$this->assertSame('("a" = "b" OR ("c" <> "d"', $db->quote($conditions));
	}
}
