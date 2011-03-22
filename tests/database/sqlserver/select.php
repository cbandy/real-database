<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.sqlserver
 */
class Database_SQLServer_Select_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLServer_Select::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_SQLServer_Select;
		$statement
			->distinct()
			->from('a')
			->where('b', '=', 0)
			->group_by(array('c'))
			->having('d', '=', 1)
			->order_by('e');

		$this->assertSame(
			'SELECT :distinct :columns FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby',
			(string) $statement
		);
	}

	/**
	 * @covers  Database_SQLServer_Select::__toString
	 */
	public function test_toString_limit()
	{
		$statement = new Database_SQLServer_Select;
		$statement
			->limit(1);

		$this->assertSame(
			'SELECT TOP (:limit) :columns', (string) $statement
		);
	}

	/**
	 * @covers  Database_SQLServer_Select::__toString
	 */
	public function test_toString_offset()
	{
		$statement = new Database_SQLServer_Select;
		$statement
			->offset(1);

		$table = 'kohana_af8304e0ab880429910aaec9a9cbcb7b83b507bc';
		$this->assertSame(
			'SELECT * FROM (SELECT :columns, ROW_NUMBER() OVER(ORDER BY :orderby) AS kohana_row_number) AS '.$table.' WHERE '.$table.'.kohana_row_number > :offset',
			(string) $statement
		);
	}

	/**
	 * @covers  Database_SQLServer_Select::__toString
	 */
	public function test_toString_offset_limit()
	{
		$statement = new Database_SQLServer_Select;
		$statement
			->limit(1)
			->offset(2);

		$table = 'kohana_e2d27645af3bfea9612b845e11fa08f221aecf0a';
		$this->assertSame(
			'SELECT * FROM (SELECT :columns, ROW_NUMBER() OVER(ORDER BY :orderby) AS kohana_row_number) AS '.$table.' WHERE '.$table.'.kohana_row_number > :offset AND '.$table.'.kohana_row_number <= (:offset + :limit)',
			(string) $statement
		);
	}
}
