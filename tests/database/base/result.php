<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Base_Result_Test extends PHPUnit_Framework_TestCase
{
	public function test_interfaces()
	{
		$class = new ReflectionClass('Database_Result');

		$this->assertTrue($class->implementsInterface('ArrayAccess'));
		$this->assertTrue($class->implementsInterface('Countable'));
		$this->assertTrue($class->implementsInterface('Iterator'));
		$this->assertTrue($class->implementsInterface('SeekableIterator'));
		$this->assertTrue($class->implementsInterface('Traversable'));
	}

	public function provider_count()
	{
		return array
		(
			array(0),
			array(1),
			array(2),
		);
	}

	/**
	 * @covers  Database_Result::__construct
	 * @covers  Database_Result::count
	 * @dataProvider    provider_count
	 *
	 * @param   integer $count
	 */
	public function test_count($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$this->assertSame($count, $result->count());
	}

	/**
	 * @covers  Database_Result::key
	 * @covers  Database_Result::next
	 * @dataProvider    provider_count
	 *
	 * @param   integer $count
	 */
	public function test_next($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		for ($i = 0; $i < $count; ++$i)
		{
			$this->assertSame($i, $result->key());
			$this->assertSame($result, $result->next());
		}

		$this->assertSame($count, $result->key());
	}

	public function provider_seek()
	{
		return array
		(
			array(1, 0),
			array(2, 0),
			array(2, 1),
			array(3, 0),
			array(3, 1),
			array(3, 2),
		);
	}

	/**
	 * @covers  Database_Result::key
	 * @covers  Database_Result::seek
	 * @dataProvider    provider_seek
	 *
	 * @param   integer $count
	 * @param   integer $position
	 */
	public function test_seek($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$this->assertSame($result, $result->seek($position));
		$this->assertSame($position, $result->key());
	}

	/**
	 * @covers  Database_Result::seek
	 *
	 * @dataProvider    provider_count
	 *
	 * @param   integer $count
	 */
	public function test_seek_error_low($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$this->setExpectedException('OutOfBoundsException');

		$result->seek(-1);
	}

	public function provider_seek_error_high()
	{
		return array
		(
			array(1, 1),
			array(1, 5),
			array(2, 2),
			array(2, 3),
			array(3, 3),
			array(3, 7),
		);
	}

	/**
	 * @covers  Database_Result::seek
	 *
	 * @dataProvider    provider_seek_error_high
	 *
	 * @param   integer $count
	 * @param   integer $position
	 */
	public function test_seek_error_high($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$this->setExpectedException('OutOfBoundsException');

		$result->seek($position);
	}

	/**
	 * @covers  Database_Result::key
	 * @covers  Database_Result::rewind
	 * @dataProvider    provider_seek
	 *
	 * @param   integer $count
	 * @param   integer $position
	 */
	public function test_rewind($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$result->seek($position);

		$this->assertSame($result, $result->rewind());
		$this->assertSame(0, $result->key());
	}

	/**
	 * @covers  Database_Result::key
	 * @covers  Database_Result::prev
	 * @dataProvider    provider_seek
	 *
	 * @param   integer         $count
	 * @param   integer         $position
	 */
	public function test_prev($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$result->seek($position);

		for ($i = $position; $i >= 0; --$i)
		{
			$this->assertSame($i, $result->key());
			$this->assertSame($result, $result->prev());
		}

		$this->assertSame(-1, $result->key());
	}

	public function provider_get()
	{
		return array
		(
			array(FALSE, array('value' => 50), 'value', 50),
			array(TRUE, (object) array('key' => 20), 'key', 20),
		);
	}

	/**
	 * @covers  Database_Result::get
	 * @dataProvider    provider_get
	 *
	 * @param   string|boolean  $as_object
	 * @param   array|object    $current    One row with a non-null value
	 * @param   string          $key        Index of the non-null value
	 * @param   mixed           $value      Non-null value
	 */
	public function test_get($as_object, $current, $key, $value)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array($as_object, 1)
		);

		$result->expects($this->exactly(4))
			->method('current')
			->will($this->returnValue($current));

		$this->assertSame($value, $result->get(), 'void');
		$this->assertSame($value, $result->get($key), 'value');
		$this->assertSame($value, $result->get($key, 'other'), 'default');
		$this->assertSame('other', $result->get($key.'non-existent', 'other'), 'non-existent');
	}

	public function provider_get_invalid()
	{
		return array
		(
			array(FALSE),
			array(TRUE),
		);
	}

	/**
	 * @covers  Database_Result::get
	 * @dataProvider    provider_get_invalid
	 */
	public function test_get_invalid($as_object)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array($as_object, 0)
		);

		$this->assertNull($result->get(), 'void');
		$this->assertNull($result->get('value'), 'value');
		$this->assertSame('other', $result->get('value', 'other'), 'default');
	}

	public function provider_get_null()
	{
		return array
		(
			array(FALSE, array('value' => NULL), 'value'),
			array(TRUE, (object) array('key' => NULL), 'key'),
		);
	}

	/**
	 * @covers  Database_Result::get
	 * @dataProvider    provider_get_null
	 *
	 * @param   string|boolean  $as_object
	 * @param   array|object    $current    One row with a NULL value
	 * @param   string          $key        Index of the NULL value
	 */
	public function test_get_null($as_object, $current, $key)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array($as_object, 1)
		);

		$result->expects($this->exactly(3))
			->method('current')
			->will($this->returnValue($current));

		$this->assertNull($result->get(), 'void');
		$this->assertNull($result->get($key), 'value');
		$this->assertSame('other', $result->get($key, 'other'), 'default');
	}

	/**
	 * @covers  Database_Result::offsetExists
	 * @dataProvider    provider_count
	 */
	public function test_offset_exists($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		for ($i = 0; $i < $count; ++$i)
		{
			$this->assertTrue($result->offsetExists($i));
		}

		$this->assertFalse($result->offsetExists(-1));
		$this->assertFalse($result->offsetExists($count));
	}

	/**
	 * @covers  Database_Result::offsetGet
	 */
	public function test_offset_get()
	{
		$value = array('key' => 'value');

		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, 1)
		);

		$result->expects($this->once())
			->method('current')
			->will($this->returnValue($value));

		$this->assertSame($value, $result->offsetGet(0));
	}

	/**
	 * @covers  Database_Result::offsetGet
	 *
	 * @dataProvider    provider_count
	 *
	 * @param   integer $count
	 */
	public function test_offset_get_error_low($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$this->assertNull($result->offsetGet(-1));
	}

	/**
	 * @covers  Database_Result::offsetGet
	 *
	 * @dataProvider    provider_seek_error_high
	 *
	 * @param   integer $count
	 * @param   integer $position
	 */
	public function test_offset_get_error_high($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$this->assertNull($result->offsetGet($position));
	}

	/**
	 * Always throws an exception.
	 *
	 * @covers  Database_Result::offsetSet
	 */
	public function test_offset_set()
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, 1)
		);

		$this->setExpectedException('Kohana_Exception');

		$result->offsetSet(0, TRUE);
	}

	/**
	 * Always throws an exception.
	 *
	 * @covers  Database_Result::offsetUnset
	 */
	public function test_offset_unset()
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, 1)
		);

		$this->setExpectedException('Kohana_Exception');

		$result->offsetUnset(0);
	}

	/**
	 * @covers  Database_Result::valid
	 */
	public function test_valid_empty()
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, 0)
		);

		$this->assertFalse($result->valid());
	}

	public function provider_count_not_empty()
	{
		return array
		(
			array(1),
			array(2),
			array(3),
		);
	}

	/**
	 * @covers  Database_Result::valid
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_initial($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		$this->assertTrue($result->valid());
	}

	/**
	 * @covers  Database_Result::valid
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_low($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		// Move pointer before the beginning
		$result->prev();

		$this->assertFalse($result->valid());
	}

	/**
	 * @covers  Database_Result::valid
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_high($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		// Move pointer past the end
		for ($i = 0; $i < $count; ++$i)
		{
			$result->next();
		}

		$this->assertFalse($result->valid());
	}

	/**
	 * @covers  Database_Result::valid
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_end($count)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array(FALSE, $count)
		);

		// Move pointer to the end
		for ($i = 0; $i < ($count - 1); ++$i)
		{
			$result->next();
		}

		$this->assertTrue($result->valid());
	}

	public function provider_as_array()
	{
		$result = array();

		$rows = array(
			array('id' => 5, 'value' => 50),
			array('id' => 6, 'value' => 60),
			array('id' => 7, 'value' => 70),
		);

		$result[] = array(FALSE, NULL, NULL, $rows, $rows);
		$result[] = array(FALSE, NULL, 'id', $rows, array(5, 6, 7));
		$result[] = array(FALSE, NULL, 'value', $rows, array(50, 60, 70));
		$result[] = array(FALSE, 'id', 'value', $rows, array(5 => 50, 6 => 60, 7 => 70));
		$result[] = array(FALSE, 'value', 'id', $rows, array(50 => 5, 60 => 6, 70 => 7));
		$result[] = array(FALSE, 'id', NULL, $rows, array(
			5 => array('id' => 5, 'value' => 50),
			6 => array('id' => 6, 'value' => 60),
			7 => array('id' => 7, 'value' => 70),
		));
		$result[] = array(FALSE, 'value', NULL, $rows, array(
			50 => array('id' => 5, 'value' => 50),
			60 => array('id' => 6, 'value' => 60),
			70 => array('id' => 7, 'value' => 70),
		));

		$rows = array(
			(object) array('a' => 'A', 'b' => 'B'),
			(object) array('a' => 'C', 'b' => 'D'),
			(object) array('a' => 3, 'b' => 100),
		);

		$result[] = array(TRUE, NULL, NULL, $rows, $rows);
		$result[] = array(TRUE, NULL, 'a', $rows, array('A', 'C', 3));
		$result[] = array(TRUE, NULL, 'b', $rows, array('B', 'D', 100));
		$result[] = array(TRUE, 'a', 'b', $rows, array('A' => 'B', 'C' => 'D', 3 => 100));
		$result[] = array(TRUE, 'b', 'a', $rows, array('B' => 'A', 'D' => 'C', 100 => 3));
		$result[] = array(TRUE, 'a', NULL, $rows, array(
			'A' => (object) array('a' => 'A', 'b' => 'B'),
			'C' => (object) array('a' => 'C', 'b' => 'D'),
			3 => (object) array('a' => 3, 'b' => 100),
		));
		$result[] = array(TRUE, 'b', NULL, $rows, array(
			'B' => (object) array('a' => 'A', 'b' => 'B'),
			'D' => (object) array('a' => 'C', 'b' => 'D'),
			100 => (object) array('a' => 3, 'b' => 100),
		));

		return $result;
	}

	/**
	 * @covers  Database_Result::as_array
	 * @dataProvider    provider_as_array
	 *
	 * @param   string|boolean  $as_object
	 * @param   string          $key        First argument to method
	 * @param   string          $value      Second argument to method
	 * @param   array           $rows       Data set
	 * @param   array           $expected
	 */
	public function test_as_array($as_object, $key, $value, $rows, $expected)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array($as_object, count($rows))
		);

		foreach ($rows as $i => $row)
		{
			$result->expects($this->at($i))
				->method('current')
				->will($this->returnValue($row));
		}

		$this->assertEquals($expected, $result->as_array($key, $value));
	}

	public function provider_as_array_after_seek()
	{
		$result = array();

		$rows = array(
			array('id' => 5, 'value' => 50),
			array('id' => 6, 'value' => 60),
			array('id' => 7, 'value' => 70),
		);

		// data set #0
		$result[] = array(FALSE, 0, NULL, NULL, $rows, $rows);
		$result[] = array(FALSE, 1, NULL, NULL, $rows, $rows);
		$result[] = array(FALSE, 2, NULL, NULL, $rows, $rows);

		// data set #3
		$result[] = array(FALSE, 0, NULL, 'id', $rows, array(5, 6, 7));
		$result[] = array(FALSE, 1, NULL, 'id', $rows, array(5, 6, 7));
		$result[] = array(FALSE, 2, NULL, 'id', $rows, array(5, 6, 7));

		// data set #6
		$result[] = array(FALSE, 0, NULL, 'value', $rows, array(50, 60, 70));
		$result[] = array(FALSE, 1, NULL, 'value', $rows, array(50, 60, 70));
		$result[] = array(FALSE, 2, NULL, 'value', $rows, array(50, 60, 70));

		// data set #9
		$result[] = array(FALSE, 0, 'id', 'value', $rows, array(5 => 50, 6 => 60, 7 => 70));
		$result[] = array(FALSE, 1, 'id', 'value', $rows, array(5 => 50, 6 => 60, 7 => 70));
		$result[] = array(FALSE, 2, 'id', 'value', $rows, array(5 => 50, 6 => 60, 7 => 70));

		// data set #12
		$result[] = array(FALSE, 0, 'value', 'id', $rows, array(50 => 5, 60 => 6, 70 => 7));
		$result[] = array(FALSE, 1, 'value', 'id', $rows, array(50 => 5, 60 => 6, 70 => 7));
		$result[] = array(FALSE, 2, 'value', 'id', $rows, array(50 => 5, 60 => 6, 70 => 7));

		// data set #15
		$result[] = array(FALSE, 0, 'id', NULL, $rows, array(
			5 => array('id' => 5, 'value' => 50),
			6 => array('id' => 6, 'value' => 60),
			7 => array('id' => 7, 'value' => 70),
		));
		$result[] = array(FALSE, 1, 'id', NULL, $rows, array(
			5 => array('id' => 5, 'value' => 50),
			6 => array('id' => 6, 'value' => 60),
			7 => array('id' => 7, 'value' => 70),
		));
		$result[] = array(FALSE, 2, 'id', NULL, $rows, array(
			5 => array('id' => 5, 'value' => 50),
			6 => array('id' => 6, 'value' => 60),
			7 => array('id' => 7, 'value' => 70),
		));

		// data set #18
		$result[] = array(FALSE, 0, 'value', NULL, $rows, array(
			50 => array('id' => 5, 'value' => 50),
			60 => array('id' => 6, 'value' => 60),
			70 => array('id' => 7, 'value' => 70),
		));
		$result[] = array(FALSE, 1, 'value', NULL, $rows, array(
			50 => array('id' => 5, 'value' => 50),
			60 => array('id' => 6, 'value' => 60),
			70 => array('id' => 7, 'value' => 70),
		));
		$result[] = array(FALSE, 2, 'value', NULL, $rows, array(
			50 => array('id' => 5, 'value' => 50),
			60 => array('id' => 6, 'value' => 60),
			70 => array('id' => 7, 'value' => 70),
		));

		$rows = array(
			(object) array('a' => 'A', 'b' => 'B'),
			(object) array('a' => 'C', 'b' => 'D'),
			(object) array('a' => 3, 'b' => 100),
		);

		// data set #21
		$result[] = array(TRUE, 0, NULL, NULL, $rows, $rows);
		$result[] = array(TRUE, 1, NULL, NULL, $rows, $rows);
		$result[] = array(TRUE, 2, NULL, NULL, $rows, $rows);

		// data set #24
		$result[] = array(TRUE, 0, NULL, 'a', $rows, array('A', 'C', 3));
		$result[] = array(TRUE, 1, NULL, 'a', $rows, array('A', 'C', 3));
		$result[] = array(TRUE, 2, NULL, 'a', $rows, array('A', 'C', 3));

		// data set #27
		$result[] = array(TRUE, 0, NULL, 'b', $rows, array('B', 'D', 100));
		$result[] = array(TRUE, 1, NULL, 'b', $rows, array('B', 'D', 100));
		$result[] = array(TRUE, 2, NULL, 'b', $rows, array('B', 'D', 100));

		// data set #30
		$result[] = array(TRUE, 0, 'a', 'b', $rows, array('A' => 'B', 'C' => 'D', 3 => 100));
		$result[] = array(TRUE, 1, 'a', 'b', $rows, array('A' => 'B', 'C' => 'D', 3 => 100));
		$result[] = array(TRUE, 2, 'a', 'b', $rows, array('A' => 'B', 'C' => 'D', 3 => 100));

		// data set #33
		$result[] = array(TRUE, 0, 'b', 'a', $rows, array('B' => 'A', 'D' => 'C', 100 => 3));
		$result[] = array(TRUE, 1, 'b', 'a', $rows, array('B' => 'A', 'D' => 'C', 100 => 3));
		$result[] = array(TRUE, 2, 'b', 'a', $rows, array('B' => 'A', 'D' => 'C', 100 => 3));

		// data set #36
		$result[] = array(TRUE, 0, 'a', NULL, $rows, array(
			'A' => (object) array('a' => 'A', 'b' => 'B'),
			'C' => (object) array('a' => 'C', 'b' => 'D'),
			3 => (object) array('a' => 3, 'b' => 100),
		));
		$result[] = array(TRUE, 1, 'a', NULL, $rows, array(
			'A' => (object) array('a' => 'A', 'b' => 'B'),
			'C' => (object) array('a' => 'C', 'b' => 'D'),
			3 => (object) array('a' => 3, 'b' => 100),
		));
		$result[] = array(TRUE, 2, 'a', NULL, $rows, array(
			'A' => (object) array('a' => 'A', 'b' => 'B'),
			'C' => (object) array('a' => 'C', 'b' => 'D'),
			3 => (object) array('a' => 3, 'b' => 100),
		));

		// data set #39
		$result[] = array(TRUE, 0, 'b', NULL, $rows, array(
			'B' => (object) array('a' => 'A', 'b' => 'B'),
			'D' => (object) array('a' => 'C', 'b' => 'D'),
			100 => (object) array('a' => 3, 'b' => 100),
		));
		$result[] = array(TRUE, 1, 'b', NULL, $rows, array(
			'B' => (object) array('a' => 'A', 'b' => 'B'),
			'D' => (object) array('a' => 'C', 'b' => 'D'),
			100 => (object) array('a' => 3, 'b' => 100),
		));
		$result[] = array(TRUE, 2, 'b', NULL, $rows, array(
			'B' => (object) array('a' => 'A', 'b' => 'B'),
			'D' => (object) array('a' => 'C', 'b' => 'D'),
			100 => (object) array('a' => 3, 'b' => 100),
		));

		return $result;
	}

	/**
	 * @covers  Database_Result::as_array
	 * @dataProvider    provider_as_array_after_seek
	 *
	 * @param   string|boolean  $as_object
	 * @param   integer         $position
	 * @param   string          $key        First argument to method
	 * @param   string          $value      Second argument to method
	 * @param   array           $rows       Data set
	 * @param   array           $expected
	 */
	public function test_as_array_after_seek($as_object, $position, $key, $value, $rows, $expected)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array($as_object, count($rows))
		);

		foreach ($rows as $i => $row)
		{
			$result->expects($this->at($i))
				->method('current')
				->will($this->returnValue($row));
		}

		$result->seek($position);

		$this->assertEquals($expected, $result->as_array($key, $value));
		$this->assertSame($position, $result->key(), 'Position unchanged');
	}

	public function provider_serializable()
	{
		$result = array();

		$rows = array(
			array('id' => 5, 'value' => 50),
			array('id' => 6, 'value' => 60),
			array('id' => 7, 'value' => 70),
		);

		$result[] = array(FALSE, $rows, new Database_Result_Array($rows, FALSE));

		$rows = array(
			(object) array('a' => 'A', 'b' => 'B'),
			(object) array('a' => 'C', 'b' => 'D'),
			(object) array('a' => 3, 'b' => 100),
		);

		$result[] = array(TRUE, $rows, new Database_Result_Array($rows, TRUE));

		return $result;
	}

	/**
	 * @covers  Database_Result::serializable
	 *
	 * @dataProvider    provider_serializable
	 *
	 * @param   string|boolean          $as_object
	 * @param   array                   $rows       Data set
	 * @param   Database_Result_Array   $expected
	 */
	public function test_serializable($as_object, $rows, $expected)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array($as_object, count($rows))
		);

		foreach ($rows as $i => $row)
		{
			$result->expects($this->at($i))
				->method('current')
				->will($this->returnValue($row));
		}

		$this->assertEquals($expected, $result->serializable());
	}

	/**
	 * @covers  Database_Result::serializable
	 *
	 * @dataProvider    provider_serializable
	 *
	 * @param   string|boolean          $as_object
	 * @param   array                   $rows       Data set
	 * @param   Database_Result_Array   $expected
	 */
	public function test_serializable_can_serialize($as_object, $rows, $expected)
	{
		$result = $this->getMockForAbstractClass(
			'Database_Result',
			array($as_object, count($rows))
		);

		foreach ($rows as $i => $row)
		{
			$result->expects($this->at($i))
				->method('current')
				->will($this->returnValue($row));
		}

		$string = serialize($result->serializable());

		$this->assertEquals($expected, unserialize($string));
	}
}
