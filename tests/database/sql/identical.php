<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_SQL_Identical_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(
				'a', '=', 'b',
				"(NOT ('a' <> 'b' OR 'a' IS NULL OR 'b' IS NULL) OR ('a' IS NULL AND 'b' IS NULL))",
			),
			array(
				'a', '<>', 'b',
				"(('a' <> 'b' OR 'a' IS NULL OR 'b' IS NULL) AND NOT ('a' IS NULL AND 'b' IS NULL))",
			),

			array(
				new SQL_Identifier('a'), '=', NULL,
				'(NOT ("a" <> NULL OR "a" IS NULL OR NULL IS NULL) OR ("a" IS NULL AND NULL IS NULL))',
			),
			array(
				new SQL_Identifier('a'), '<>', NULL,
				'(("a" <> NULL OR "a" IS NULL OR NULL IS NULL) AND NOT ("a" IS NULL AND NULL IS NULL))',
			),
		);
	}

	/**
	 * @covers  SQL_Identical::__construct
	 * @covers  SQL_Identical::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   mixed   $left       First argument to the constructor
	 * @param   string  $operator   Second argument to the constructor
	 * @param   mixed   $right      Third argument to the constructor
	 * @param   string  $expected
	 */
	public function test_constructor($left, $operator, $right, $expected)
	{
		$db = new SQL;

		$expression = new SQL_Identical($left, $operator, $right);

		$this->assertSame($expected, $db->quote($expression));
	}
}
