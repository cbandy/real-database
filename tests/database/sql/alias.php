<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_SQL_Alias_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(NULL, NULL, 'NULL AS ""'),

			array(0, 'a', '0 AS "a"'),
			array(1, array('a'), '1 AS "a"'),
			array(5, new SQL_Expression('a'), '5 AS a'),
			array(6, new SQL_Identifier('a'), '6 AS "a"'),
		);
	}

	/**
	 * @covers  SQL_Alias::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   mixed                                       $value
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias
	 * @param   string                                      $expected
	 */
	public function test_constructor($value, $alias, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame($expected, $db->quote(new SQL_Alias($value, $alias)));
	}
}
