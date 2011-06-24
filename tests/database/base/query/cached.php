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
	 * Build a Database_Result mock that returns rows from as_array().
	 *
	 * @param   PHPUnit_Framework_MockObject_Matcher_Invocation $expects
	 * @param   array                                           $rows
	 * @return  Database_Result
	 */
	protected function _get_mock_result_as_array($expects, $rows)
	{
		/**
		 * Use getMock() rather than getMockForAbstractClass() to mock/stub the
		 * concrete method, as_array().
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/49
		 */
		$result = $this->getMock(
			'Database_Result',
			array('as_array', 'current'),
			array(FALSE, count($rows))
		);

		$result->expects($expects)
			->method('as_array')
			->will($this->returnValue($rows));

		return $result;
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
			->with(
				$this->identicalTo('Database_Query_Cached(db,test_delete,a:0:{},,N;)')
			);

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_delete')
		);

		$this->assertNull($cached->delete());
	}

	public function provider_get()
	{
		return array(
			array(NULL, NULL),
			array(
				array(array('kohana')),
				new Database_Result_Array(array(array('kohana')), FALSE),
			),
		);
	}

	/**
	 * @covers  Database_Query_Cached::_get
	 * @covers  Database_Query_Cached::get
	 *
	 * @dataProvider    provider_get
	 *
	 * @param   array                   $data       Data in the cache
	 * @param   Database_Result_Array   $expected
	 */
	public function test_get($data, $expected)
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		$cache->expects($this->once())
			->method('get')
			->with(
				$this->identicalTo('Database_Query_Cached(db,test_get,a:0:{},,N;)')
			)
			->will($this->returnValue($data));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_get')
		);

		$this->assertEquals($expected, $cached->get());
	}

	public function provider_execute_cache_hit()
	{
		return array(
			array(
				NULL, array(array('a')),
				new Database_Result_Array(array(array('a')), FALSE),
			),
			array(
				3, array(array('b')),
				new Database_Result_Array(array(array('b')), FALSE),
			),
			array(
				-3, array(array('c')),
				new Database_Result_Array(array(array('c')), FALSE),
			),
		);
	}

	/**
	 * @covers  Database_Query_Cached::execute
	 *
	 * @dataProvider    provider_execute_cache_hit
	 *
	 * @param   integer                 $lifetime   Argument to the method
	 * @param   array                   $data       Data in the cache
	 * @param   Database_Result_Array   $expected
	 */
	public function test_execute_cache_hit($lifetime, $data, $expected)
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		// Cache hit
		$cache->expects($this->once())
			->method('get')
			->with(
				$this->identicalTo('Database_Query_Cached(db,test_execute_cache_hit,a:0:{},,N;)')
			)
			->will($this->returnValue($data));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_execute_cache_hit')
		);

		$this->assertEquals($expected, $cached->execute($lifetime));
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
		$result = $this->_get_mock_result_as_array($this->once(), $array);

		// Cache miss
		$cache->expects($this->once())
			->method('get')
			->with(
				$this->identicalTo('Database_Query_Cached(db,test_execute_cache_miss,a:0:{},,N;)')
			)
			->will($this->returnValue(NULL));

		// Data array saved to cache
		$cache->expects($this->once())
			->method('set')
			->with(
				$this->identicalTo('Database_Query_Cached(db,test_execute_cache_miss,a:0:{},,N;)'),
				$this->identicalTo($array),
				$this->identicalTo($lifetime)
			);

		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

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
		$result = $this->getMockForAbstractClass(
			'Database_Result', array(), '', FALSE
		);

		// Cache miss
		$cache->expects($this->once())
			->method('get')
			->with(
				$this->identicalTo('Database_Query_Cached(db,test_execute_cache_miss_negative_lifetime,a:0:{},,N;)')
			)
			->will($this->returnValue(NULL));

		// Nothing saved to cache
		$cache->expects($this->never())
			->method('set');

		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_execute_cache_miss_negative_lifetime')
		);

		$this->assertSame($result, $cached->execute(-3));
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

		$cache->expects($this->never())
			->method('set');

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
	 * @covers  Database_Query_Cached::_set
	 * @covers  Database_Query_Cached::set
	 *
	 * @dataProvider    provider_set
	 *
	 * @param   integer $lifetime   Argument to the method
	 */
	public function test_set($lifetime)
	{
		$array = array(array('kohana'));
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$result = $this->_get_mock_result_as_array($this->once(), $array);

		$cache->expects($this->once())
			->method('set')
			->with(
				$this->identicalTo('Database_Query_Cached(db,test_set,a:0:{},,N;)'),
				$this->identicalTo($array),
				$this->identicalTo($lifetime)
			);

		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_set')
		);

		$this->assertSame($result, $cached->set($lifetime));
	}

	/**
	 * Statements that return NULL from Database::execute_query() should never
	 * be cached.
	 *
	 * @covers  Database_Query_Cached::_set
	 */
	public function test_set_command()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));

		$cache->expects($this->never())
			->method('set');

		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue(NULL));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_set_command')
		);

		$this->assertNull($cached->set(3));
	}

	/**
	 * @covers  Database_Query_Cached::_set
	 */
	public function test_set_negative_lifetime()
	{
		$cache = $this->getMockForAbstractClass('Cache', array(), '', FALSE);
		$db = $this->getMockForAbstractClass('Database', array('db', array()));
		$result = $this->getMockForAbstractClass(
			'Database_Result', array(), '', FALSE
		);

		$cache->expects($this->never())
			->method('set');

		$db->expects($this->once())
			->method('execute_query')
			->will($this->returnValue($result));

		$cached = new Database_Query_Cached(
			$cache, $db, new Database_Query('test_set_negative_lifetime')
		);

		$this->assertSame($result, $cached->set(-3));
	}
}
