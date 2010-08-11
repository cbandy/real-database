<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 * @dataProvider    provider_execute
	 */
	public function test_execute($sql)
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_query'));

		$db->expects($this->once())
			->method('execute_query')
			->with($this->equalTo($sql));

		$query = new Database_Query($sql);

		$query->execute($db);
	}

	public function provider_execute()
	{
		return array
		(
			array(''),
			array('SELECT * FROM t1'),
		);
	}
}
