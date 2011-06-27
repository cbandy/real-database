<?php
/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Select_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_MySQL_Select::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_MySQL_Select;
		$statement
			->distinct()
			->where('a', '=', 'b')
			->group_by(array('c'))
			->having('d', '=', 'e')
			->order_by('f')
			->limit(1);

		$this->assertSame(
			'SELECT DISTINCT * FROM DUAL WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $statement
		);

		$statement->from('g');

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $statement
		);
	}

	/**
	 * @covers  Database_MySQL_Select::__toString
	 */
	public function test_toString_offset()
	{
		$statement = new Database_MySQL_Select;
		$statement->offset(1);

		$this->assertSame(
			'SELECT * LIMIT :offset,18446744073709551615',
			(string) $statement
		);

		$statement->limit(1);

		$this->assertSame(
			'SELECT * LIMIT :offset,:limit',
			(string) $statement
		);
	}
}
