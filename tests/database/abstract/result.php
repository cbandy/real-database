<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
abstract class Database_Abstract_Result_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * Return a Database_Result of three rows with one column:
	 *
	 *  value
	 *  -----
	 *  50
	 *  55
	 *  60
	 *
	 * @return  Database_Result
	 */
	abstract protected function _select_all();

	/**
	 * Return a Database_Result of one row with one column:
	 *
	 *  value
	 *  -----
	 *  NULL
	 *
	 * @return  Database_Result
	 */
	abstract protected function _select_null();

	public function test_count()
	{
		$result = $this->_select_all();

		$this->assertSame(3, $result->count());
	}

	public function test_get()
	{
		$result = $this->_select_all();

		$this->assertEquals(50, $result->get(), 'void');
		$this->assertEquals(50, $result->get('value'), 'value');
		$this->assertEquals(50, $result->get('value', 'other'), 'default');
		$this->assertEquals('other', $result->get('non', 'other'), 'non-existent');
	}

	public function test_get_after_next()
	{
		$result = $this->_select_all()->next();

		$this->assertEquals(55, $result->get(), 'void');
		$this->assertEquals(55, $result->get('value'), 'value');
		$this->assertEquals(55, $result->get('value', 'other'), 'default');
		$this->assertEquals('other', $result->get('non', 'other'), 'non-existent');
	}

	public function test_get_null()
	{
		$result = $this->_select_null();

		$this->assertNull($result->get(), 'void');
		$this->assertNull($result->get('value'), 'value');
		$this->assertSame('other', $result->get('value', 'other'), 'default');
	}

	public function tests_offset_exists()
	{
		$result = $this->_select_all();

		$this->assertTrue($result->offsetExists(0));
		$this->assertTrue($result->offsetExists(2));

		$this->assertFalse($result->offsetExists(-1));
		$this->assertFalse($result->offsetExists(3));
	}

	public function test_offset_get_error()
	{
		$result = $this->_select_all();

		try
		{
			$result->offsetGet(-1);

			$this->setExpectedException('OutOfBoundsException');
		}
		catch (OutOfBoundsException $e) {}

		try
		{
			$result->offsetGet(3);

			$this->setExpectedException('OutOfBoundsException');
		}
		catch (OutOfBoundsException $e) {}
	}

	/**
	 * @expectedException Kohana_Exception
	 */
	public function test_offset_set()
	{
		$result = $this->_select_all();

		$result->offsetSet(0, TRUE);
	}

	/**
	 * @expectedException Kohana_Exception
	 */
	public function test_offset_unset()
	{
		$result = $this->_select_all();

		$result->offsetUnset(0);
	}

	public function test_seek_error()
	{
		$result = $this->_select_all();

		try
		{
			$result->seek(-1);

			$this->setExpectedException('OutOfBoundsException');
		}
		catch (OutOfBoundsException $e) {}

		try
		{
			$result->seek(3);

			$this->setExpectedException('OutOfBoundsException');
		}
		catch (OutOfBoundsException $e) {}
	}
}
