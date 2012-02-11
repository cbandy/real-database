<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_SQL_Conditions_Stack_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_Conditions_Stack::add
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_add()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack;

		$this->assertSame($conditions, $conditions->add('and', 'a', '=', 0));
		$this->assertSame("'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->add('or', 'b', '<>', 1));
		$this->assertSame("'a' = 0 OR 'b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_add_between()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack(
			'2009-11-19', 'between', array('2009-11-1', '2009-12-1')
		);

		$this->assertSame("'2009-11-19' BETWEEN '2009-11-1' AND '2009-12-1'", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_add_in()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack(
			new SQL_Identifier('a'), 'in', array('x', 5, new SQL_Identifier('z'))
		);

		$this->assertSame('"a" IN (\'x\', 5, "z")', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::close
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_close()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack;

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame(')', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('))', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame(')', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	public function provider_close_empty()
	{
		return array(
			array(SQL::conditions_stack(), '', ''),
			array(SQL::conditions_stack()->and_open(), '', '('),
			array(
				SQL::conditions_stack()->and_open('a', '=', 'b'),
				"('a' = 'b')",
				"('a' = 'b'",
			),
			array(SQL::conditions_stack()->and_not_open(), '', 'NOT ('),
			array(
				SQL::conditions_stack()->and_not_open('a', '=', 'b'),
				"NOT ('a' = 'b')",
				"NOT ('a' = 'b'",
			),

			array(SQL::conditions_stack('a', '=', 'b'), "'a' = 'b')", "'a' = 'b'"),
			array(
				SQL::conditions_stack('a', '=', 'b')->and_open(),
				"'a' = 'b'",
				"'a' = 'b' AND (",
			),
			array(
				SQL::conditions_stack('a', '=', 'b')->and_open('c', '=', 'd'),
				"'a' = 'b' AND ('c' = 'd')",
				"'a' = 'b' AND ('c' = 'd'",
			),
			array(
				SQL::conditions_stack('a', '=', 'b')->and_not_open(),
				"'a' = 'b'",
				"'a' = 'b' AND NOT (",
			),
			array(
				SQL::conditions_stack('a', '=', 'b')->and_not_open('c', '=', 'd'),
				"'a' = 'b' AND NOT ('c' = 'd')",
				"'a' = 'b' AND NOT ('c' = 'd'",
			),
		);
	}

	/**
	 * @covers  SQL_Conditions_Stack::close_empty
	 * @covers  SQL_Conditions_Stack::pop
	 *
	 * @dataProvider    provider_close_empty
	 *
	 * @param   SQL_Conditions_Stack    $conditions
	 * @param   string                  $closed
	 * @param   string                  $popped
	 */
	public function test_close_empty($conditions, $closed, $popped)
	{
		$db = new SQL;

		$this->assertSame($conditions, $conditions->close_empty());
		$this->assertSame($closed, $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame($popped, $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::exists
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_exists()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack;

		$this->assertSame($conditions, $conditions->exists('and', 'a'));
		$this->assertSame('EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->exists('and', 'b'));
		$this->assertSame('EXISTS (a) AND EXISTS (b)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::not
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_not()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack;

		$this->assertSame($conditions, $conditions->not('and', 'a', '=', 0));
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not('or', 'b', '<>', 1));
		$this->assertSame("NOT 'a' = 0 OR NOT 'b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("NOT 'a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::not_exists
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_not_exists()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack;

		$this->assertSame($conditions, $conditions->not_exists('and', 'a'));
		$this->assertSame('NOT EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_exists('and', 'b'));
		$this->assertSame('NOT EXISTS (a) AND NOT EXISTS (b)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('NOT EXISTS (a)', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::not_open
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_not_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack;

		$this->assertSame($conditions, $conditions->not_open('and'));
		$this->assertSame('NOT (', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open('or', 'a', '=', 0));
		$this->assertSame("NOT (NOT ('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open('and', 'b', '<>', 1));
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->not_open('or', 'c', '>', 2));
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1 OR NOT ('c' > 2", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1 OR NOT (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT ('b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("NOT (NOT ('a' = 0 AND NOT (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("NOT (NOT ('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('NOT (NOT (', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('NOT (', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}

	/**
	 * @covers  SQL_Conditions_Stack::open
	 * @covers  SQL_Conditions_Stack::pop
	 */
	public function test_open()
	{
		$db = new SQL;
		$conditions = new SQL_Conditions_Stack;

		$this->assertSame($conditions, $conditions->open('and'));
		$this->assertSame('(', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('or', 'a', '=', 0));
		$this->assertSame("(('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('and', 'b', '<>', 1));
		$this->assertSame("(('a' = 0 AND ('b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->open('or', 'c', '>', 2));
		$this->assertSame("(('a' = 0 AND ('b' <> 1 OR ('c' > 2", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("(('a' = 0 AND ('b' <> 1 OR (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("(('a' = 0 AND ('b' <> 1", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("(('a' = 0 AND (", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame("(('a' = 0", $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('((', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('(', $db->quote($conditions));

		$this->assertSame($conditions, $conditions->pop());
		$this->assertSame('', $db->quote($conditions));
	}
}
