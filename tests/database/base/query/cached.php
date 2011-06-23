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
	public static function setUpBeforeClass()
	{
		if ( ! class_exists('Cache'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Cache module not installed'
			);
	}

	/**
	 * @covers  Database_Query_Cached::__construct
	 * @covers  Database_Query_Cached::key
	 */
	public function test_constructor()
	{
		$cache = Cache::instance();
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('query')
		);

		$this->assertSame(
			'Database_Query_Cached(db,query,a:0:{},,N;)', $cached->key()
		);
	}

	/**
	 * @covers  Database_Query_Cached::delete
	 */
	public function test_delete()
	{
		$array = array(array('kohana'));
		$cache = Cache::instance();

		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		/**
		 * Use getMock() rather than getMockForAbstractClass() to mock/stub the
		 * concrete method, as_array().
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/49
		 */
		$result = $this->getMock(
			'Database_Result', array('as_array', 'current'), array(FALSE, 1)
		);

		$db->expects($this->exactly(2))
			->method('execute_query')
			->will($this->returnValue($result));

		$result->expects($this->exactly(2))
			->method('as_array')
			->will($this->returnValue($array));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('query')
		);

		// Cache the result
		$cached->execute(3);

		// Clear the cache
		$cached->delete();
		$this->assertType(get_class($result), $cached->execute(3), 'Not cached');
	}

	/**
	 * @covers  Database_Query_Cached::execute
	 */
	public function test_execute()
	{
		$array = array(array('kohana'));
		$cache = Cache::instance();

		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		/**
		 * Use getMock() rather than getMockForAbstractClass() to mock/stub the
		 * concrete method, as_array().
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/49
		 */
		$result = $this->getMock(
			'Database_Result', array('as_array', 'current'), array(FALSE, 1)
		);

		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		$result->expects($this->once())
			->method('as_array')
			->will($this->returnValue($array));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('query')
		);

		// Clear the cache
		$cached->delete();

		$this->assertType(get_class($result), $cached->execute(3), 'First execution not cached');
		$this->assertType('Database_Result_Array', $cached->execute(3), 'Second execution cached');
	}
}
