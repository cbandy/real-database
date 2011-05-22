<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Select_Test extends PHPUnit_Framework_TestCase
{
	public function provider_distinct()
	{
		return array(
			array(array(), 'SELECT DISTINCT '),

			array(array(TRUE), 'SELECT DISTINCT '),
			array(array(FALSE), 'SELECT '),
			array(array(NULL), 'SELECT '),

			array(
				array(array('a')),
				'SELECT DISTINCT ON ("a") ',
			),
			array(
				array(array('a', 'b')),
				'SELECT DISTINCT ON ("a", "b") ',
			),

			array(
				array(array(new SQL_Column('a'))),
				'SELECT DISTINCT ON ("a") ',
			),
			array(
				array(array(new SQL_Column('a'), new SQL_Column('b'))),
				'SELECT DISTINCT ON ("a", "b") ',
			),

			array(
				array(array(new SQL_Expression('a'))),
				'SELECT DISTINCT ON (a) ',
			),
			array(
				array(array(new SQL_Expression('a'), new SQL_Expression('b'))),
				'SELECT DISTINCT ON (a, b) ',
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Select::distinct
	 *
	 * @dataProvider    provider_distinct
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_distinct($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_PostgreSQL_Select;

		$result = call_user_func_array(array($statement, 'distinct'), $arguments);

		$this->assertSame($statement, $result, 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  Database_PostgreSQL_Select::distinct
	 *
	 * @dataProvider    provider_distinct
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_distinct_reset($arguments)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_PostgreSQL_Select;

		call_user_func_array(array($statement, 'distinct'), $arguments);

		$statement->distinct(NULL);

		$this->assertSame('SELECT ', $db->quote($statement));
	}

	/**
	 * @covers  Database_PostgreSQL_Select::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_PostgreSQL_Select;
		$statement
			->distinct()
			->select(array('a'))
			->from('b')
			->where('c', '=', 'd')
			->group_by(array('e'))
			->having('f', '=', 'g')
			->order_by('h')
			->limit(1)
			->offset(1);

		$this->assertSame(
			'SELECT DISTINCT :columns FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $statement
		);

		$statement->distinct(array('i'));

		$this->assertSame(
			'SELECT DISTINCT ON (:distinct) :columns FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $statement
		);
	}
}
