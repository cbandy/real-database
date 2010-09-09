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
	public function test_as_assoc()
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_query'));
		$query = new Database_Query('a');

		$this->assertSame($query, $query->as_assoc());

		$db->expects($this->once())
			->method('execute_query')
			->with($this->equalTo('a'), FALSE);

		$query->execute($db);
	}

	/**
	 * @dataProvider    provider_as_object
	 */
	public function test_as_object($sql, $as_object)
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_query'));
		$query = new Database_Query($sql);

		$this->assertSame($query, $query->as_object($as_object));

		$db->expects($this->once())
			->method('execute_query')
			->with($this->equalTo($sql), $as_object);

		$query->execute($db);
	}

	public function provider_as_object()
	{
		return array
		(
			array('a', FALSE),
			array('a', TRUE),
			array('a', 'b'),
		);
	}
}
