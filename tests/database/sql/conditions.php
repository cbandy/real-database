<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_SQL_Conditions_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constuctor()
	{
		return array(
			array(array(), ''),

			array(array('a'),           "'a'"),
			array(array('b', 'c'),      "'b' C NULL"),
			array(array('d', 'e', 'f'), "'d' E 'f'"),

			array(array(NULL),              ''),
			array(array(NULL, 'g'),         'NULL G NULL'),
			array(array(NULL, 'h', 'i'),    "NULL H 'i'"),
		);
	}

	/**
	 * @covers  SQL_Conditions::__construct
	 *
	 * @dataProvider    provider_constuctor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = new SQL;

		$class = new ReflectionClass('SQL_Conditions');
		$conditions = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::_add_rhs
	 * @covers  SQL_Conditions::add
	 */
	public function test_add()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->add('and', 'a', '=', 0));
		$this->assertSame("'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->add('or', 'b', '<>', 1));
		$this->assertSame("'a' = 0 OR 'b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->add('and', 'c', '>', 2));
		$this->assertSame("'a' = 0 OR 'b' <> 1 AND 'c' > 2", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::_add_rhs
	 */
	public function test_add_between()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions(
			'2009-11-19', 'between', array('2009-11-1', '2009-12-1')
		);

		$this->assertSame("'2009-11-19' BETWEEN '2009-11-1' AND '2009-12-1'", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::_add_rhs
	 */
	public function test_add_in()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions(
			new SQL_Identifier('a'), 'in', array('x', 5, new SQL_Identifier('z'))
		);

		$this->assertSame('"a" IN (\'x\', 5, "z")', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::close
	 */
	public function test_close()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame(')', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('))', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::column
	 */
	public function test_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->column('and', 'a', '=', 0));
		$this->assertSame('"a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->column('and', 'b', '<>', 1));
		$this->assertSame('"a" = 0 AND "b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->column('or', 'c', 'is', NULL));
		$this->assertSame('"a" = 0 AND "b" <> 1 OR "c" IS NULL', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::columns
	 */
	public function test_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->columns('and', 'a', '=', 'b'));
		$this->assertSame('"a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->columns('and', 'c', '<>', 'd'));
		$this->assertSame('"a" = "b" AND "c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->columns('or', 'e', '=', 'f'));
		$this->assertSame('"a" = "b" AND "c" <> "d" OR "e" = "f"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::not
	 */
	public function test_not()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->not('and', 'a', '=', 0));
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not('or', 'b', '<>', 1));
		$this->assertSame("NOT 'a' = 0 OR NOT 'b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not('and', 'c', '>', 2));
		$this->assertSame("NOT 'a' = 0 OR NOT 'b' <> 1 AND NOT 'c' > 2", $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::not_column
	 */
	public function test_not_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->not_column('and', 'a', '=', 0));
		$this->assertSame('NOT "a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_column('or', 'b', '<>', 1));
		$this->assertSame('NOT "a" = 0 OR NOT "b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_column('and', 'c', '>', 2));
		$this->assertSame('NOT "a" = 0 OR NOT "b" <> 1 AND NOT "c" > 2', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::not_columns
	 */
	public function test_not_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->not_columns('and', 'a', '=', 'b'));
		$this->assertSame('NOT "a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_columns('or', 'c', '<>', 'd'));
		$this->assertSame('NOT "a" = "b" OR NOT "c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_columns('and', 'e', '>', 'f'));
		$this->assertSame('NOT "a" = "b" OR NOT "c" <> "d" AND NOT "e" > "f"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::not_open
	 */
	public function test_not_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

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
	 * @covers  SQL_Conditions::not_open_column
	 */
	public function test_not_open_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->not_open_column('and', 'a', '=', 0));
		$this->assertSame('NOT ("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_column('or', 'b', '<>', 1));
		$this->assertSame('NOT ("a" = 0 OR NOT ("b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_column('and', 'c', '>', 2));
		$this->assertSame('NOT ("a" = 0 OR NOT ("b" <> 1 AND NOT ("c" > 2', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::not_open_columns
	 */
	public function test_not_open_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->not_open_columns('and', 'a', '=', 'b'));
		$this->assertSame('NOT ("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_columns('or', 'c', '<>', 'd'));
		$this->assertSame('NOT ("a" = "b" OR NOT ("c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open_columns('and', 'e', '>', 'f'));
		$this->assertSame('NOT ("a" = "b" OR NOT ("c" <> "d" AND NOT ("e" > "f"', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::open
	 */
	public function test_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

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
	 * @covers  SQL_Conditions::open_column
	 */
	public function test_open_column()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->open_column('and', 'a', '=', 0));
		$this->assertSame('("a" = 0', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_column('or', 'b', '<>', 1));
		$this->assertSame('("a" = 0 OR ("b" <> 1', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_column('and', 'c', '>', 2));
		$this->assertSame('("a" = 0 OR ("b" <> 1 AND ("c" > 2', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions::open_columns
	 */
	public function test_open_columns()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions;

		$this->assertSame($conditions, $conditions->open_columns('and', 'a', '=', 'b'));
		$this->assertSame('("a" = "b"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_columns('or', 'c', '<>', 'd'));
		$this->assertSame('("a" = "b" OR ("c" <> "d"', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open_columns('and', 'e', '>', 'f'));
		$this->assertSame('("a" = "b" OR ("c" <> "d" AND ("e" > "f"', $db->quote($conditions));
	}
}
