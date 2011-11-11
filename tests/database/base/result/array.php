<?php

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.result
 */
class Database_Base_Result_Array_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array
		(
			array(array(), FALSE),
			array(array(), TRUE),

			array(array(
				array('a' => 'A'),
				array('b' => 'B'),
			), FALSE),

			array(array(
				(object) array('a' => 'A'),
				(object) array('b' => 'B'),
			), TRUE),
		);
	}

	/**
	 * @covers  Database_Result_Array::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array           $rows
	 * @param   string|boolean  $as_object
	 */
	public function test_constructor($rows, $as_object)
	{
		$result = new Database_Result_Array($rows, $as_object);

		$this->assertSame(count($rows), $result->count());
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
	 * @covers  Database_Result_Array::as_array
	 *
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
		$result = new Database_Result_Array($rows, $as_object);

		$this->assertEquals($expected, $result->as_array($key, $value));
	}

	public function provider_current()
	{
		return array(
			array(
				FALSE,
				array(
					array('a' => 'A'),
					array('b' => 'B'),
				),
				array('a' => 'A'),
			),

			array(
				TRUE,
				array(
					(object) array('a' => 'A'),
					(object) array('b' => 'B'),
				),
				(object) array('a' => 'A'),
			),
		);
	}

	/**
	 * @covers  Database_Result_Array::current
	 *
	 * @dataProvider    provider_current
	 *
	 * @param   string|boolean  $as_object
	 * @param   array           $rows       Data set
	 * @param   array           $expected
	 */
	public function test_current($as_object, $rows, $expected)
	{
		$result = new Database_Result_Array($rows, $as_object);

		$this->assertEquals($expected, $result->current());
		$this->assertEquals($expected, $result->current(), 'Do not move pointer');
	}

	public function provider_current_after_seek()
	{
		$result = array();

		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		$result[] = array(FALSE, 0, $rows, array('a' => 'A'));
		$result[] = array(FALSE, 1, $rows, array('b' => 'B'));
		$result[] = array(FALSE, 2, $rows, array('c' => 'C'));

		$rows = array(
			(object) array('a' => 'A'),
			(object) array('b' => 'B'),
			(object) array('c' => 'C'),
		);

		$result[] = array(TRUE, 0, $rows, (object) array('a' => 'A'));
		$result[] = array(TRUE, 1, $rows, (object) array('b' => 'B'));
		$result[] = array(TRUE, 2, $rows, (object) array('c' => 'C'));

		return $result;
	}

	/**
	 * @covers  Database_Result_Array::current
	 *
	 * @dataProvider    provider_current_after_seek
	 *
	 * @param   string|boolean  $as_object
	 * @param   integer         $position
	 * @param   array           $rows       Data set
	 * @param   array           $expected
	 */
	public function test_current_after_seek($as_object, $position, $rows, $expected)
	{
		$result = new Database_Result_Array($rows, $as_object);

		$result->seek($position);

		$this->assertEquals($expected, $result->current());
		$this->assertEquals($expected, $result->current(), 'Do not move pointer');
	}

	public function provider_offset_get()
	{
		$result = array();

		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		$result[] = array($rows, FALSE, 0, array('a' => 'A'));
		$result[] = array($rows, FALSE, 1, array('b' => 'B'));
		$result[] = array($rows, FALSE, 2, array('c' => 'C'));

		$rows = array(
			(object) array('a' => 'A'),
			(object) array('b' => 'B'),
			(object) array('c' => 'C'),
		);

		$result[] = array($rows, TRUE, 0, (object) array('a' => 'A'));
		$result[] = array($rows, TRUE, 1, (object) array('b' => 'B'));
		$result[] = array($rows, TRUE, 2, (object) array('c' => 'C'));

		return $result;
	}

	/**
	 * @covers  Database_Result_Array::offsetGet
	 *
	 * @dataProvider    provider_offset_get
	 *
	 * @param   array           $rows       Data set
	 * @param   string|boolean  $as_object
	 * @param   integer         $offset
	 * @param   array           $expected
	 */
	public function test_offset_get($rows, $as_object, $offset, $expected)
	{
		$result = new Database_Result_Array($rows, $as_object);

		$this->assertEquals($expected, $result->offsetGet($offset));
	}

	public function provider_offset_get_after_seek()
	{
		$result = array();

		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		// data set #0
		$result[] = array($rows, FALSE, 0, 0, array('a' => 'A'));
		$result[] = array($rows, FALSE, 1, 0, array('a' => 'A'));
		$result[] = array($rows, FALSE, 2, 0, array('a' => 'A'));

		// data set #3
		$result[] = array($rows, FALSE, 0, 1, array('b' => 'B'));
		$result[] = array($rows, FALSE, 1, 1, array('b' => 'B'));
		$result[] = array($rows, FALSE, 2, 1, array('b' => 'B'));

		// data set #6
		$result[] = array($rows, FALSE, 0, 2, array('c' => 'C'));
		$result[] = array($rows, FALSE, 1, 2, array('c' => 'C'));
		$result[] = array($rows, FALSE, 2, 2, array('c' => 'C'));

		$rows = array(
			(object) array('a' => 'A'),
			(object) array('b' => 'B'),
			(object) array('c' => 'C'),
		);

		// data set #9
		$result[] = array($rows, TRUE, 0, 0, (object) array('a' => 'A'));
		$result[] = array($rows, TRUE, 1, 0, (object) array('a' => 'A'));
		$result[] = array($rows, TRUE, 2, 0, (object) array('a' => 'A'));

		// data set #12
		$result[] = array($rows, TRUE, 0, 1, (object) array('b' => 'B'));
		$result[] = array($rows, TRUE, 1, 1, (object) array('b' => 'B'));
		$result[] = array($rows, TRUE, 2, 1, (object) array('b' => 'B'));

		// data set #15
		$result[] = array($rows, TRUE, 0, 2, (object) array('c' => 'C'));
		$result[] = array($rows, TRUE, 1, 2, (object) array('c' => 'C'));
		$result[] = array($rows, TRUE, 2, 2, (object) array('c' => 'C'));

		return $result;
	}

	/**
	 * @covers  Database_Result_Array::offsetGet
	 *
	 * @dataProvider    provider_offset_get_after_seek
	 *
	 * @param   array           $rows       Data set
	 * @param   string|boolean  $as_object
	 * @param   integer         $position
	 * @param   integer         $offset
	 * @param   array           $expected
	 */
	public function test_offset_get_after_seek($rows, $as_object, $position, $offset, $expected)
	{
		$result = new Database_Result_Array($rows, $as_object);
		$result->seek($position);

		$this->assertEquals($expected, $result->offsetGet($offset));
		$this->assertSame($position, $result->key(), 'Do not move pointer');
	}

	public function provider_offset_get_invalid()
	{
		$result = array();

		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		// data set #0
		$result[] = array($rows, FALSE, -5);
		$result[] = array($rows, FALSE, -1);
		$result[] = array($rows, FALSE, 7);
		$result[] = array($rows, FALSE, 8);
		$result[] = array($rows, FALSE, 10);

		$rows = array(
			(object) array('a' => 'A'),
			(object) array('b' => 'B'),
			(object) array('c' => 'C'),
		);

		// data set #5
		$result[] = array($rows, TRUE, -5);
		$result[] = array($rows, TRUE, -1);
		$result[] = array($rows, TRUE, 7);
		$result[] = array($rows, TRUE, 8);
		$result[] = array($rows, TRUE, 10);

		return $result;
	}

	/**
	 * @covers  Database_Result_Array::offsetGet
	 *
	 * @dataProvider    provider_offset_get_invalid
	 *
	 * @param   array   $rows       Data set
	 * @param   integer $offset
	 */
	public function test_offset_get_invalid($rows, $as_object, $offset)
	{
		$result = new Database_Result_Array($rows, $as_object);

		$this->assertNull($result->offsetGet($offset));
	}
}
