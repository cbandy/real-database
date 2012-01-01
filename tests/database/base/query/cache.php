<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Cache_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! class_exists('Cache'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Cache module not installed'
			);
	}

	public function provider_key()
	{
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$result = array();

		$result[] = array(
			$db,
			new Database_Query('sql'),
			'Database_Query_Cache(db,sql,a:0:{},,N;)',
		);
		$result[] = array(
			$db,
			new Database_Query('params', array('x')),
			'Database_Query_Cache(db,params,a:1:{i:0;s:1:"x";},,N;)',
		);

		$query = new Database_Query('sql');
		$query->as_object();

		$result[] = array(
			$db,
			$query,
			'Database_Query_Cache(db,sql,a:0:{},1,a:0:{})',
		);

		$query = new Database_Query('sql');
		$query->as_object('class');

		$result[] = array(
			$db,
			$query,
			'Database_Query_Cache(db,sql,a:0:{},class,a:0:{})',
		);

		$query = new Database_Query('sql');
		$query->as_object('class', array('y'));

		$result[] = array(
			$db,
			$query,
			'Database_Query_Cache(db,sql,a:0:{},class,a:1:{i:0;s:1:"y";})',
		);

		return $result;
	}

	/**
	 * @covers  Database_Query_Cache::key
	 *
	 * @dataProvider    provider_key
	 *
	 * @param   Database        $db
	 * @param   Database_iQuery $query      Argument to the method
	 * @param   string          $expected
	 */
	public function test_key($db, $query, $expected)
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertSame($expected, $query_cache->key($query));
	}

	/**
	 * @covers  Database_Query_Cache::delete
	 */
	public function test_delete()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$query = new Database_Query('test_delete');

		$cache->expects($this->once())
			->method('delete')
			->with('Database_Query_Cache(db,test_delete,a:0:{},,N;)');

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertNull($query_cache->delete($query));
	}

	/**
	 * @covers  Database_Query_Cache::get
	 */
	public function test_get()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;
		$query = new Database_Query('test_get');

		$cache->expects($this->once())
			->method('get')
			->with('Database_Query_Cache(db,test_get,a:0:{},,N;)')
			->will($this->returnValue($expected));

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertSame($expected, $query_cache->get($query));
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
	 * @covers  Database_Query_Cache::execute
	 *
	 * @dataProvider    provider_execute_cache_hit
	 *
	 * @param   integer $lifetime   Second argument to the method
	 */
	public function test_execute_cache_hit($lifetime)
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;
		$query = new Database_Query('test_execute_cache_hit');

		// Cache hit
		$cache->expects($this->once())
			->method('get')
			->with('Database_Query_Cache(db,test_execute_cache_hit,a:0:{},,N;)')
			->will($this->returnValue($expected));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertSame($expected, $query_cache->execute($query, $lifetime));
	}

	public function provider_execute_cache_miss()
	{
		return array(
			array(NULL),
			array(3),
		);
	}

	/**
	 * @covers  Database_Query_Cache::execute
	 *
	 * @dataProvider    provider_execute_cache_miss
	 *
	 * @param   integer $lifetime   Second argument to the method
	 */
	public function test_execute_cache_miss($lifetime)
	{
		$array = array(array('kohana'));
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;
		$key = 'Database_Query_Cache(db,test_execute_cache_miss,a:0:{},,N;)';
		$query = new Database_Query('test_execute_cache_miss');

		/**
		 * Use getMock() rather than getMockForAbstractClass() to mock/stub the
		 * concrete method, serializable().
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/49 Fixed in PHPUnit 3.6.0
		 */
		$result = $this->getMock(
			'Database_Result',
			array('current', 'offsetGet', 'serializable'),
			array(NULL, 0)
		);

		// Cache miss
		$cache->expects($this->once())
			->method('get')
			->with($key)
			->will($this->returnValue(NULL));

		// Sanitized result saved to cache
		$cache->expects($this->once())
			->method('set')
			->with($key, $expected, $lifetime);

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		// Result sanitized
		$result->expects($this->once())
			->method('serializable')
			->will($this->returnValue($expected));

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertSame($result, $query_cache->execute($query, $lifetime));
	}

	/**
	 * @covers  Database_Query_Cache::execute
	 */
	public function test_execute_cache_miss_negative_lifetime()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;
		$key = 'Database_Query_Cache(db,test_execute_cache_miss_negative_lifetime,a:0:{},,N;)';
		$query = new Database_Query('test_execute_cache_miss_negative_lifetime');

		// Cache miss
		$cache->expects($this->once())
			->method('get')
			->with($key)
			->will($this->returnValue(NULL));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($expected));

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertSame($expected, $query_cache->execute($query, -3));
	}

	/**
	 * Statements that return NULL from Database::execute_query() should never
	 * be cached.
	 *
	 * @covers  Database_Query_Cache::execute
	 */
	public function test_execute_command()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$query = new Database_Query('test_execute_command');

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue(NULL));

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertNull($query_cache->execute($query, 3));
	}

	public function provider_set()
	{
		return array(
			array(NULL),
			array(3),
		);
	}

	/**
	 * @covers  Database_Query_Cache::_execute_set
	 * @covers  Database_Query_Cache::set
	 *
	 * @dataProvider    provider_set
	 *
	 * @param   integer $lifetime   Second argument to the method
	 */
	public function test_set($lifetime)
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;
		$key = 'Database_Query_Cache(db,test_set,a:0:{},,N;)';
		$query = new Database_Query('test_set');

		/**
		 * Use getMock() rather than getMockForAbstractClass() to mock/stub the
		 * concrete method, serializable().
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/49 Fixed in PHPUnit 3.6.0
		 */
		$result = $this->getMock(
			'Database_Result',
			array('current', 'offsetGet', 'serializable'),
			array(NULL, 0)
		);

		// Sanitized result saved to cache
		$cache->expects($this->once())
			->method('set')
			->with($key, $expected, $lifetime);

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		// Result sanitized
		$result->expects($this->once())
			->method('serializable')
			->will($this->returnValue($expected));

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertSame($result, $query_cache->set($query, $lifetime));
	}

	/**
	 * Statements that return NULL from Database::execute_query() should never
	 * be cached.
	 *
	 * @covers  Database_Query_Cache::_execute_set
	 */
	public function test_set_command()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$query = new Database_Query('test_set_command');

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue(NULL));

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertNull($query_cache->set($query, 3));
	}

	/**
	 * @covers  Database_Query_Cache::_execute_set
	 */
	public function test_set_negative_lifetime()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$expected = new stdClass;
		$query = new Database_Query('test_set_negative_lifetime');

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		// Query executed
		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($expected));

		$query_cache = new Database_Query_Cache($cache, $db);

		$this->assertSame($expected, $query_cache->set($query, -3));
	}
}
