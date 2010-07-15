<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.result
 */
class Database_Result_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;

	protected function _select_all($as_object = FALSE)
	{
		return $this->_db->execute_query('SELECT * FROM '.$this->_db->quote_table('temp_test_table'). ' ORDER BY value', $as_object);
	}

	public function setUp()
	{
		$this->_db = Database::instance('testing');
		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_db->quote_table('temp_test_table').' (value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_db->quote_table('temp_test_table').' (value) VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$this->_db->quote_table('temp_test_table').' (value) VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$this->_db->quote_table('temp_test_table').' (value) VALUES (60)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_array()
	{
		$result = $this->_select_all();

		$this->assertEquals(array(array('value' => 50), array('value' => 55), array('value' => 60)), $result->as_array());
		$this->assertEquals(array(50 => array('value' => 50), 55 => array('value' => 55), 60 => array('value' => 60)), $result->as_array('value'));
		$this->assertEquals(array(50, 55, 60), $result->as_array(NULL, 'value'));
		$this->assertEquals(array(50 => 50, 55 => 55, 60 => 60), $result->as_array('value', 'value'));

		$result = $this->_select_all(TRUE);

		$this->assertEquals(array( (object) array('value' => 50), (object) array('value' => 55), (object) array('value' => 60)), $result->as_array());
		$this->assertEquals(array(50 => (object) array('value' => 50), 55 => (object) array('value' => 55), 60 => (object) array('value' => 60)), $result->as_array('value'));
		$this->assertEquals(array(50, 55, 60), $result->as_array(NULL, 'value'));
		$this->assertEquals(array(50 => 50, 55 => 55, 60 => 60), $result->as_array('value', 'value'));
	}

	public function test_array_position()
	{
		$result = $this->_select_all();

		$this->assertEquals(array('value' => 55), $result->next()->current(), 'Offset');
		$this->assertEquals(array(array('value' => 50), array('value' => 55), array('value' => 60)), $result->as_array());
		$this->assertEquals(array('value' => 55), $result->current(), 'Same position');

		$result = $this->_select_all(TRUE);

		$this->assertEquals( (object) array('value' => 55), $result->next()->current(), 'Offset');
		$this->assertEquals(array( (object) array('value' => 50), (object) array('value' => 55), (object) array('value' => 60)), $result->as_array());
		$this->assertEquals( (object) array('value' => 55), $result->current(), 'Same position');
	}

	public function test_count()
	{
		$result = $this->_select_all();

		$this->assertSame(3, $result->count());
	}

	public function test_current()
	{
		$result = $this->_select_all();

		$this->assertEquals(array('value' => 50), $result->current());
		$this->assertEquals(array('value' => 50), $result->current(), 'Do not advance');
	}

	public function test_get()
	{
		$result = $this->_select_all();

		$this->assertEquals(50, $result->get(), 'Associative, void');
		$this->assertEquals(50, $result->get('value'), 'Associative, value');
		$this->assertEquals(50, $result->get('value', 'other'), 'Associative, default');
		$this->assertEquals('other', $result->get('non', 'other'), 'Associative, non-existent');

		$result = $this->_select_all(TRUE);

		$this->assertEquals(50, $result->get(), 'Object, void');
		$this->assertEquals(50, $result->get('value'), 'Object, value');
		$this->assertEquals(50, $result->get('value', 'other'), 'Object, default');
		$this->assertEquals('other', $result->get('non', 'other'), 'Object, non-existent');
	}

	public function test_next()
	{
		$result = $this->_select_all();

		$this->assertSame($result, $result->next(), 'Chainable (1)');
		$this->assertSame(1, $result->key());
		$this->assertEquals(array('value' => 55), $result->current());
		$this->assertSame($result, $result->next(), 'Chainable (2)');
		$this->assertSame(2, $result->key());
		$this->assertEquals(array('value' => 60), $result->current());
		$this->assertSame($result, $result->next(), 'Chainable (3)');
		$this->assertFalse($result->valid());
	}

	public function tests_offset_exists()
	{
		$result = $this->_select_all();

		$this->assertTrue($result->offsetExists(0));
		$this->assertTrue($result->offsetExists(2));

		$this->assertFalse($result->offsetExists(-1));
		$this->assertFalse($result->offsetExists(3));
	}

	public function test_offset_get()
	{
		$result = $this->_select_all();

		$this->assertEquals(array('value' => 50), $result->offsetGet(0));
		$this->assertEquals(array('value' => 60), $result->offsetGet(2));

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

	public function test_prev()
	{
		$result = $this->_select_all();

		$result->seek(2);

		$this->assertSame($result, $result->prev(), 'Chainable (1)');
		$this->assertSame(1, $result->key());
		$this->assertEquals(array('value' => 55), $result->current());
		$this->assertSame($result, $result->prev(), 'Chainable (2)');
		$this->assertSame(0, $result->key());
		$this->assertEquals(array('value' => 50), $result->current());
		$this->assertSame($result, $result->prev(), 'Chainable (3)');
		$this->assertFalse($result->valid());
	}

	public function test_rewind()
	{
		$result = $this->_select_all();

		$result->next();

		$this->assertSame($result, $result->rewind(), 'Chainable');
		$this->assertSame(0, $result->key());
		$this->assertEquals(array('value' => 50), $result->current());
	}

	public function test_seek()
	{
		$result = $this->_select_all();

		$result->seek(2);

		$this->assertSame(2, $result->key());
		$this->assertEquals(array('value' => 60), $result->current());

		$result->seek(0);

		$this->assertSame(0, $result->key());
		$this->assertEquals(array('value' => 50), $result->current());

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
