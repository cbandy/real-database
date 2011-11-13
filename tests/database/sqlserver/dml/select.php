<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.sqlserver
 */
class Database_SQLServer_DML_Select_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLServer_DML_Select::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_SQLServer_DML_Select;
		$statement
			->distinct()
			->from('a')
			->where('b', '=', 0)
			->group_by(array('c'))
			->having('d', '=', 1)
			->order_by('e');

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby',
			(string) $statement
		);
	}

	/**
	 * @covers  Database_SQLServer_DML_Select::__toString
	 */
	public function test_toString_limit()
	{
		$statement = new Database_SQLServer_DML_Select;
		$statement
			->limit(1);

		$this->assertSame(
			'SELECT TOP (:limit) *', (string) $statement
		);
	}

	/**
	 * @covers  Database_SQLServer_DML_Select::__toString
	 */
	public function test_toString_offset()
	{
		$statement = new Database_SQLServer_DML_Select;
		$statement
			->offset(1);

		$table = 'kohana_6663b885a82bee0d830744a6054ec8c27753103d';
		$this->assertSame(
			'SELECT * FROM (SELECT *, ROW_NUMBER() OVER(ORDER BY :orderby) AS kohana_row_number) AS '.$table.' WHERE '.$table.'.kohana_row_number > :offset',
			(string) $statement
		);
	}

	/**
	 * @covers  Database_SQLServer_DML_Select::__toString
	 */
	public function test_toString_offset_limit()
	{
		$statement = new Database_SQLServer_DML_Select;
		$statement
			->limit(1)
			->offset(2);

		$table = 'kohana_a433f4765ca13efe109fdc3d82bd3093cb1bcaf4';
		$this->assertSame(
			'SELECT * FROM (SELECT *, ROW_NUMBER() OVER(ORDER BY :orderby) AS kohana_row_number) AS '.$table.' WHERE '.$table.'.kohana_row_number > :offset AND '.$table.'.kohana_row_number <= (:offset + :limit)',
			(string) $statement
		);
	}
}
