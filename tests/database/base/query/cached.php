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
		$query = new Database_Query('query');

		$cached = new Database_Query_Cached(5, $db, $query);

		$this->assertSame('Database_Query_Cached(db,query,a:0:{},)', $cached->key());
	}

	/**
	 * @covers  Database_Query_Cached::delete
	 */
	public function test_delete()
	{
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$query = $this->getMock('Database_Query', array('execute'), array('query'));
		$result = $this->getMock('Database_Result', array('as_array', 'current'), array(FALSE));
		$array = array(array('kohana'));

		$query->expects($this->exactly(2))
			->method('execute')
			->will($this->returnValue($result));

		$result->expects($this->exactly(2))
			->method('as_array')
			->will($this->returnValue($array));

		$cached = new Database_Query_Cached(5, $db, $query);

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
		$query = $this->getMock('Database_Query', array('execute'), array('query'));
		$result = $this->getMock('Database_Result', array('as_array', 'current'), array(FALSE));
		$array = array(array('kohana'));

		$query->expects($this->once())
			->method('execute')
			->will($this->returnValue($result));

		$result->expects($this->once())
			->method('as_array')
			->will($this->returnValue($array));

		$cached = new Database_Query_Cached(5, $db, $query);

		// Clear the cache
		$cached->delete();

		$this->assertType(get_class($result), $cached->execute(), 'First execution not cached');
		$this->assertType('Database_Result_Array', $cached->execute(), 'Second execution cached');
	}
}
