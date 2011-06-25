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
	 * @covers  Database_Query_Cached
	 * @covers  Database_Query_Cached::__construct
	 * @covers  Database_Query_Cached::key
	 */
	public function test_constructor()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
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
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		$cache->expects($this->once())
			->method('delete')
			->with('Database_Query_Cached(db,test_delete,a:0:{},,N;)');

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_delete')
		);

		$this->assertNull($cached->delete());
	}

	/**
	 * @covers  Database_Query_Cached::get
	 */
	public function test_get()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;

		$cache->expects($this->once())
			->method('get')
			->with('Database_Query_Cached(db,test_get,a:0:{},,N;)')
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_get')
		);

		$this->assertSame($expected, $cached->get());
	}

	public function provider_execute_cache_hit()
	{
		return array(
			array(NULL),
			array(3),
			array(-3),
		);
	}

	/**
	 * @covers  Database_Query_Cached::execute
	 *
	 * @dataProvider    provider_execute_cache_hit
	 *
	 * @param   integer $lifetime   Argument to the method
	 */
	public function test_execute_cache_hit($lifetime)
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;

		// Cache hit
		$cache->expects($this->once())
			->method('get')
			->with(
				'Database_Query_Cached(db,test_execute_cache_hit,a:0:{},,N;)'
			)
			->will($this->returnValue($expected));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_execute_cache_hit')
		);

		$this->assertSame($expected, $cached->execute($lifetime));
	}

	public function provider_execute_cache_miss()
	{
		return array(
			array(NULL),
			array(3),
		);
	}

	/**
	 * @covers  Database_Query_Cached::execute
	 *
	 * @dataProvider    provider_execute_cache_miss
	 *
	 * @param   integer $lifetime   Argument to the method
	 */
	public function test_execute_cache_miss($lifetime)
	{
		$array = array(array('kohana'));
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;

		/**
		 * Use getMock() rather than getMockForAbstractClass() to mock/stub the
		 * concrete method, serializable().
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/49
		 */
		$result = $this->getMock(
			'Database_Result',
			array('current', 'serializable'),
			array(NULL, 0)
		);

		// Cache miss
		$cache->expects($this->once())
			->method('get')
			->with(
				'Database_Query_Cached(db,test_execute_cache_miss,a:0:{},,N;)'
			)
			->will($this->returnValue(NULL));

		// Sanitized result saved to cache
		$cache->expects($this->once())
			->method('set')
			->with(
				'Database_Query_Cached(db,test_execute_cache_miss,a:0:{},,N;)',
				$expected,
				$lifetime
			);

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		// Result sanitized
		$result->expects($this->once())
			->method('serializable')
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_execute_cache_miss')
		);

		$this->assertSame($result, $cached->execute($lifetime));
	}

	/**
	 * @covers  Database_Query_Cached::execute
	 */
	public function test_execute_cache_miss_negative_lifetime()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;

		// Cache miss
		$cache->expects($this->once())
			->method('get')
			->with(
				'Database_Query_Cached(db,test_execute_cache_miss_negative_lifetime,a:0:{},,N;)'
			)
			->will($this->returnValue(NULL));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_execute_cache_miss_negative_lifetime')
		);

		$this->assertSame($expected, $cached->execute(-3));
	}

	/**
	 * Statements that return NULL from Database::execute_query() should never
	 * be cached.
	 *
	 * @covers  Database_Query_Cached::execute
	 */
	public function test_execute_command()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue(NULL));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_execute_command')
		);

		$this->assertNull($cached->execute(3));
	}

	public function provider_set()
	{
		return array(
			array(NULL),
			array(3),
		);
	}

	/**
	 * @covers  Database_Query_Cached::_execute_set
	 * @covers  Database_Query_Cached::set
	 *
	 * @dataProvider    provider_set
	 *
	 * @param   integer $lifetime   Argument to the method
	 */
	public function test_set($lifetime)
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;

		/**
		 * Use getMock() rather than getMockForAbstractClass() to mock/stub the
		 * concrete method, serializable().
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/49
		 */
		$result = $this->getMock(
			'Database_Result',
			array('current', 'serializable'),
			array(NULL, 0)
		);

		// Sanitized result saved to cache
		$cache->expects($this->once())
			->method('set')
			->with(
				'Database_Query_Cached(db,test_set,a:0:{},,N;)',
				$expected,
				$lifetime
			);

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		// Result sanitized
		$result->expects($this->once())
			->method('serializable')
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_set')
		);

		$this->assertSame($result, $cached->set($lifetime));
	}

	/**
	 * Statements that return NULL from Database::execute_query() should never
	 * be cached.
	 *
	 * @covers  Database_Query_Cached::_execute_set
	 */
	public function test_set_command()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue(NULL));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_set_command')
		);

		$this->assertNull($cached->set(3));
	}

	/**
	 * @covers  Database_Query_Cached::_execute_set
	 */
	public function test_set_negative_lifetime()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_set_negative_lifetime')
		);

		$this->assertSame($expected, $cached->set(-3));
	}
}
