<?php
/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_Select_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLite_Select::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_SQLite_Select;
		$statement
			->distinct()
			->from('a')
			->where('b', '=', 'c')
			->group_by(array('d'))
			->having('e', '=', 'f')
			->order_by('g')
			->limit(1);

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $statement
		);
	}

	/**
	 * @covers  Database_SQLite_Select::__toString
	 */
	public function test_toString_offset()
	{
		$statement = new Database_SQLite_Select;
		$statement->offset(1);

		$this->assertSame(
			'SELECT * LIMIT :offset,9223372036854775807',
			(string) $statement
		);

		$statement->limit(1);

		$this->assertSame(
			'SELECT * LIMIT :offset,:limit',
			(string) $statement
		);
	}
}
