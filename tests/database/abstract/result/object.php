<?php

require_once dirname(dirname(__FILE__)).'/result'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
abstract class Database_Abstract_Result_Object_Test extends Database_Abstract_Result_Test
{
	public function test_array()
	{
		$result = $this->_select_all();

		$this->assertEquals(array( (object) array('value' => 50), (object) array('value' => 55), (object) array('value' => 60)), $result->as_array());
		$this->assertEquals(array(50 => (object) array('value' => 50), 55 => (object) array('value' => 55), 60 => (object) array('value' => 60)), $result->as_array('value'));
		$this->assertEquals(array(50, 55, 60), $result->as_array(NULL, 'value'));
		$this->assertEquals(array(50 => 50, 55 => 55, 60 => 60), $result->as_array('value', 'value'));
	}

	public function test_array_position()
	{
		$result = $this->_select_all();

		$this->assertEquals( (object) array('value' => 55), $result->next()->current(), 'Offset');
		$this->assertEquals(array( (object) array('value' => 50), (object) array('value' => 55), (object) array('value' => 60)), $result->as_array());
		$this->assertEquals( (object) array('value' => 55), $result->current(), 'Same position');
	}

	public function test_current()
	{
		$result = $this->_select_all();

		$this->assertEquals( (object) array('value' => 50), $result->current());
		$this->assertEquals( (object) array('value' => 50), $result->current(), 'Do not advance');
	}

	public function test_next()
	{
		$result = $this->_select_all();

		$this->assertSame($result, $result->next(), 'Chainable (1)');
		$this->assertSame(1, $result->key());
		$this->assertEquals( (object) array('value' => 55), $result->current());

		$this->assertSame($result, $result->next(), 'Chainable (2)');
		$this->assertSame(2, $result->key());
		$this->assertEquals( (object) array('value' => 60), $result->current());

		$this->assertSame($result, $result->next(), 'Chainable (3)');
		$this->assertFalse($result->valid());
	}

	public function test_offset_get()
	{
		$result = $this->_select_all();

		$this->assertEquals( (object) array('value' => 50), $result->offsetGet(0));
		$this->assertEquals( (object) array('value' => 60), $result->offsetGet(2));
	}

	public function test_prev()
	{
		$result = $this->_select_all();

		$result->seek(2);

		$this->assertSame($result, $result->prev(), 'Chainable (1)');
		$this->assertSame(1, $result->key());
		$this->assertEquals( (object) array('value' => 55), $result->current());

		$this->assertSame($result, $result->prev(), 'Chainable (2)');
		$this->assertSame(0, $result->key());
		$this->assertEquals( (object) array('value' => 50), $result->current());

		$this->assertSame($result, $result->prev(), 'Chainable (3)');
		$this->assertFalse($result->valid());
	}

	public function test_rewind()
	{
		$result = $this->_select_all();

		$result->next();

		$this->assertSame($result, $result->rewind(), 'Chainable');
		$this->assertSame(0, $result->key());
		$this->assertEquals( (object) array('value' => 50), $result->current());
	}

	public function test_seek()
	{
		$result = $this->_select_all();

		$result->seek(2);

		$this->assertSame(2, $result->key());
		$this->assertEquals( (object) array('value' => 60), $result->current());

		$result->seek(0);

		$this->assertSame(0, $result->key());
		$this->assertEquals( (object) array('value' => 50), $result->current());
	}
}
