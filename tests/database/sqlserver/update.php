<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.sqlserver
 */
class Database_SQLServer_Update_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLServer_Update::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_SQLServer_Update;
		$statement
			->table('a')
			->set(array('b' => 0))
			->from('c')
			->where('d', '=', 1)
			->limit(2)
			->returning(array('e'));

		$this->assertSame(
			'UPDATE TOP (:limit) :table SET :values OUTPUT :returning FROM :from WHERE :where',
			(string) $statement
		);
	}
}
