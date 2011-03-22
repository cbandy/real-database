<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Cached_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Query_Cached::__construct
	 * @covers  Database_Query_Cached::key
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		$cached = new Database_Query_Cached(5, $db, new Database_Query('query'));

		$this->assertSame('Database_Query_Cached(db,query,a:0:{},)', $cached->key());
	}

	/**
	 * @covers  Database_Query_Cached::delete
	 */
	public function test_delete()
	{
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$result = $this->getMock('Database_Result', array('as_array', 'current'), array(FALSE, 1));
		$array = array(array('kohana'));

		$db->expects($this->exactly(2))
			->method('execute_query')
			->will($this->returnValue($result));

		$result->expects($this->exactly(2))
			->method('as_array')
			->will($this->returnValue($array));

		$cached = new Database_Query_Cached(5, $db, new Database_Query('query'));

		// Cache the result
		$cached->execute();

		// Clear the cache
		$cached->delete();
		$this->assertType(get_class($result), $cached->execute(), 'Not cached');
	}

	/**
	 * @covers  Database_Query_Cached::execute
	 */
	public function test_execute()
	{
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$result = $this->getMock('Database_Result', array('as_array', 'current'), array(FALSE, 1));
		$array = array(array('kohana'));

		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		$result->expects($this->once())
			->method('as_array')
			->will($this->returnValue($array));

		$cached = new Database_Query_Cached(5, $db, new Database_Query('query'));

		// Clear the cache
		$cached->delete();

		$this->assertType(get_class($result), $cached->execute(), 'First execution not cached');
		$this->assertType('Database_Result_Array', $cached->execute(), 'Second execution cached');
	}
}
