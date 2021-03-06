<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.sqlserver
 */
class Database_SQLServer_DML_Delete_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLServer_DML_Delete::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_SQLServer_DML_Delete;
		$statement
			->from('a')
			->using('b')
			->where('c', '=', 'd')
			->limit(1)
			->returning(array('e'));

		$this->assertSame('DELETE TOP (:limit) FROM :table OUTPUT :returning FROM :using WHERE :where', (string) $statement);
	}
}
