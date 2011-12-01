<?php
/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DML_Delete_Test extends PHPUnit_Framework_TestCase
{
	public function provider_order_by()
	{
		return array(
			array(array(NULL), 'DELETE FROM `t`'),
			array(array(NULL, 'any'), 'DELETE FROM `t`'),
			array(array(NULL, new SQL_Expression('any')), 'DELETE FROM `t`'),

			array(
				array('a'),
				'DELETE FROM `t` ORDER BY `a`',
			),
			array(
				array('a', 'b'),
				'DELETE FROM `t` ORDER BY `a` B',
			),
			array(
				array('a', new SQL_Expression('b')),
				'DELETE FROM `t` ORDER BY `a` b',
			),

			array(
				array(new SQL_Column('a')),
				'DELETE FROM `t` ORDER BY `a`',
			),
			array(
				array(new SQL_Column('a'), 'b'),
				'DELETE FROM `t` ORDER BY `a` B',
			),
			array(
				array(new SQL_Column('a'), new SQL_Expression('b')),
				'DELETE FROM `t` ORDER BY `a` b',
			),

			array(
				array(new SQL_Expression('a')),
				'DELETE FROM `t` ORDER BY a'
			),
			array(
				array(new SQL_Expression('a'), 'b'),
				'DELETE FROM `t` ORDER BY a B'
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'DELETE FROM `t` ORDER BY a b'
			),
		);
	}

	/**
	 * @covers  Database_MySQL_DML_Delete::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_order_by($arguments, $expected)
	{
		$db = new SQL('t', '`');
		$statement = new Database_MySQL_DML_Delete;

		$result = call_user_func_array(array($statement, 'order_by'), $arguments);

		$this->assertSame($statement, $result, 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  Database_MySQL_DML_Delete::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_order_by_reset($arguments)
	{
		$db = new SQL('t', '`');
		$statement = new Database_MySQL_DML_Delete;

		call_user_func_array(array($statement, 'order_by'), $arguments);

		$statement->order_by(NULL);

		$this->assertSame('DELETE FROM `t`', $db->quote($statement));
	}

	/**
	 * @covers  Database_MySQL_DML_Delete::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_MySQL_DML_Delete;
		$statement
			->from('a')
			->using('b')
			->where('c', '=', 'd')
			->order_by('e', 'f')
			->limit(1);

		$this->assertSame(
			'DELETE FROM :table USING :using WHERE :where ORDER BY :orderby LIMIT :limit',
			(string) $statement
		);
	}
}
