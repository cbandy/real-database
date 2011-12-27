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
	 * @covers  Database_Query_Cached::delete
	 */
	public function test_delete()
	{
		$cache = $this->getMock(
			'Database_Query_Cache',
			array('delete'),
			array(NULL, NULL)
		);
		$query = new Database_Query('test_delete');

		$cache->expects($this->once())
			->method('delete')
			->with($query);

		$cached = new Database_Query_Cached($cache, $query);

		$this->assertNull($cached->delete());
	}

	public function provider_execute()
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
	 * @dataProvider    provider_execute
	 *
	 * @param   integer $lifetime   Argument to the method
	 */
	public function test_execute($lifetime)
	{
		$cache = $this->getMock(
			'Database_Query_Cache',
			array('execute'),
			array(NULL, NULL)
		);
		$expected = new stdClass;
		$query = new Database_Query('test_execute');

		$cache->expects($this->once())
			->method('execute')
			->with($query, $lifetime)
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached($cache, $query);

		$this->assertSame($expected, $cached->execute($lifetime));
	}

	/**
	 * @covers  Database_Query_Cached::get
	 */
	public function test_get()
	{
		$cache = $this->getMock(
			'Database_Query_Cache',
			array('get'),
			array(NULL, NULL)
		);
		$expected = new stdClass;
		$query = new Database_Query('test_get');

		$cache->expects($this->once())
			->method('get')
			->with($query)
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached($cache, $query);

		$this->assertSame($expected, $cached->get());
	}

	/**
	 * @covers  Database_Query_Cached::key
	 */
	public function test_key()
	{
		$cache = $this->getMock(
			'Database_Query_Cache',
			array('key'),
			array(NULL, NULL)
		);
		$expected = new stdClass;
		$query = new Database_Query('test_key');

		$cache->expects($this->once())
			->method('key')
			->with($query)
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached($cache, $query);

		$this->assertSame($expected, $cached->key());
	}

	public function provider_set()
	{
		return array(
			array(NULL),
			array(3),
			array(-3),
		);
	}

	/**
	 * @covers  Database_Query_Cached::set
	 *
	 * @dataProvider    provider_set
	 *
	 * @param   integer $lifetime   Argument to the method
	 */
	public function test_set($lifetime)
	{
		$cache = $this->getMock(
			'Database_Query_Cache',
			array('set'),
			array(NULL, NULL)
		);
		$expected = new stdClass;
		$query = new Database_Query('test_set');

		$cache->expects($this->once())
			->method('set')
			->with($query, $lifetime)
			->will($this->returnValue($expected));

		$cached = new Database_Query_Cached($cache, $query);

		$this->assertSame($expected, $cached->set($lifetime));
	}
}
