<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.sqlserver
 */
class Database_SQLServer_Insert_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLServer_Insert::__toString
	 */
	public function test_toString()
	{
		$statement = new Database_SQLServer_Insert;
		$statement
			->into('a')
			->columns(array('b'))
			->returning(array('c'));

		$this->assertSame('INSERT INTO :table (:columns) OUTPUT :returning DEFAULT VALUES', (string) $statement);

		$statement->values(array('d'));

		$this->assertSame('INSERT INTO :table (:columns) OUTPUT :returning VALUES :values', (string) $statement);

		$statement->values(new SQL_Expression('e'));

		$this->assertSame('INSERT INTO :table (:columns) OUTPUT :returning :values', (string) $statement);
	}
}
