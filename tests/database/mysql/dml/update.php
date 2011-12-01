<?php
/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DML_Update_Test extends PHPUnit_Framework_TestCase
{
	public function provider_order_by()
	{
		return array(
			array(array(NULL), 'UPDATE `t` SET '),
			array(array(NULL, 'any'), 'UPDATE `t` SET '),
			array(array(NULL, new SQL_Expression('any')), 'UPDATE `t` SET '),

			array(
				array('a'),
				'UPDATE `t` SET  ORDER BY `a`',
			),
			array(
				array('a', 'b'),
				'UPDATE `t` SET  ORDER BY `a` B',
			),
			array(
				array('a', new SQL_Expression('b')),
				'UPDATE `t` SET  ORDER BY `a` b',
			),

			array(
				array(new SQL_Column('a')),
				'UPDATE `t` SET  ORDER BY `a`',
			),
			array(
				array(new SQL_Column('a'), 'b'),
				'UPDATE `t` SET  ORDER BY `a` B',
			),
			array(
				array(new SQL_Column('a'), new SQL_Expression('b')),
				'UPDATE `t` SET  ORDER BY `a` b',
			),

			array(
				array(new SQL_Expression('a')),
				'UPDATE `t` SET  ORDER BY a'
			),
			array(
				array(new SQL_Expression('a'), 'b'),
				'UPDATE `t` SET  ORDER BY a B'
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'UPDATE `t` SET  ORDER BY a b'
			),
		);
	}

	/**
	 * @covers  Database_MySQL_DML_Update::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_order_by($arguments, $expected)
	{
		$db = new SQL('t', '`');
		$statement = new Database_MySQL_DML_Update;

		$result = call_user_func_array(array($statement, 'order_by'), $arguments);

		$this->assertSame($statement, $result, 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  Database_MySQL_DML_Update::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_order_by_reset($arguments)
	{
		$db = new SQL('t', '`');
		$statement = new Database_MySQL_DML_Update;

		call_user_func_array(array($statement, 'order_by'), $arguments);

		$statement->order_by(NULL);

		$this->assertSame('UPDATE `t` SET ', $db->quote($statement));
	}

	/**
	 * @covers  Database_MySQL_DML_Update::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_MySQL_DML_Update;
		$statement
			->table('a')
			->set(array('b' => 0))
			->from('c')
			->where('d', '=', 1)
			->order_by('e', 'f')
			->limit(2);

		$this->assertSame(
			'UPDATE :table SET :values WHERE :where ORDER BY :orderby LIMIT :limit',
			(string) $statement
		);
	}
}
