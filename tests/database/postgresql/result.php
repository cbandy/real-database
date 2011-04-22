<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Result_Test extends Database_PostgreSQL_TestCase
{
	protected $_table = 'kohana_test_table';

	protected function getDataSet()
	{
		$dataset = new PHPUnit_Extensions_Database_DataSet_CsvDataSet;
		$dataset->addTable(
			Database::factory()->table_prefix().$this->_table,
			dirname(dirname(__FILE__)).'/datasets/values.csv'
		);

		return $dataset;
	}

	public function provider_current()
	{
		$entire = Database::factory()
			->select(array('*'))
			->from($this->_table);

		return array
		(
			array($entire, FALSE, array('id' => 1, 'value' => 50)),
			array($entire, TRUE, (object) array('id' => 1, 'value' => 50)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Result::current
	 * @dataProvider    provider_current
	 *
	 * @param   SQL_Expression  $query
	 * @param   string|boolean  $as_object
	 * @param   array           $expected
	 */
	public function test_current($query, $as_object, $expected)
	{
		$result = Database::factory()->execute_query($query, $as_object);

		$this->assertEquals($expected, $result->current());
		$this->assertEquals($expected, $result->current(), 'Do not move pointer');
	}

	public function provider_current_after_seek()
	{
		$entire = Database::factory()
			->select(array('*'))
			->from($this->_table);

		return array
		(
			array($entire, FALSE, 0, array('id' => 1, 'value' => 50)),
			array($entire, FALSE, 1, array('id' => 2, 'value' => 55)),
			array($entire, FALSE, 2, array('id' => 3, 'value' => 60)),
			array($entire, FALSE, 6, array('id' => 7, 'value' => 65)),
			array($entire, TRUE, 0, (object) array('id' => 1, 'value' => 50)),
			array($entire, TRUE, 1, (object) array('id' => 2, 'value' => 55)),
			array($entire, TRUE, 2, (object) array('id' => 3, 'value' => 60)),
			array($entire, TRUE, 6, (object) array('id' => 7, 'value' => 65)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Result::current
	 * @dataProvider    provider_current_after_seek
	 *
	 * @param   SQL_Expression  $query
	 * @param   string|boolean  $as_object
	 * @param   integer         $position
	 * @param   array           $expected
	 */
	public function test_current_after_seek($query, $as_object, $position, $expected)
	{
		$result = Database::factory()->execute_query($query, $as_object)->seek($position);

		$this->assertEquals($expected, $result->current());
		$this->assertEquals($expected, $result->current(), 'Do not move pointer');
	}

	public function provider_current_object_arguments()
	{
		$entire = Database::factory()
			->select(array('*'))
			->from($this->_table);

		return array
		(
			array($entire, array()),
			array($entire, array(1)),
			array($entire, array('a', 'b')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Result::__construct
	 * @covers  Database_PostgreSQL_Result::current
	 *
	 * @dataProvider    provider_current_object_arguments
	 *
	 * @todo This test would be better using a mocked constructor
	 *
	 * @param   SQL_Expression  $query
	 * @param   array           $arguments
	 */
	public function test_current_object_arguments($query, $arguments)
	{
		$result = Database::factory()
			->execute_query($query, 'Database_PostgreSQL_Result_Test_Constructor', $arguments)
			->current();

		$this->assertSame($arguments, $result->arguments());
	}

	/**
	 * @covers  Database_PostgreSQL_Result::__construct
	 * @covers  Database_PostgreSQL_Result::current
	 *
	 * @param   SQL_Expression  $query
	 * @param   array           $arguments
	 */
	public function test_current_object_arguments_no_constructor()
	{
		$db = Database::factory();

		// No errors about missing constructor
		$db->execute_query(
			$db->select(array('*'))->from($this->_table),
			'stdClass',
			array()
		)->current();
	}

	public function provider_get()
	{
		$entire = Database::factory()
			->select(array('*'))
			->from($this->_table);

		return array
		(
			// data set #0
			array($entire, FALSE, NULL, NULL, 1),
			array($entire, FALSE, 'id', NULL, 1),
			array($entire, FALSE, 'value', NULL, 50),
			array($entire, FALSE, NULL, 'asdf', 1),
			array($entire, FALSE, 'id', 'asdf', 1),
			array($entire, FALSE, 'value', 'asdf', 50),

			// data set #7
			array($entire, TRUE, NULL, NULL, 1),
			array($entire, TRUE, 'id', NULL, 1),
			array($entire, TRUE, 'value', NULL, 50),
			array($entire, TRUE, NULL, 'asdf', 1),
			array($entire, TRUE, 'id', 'asdf', 1),
			array($entire, TRUE, 'value', 'asdf', 50),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Result::get
	 * @dataProvider    provider_get
	 *
	 * @param   SQL_Expression  $query
	 * @param   string|boolean  $as_object
	 * @param   string          $name       First argument to method
	 * @param   mixed           $default    Second argument to method
	 * @param   array           $expected
	 */
	public function test_get($query, $as_object, $name, $default, $expected)
	{
		$result = Database::factory()->execute_query($query, $as_object);

		$this->assertEquals($expected, $result->get($name, $default));
		$this->assertEquals($expected, $result->get($name, $default), 'Do not move pointer');
	}

	public function provider_get_after_seek()
	{
		$entire = Database::factory()
			->select(array('*'))
			->from($this->_table);

		return array
		(
			// data set #0
			array($entire, FALSE, 0, NULL, NULL, 1),
			array($entire, FALSE, 1, NULL, NULL, 2),
			array($entire, FALSE, 2, NULL, NULL, 3),
			array($entire, FALSE, 6, NULL, NULL, 7),

			// data set #4
			array($entire, FALSE, 0, NULL, 'asdf', 1),
			array($entire, FALSE, 1, NULL, 'asdf', 2),
			array($entire, FALSE, 2, NULL, 'asdf', 3),
			array($entire, FALSE, 6, NULL, 'asdf', 7),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Result::get
	 * @dataProvider    provider_get_after_seek
	 *
	 * @param   SQL_Expression  $query
	 * @param   string|boolean  $as_object
	 * @param   integer         $position
	 * @param   string          $name       First argument to method
	 * @param   mixed           $default    Second argument to method
	 * @param   array           $expected
	 */
	public function test_get_after_seek($query, $as_object, $position, $name, $default, $expected)
	{
		$result = Database::factory()->execute_query($query, $as_object)->seek($position);

		$this->assertEquals($expected, $result->get($name, $default));
		$this->assertEquals($expected, $result->get($name, $default), 'Do not move pointer');
	}

	public function provider_get_invalid()
	{
		$empty = Database::factory()
			->select(array('*'))
			->from($this->_table)
			->where('value', '>', 1000);

		return array
		(
			// data set #0
			array($empty, FALSE, NULL, NULL),
			array($empty, FALSE, NULL, 'asdf'),
			array($empty, FALSE, 'value', NULL),
			array($empty, FALSE, 'value', 'asdf'),

			// data set #4
			array($empty, TRUE, NULL, NULL),
			array($empty, TRUE, NULL, 'asdf'),
			array($empty, TRUE, 'value', NULL),
			array($empty, TRUE, 'value', 'asdf'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Result::get
	 * @dataProvider    provider_get_invalid
	 *
	 * @param   SQL_Expression  $query      SQL that returns no rows
	 * @param   string|boolean  $as_object
	 * @param   string          $name       First argument to method
	 * @param   mixed           $default    Second argument to method
	 */
	public function test_get_invalid($query, $as_object, $name, $default)
	{
		$result = Database::factory()->execute_query($query, $as_object);

		$this->assertSame($default, $result->get($name, $default));
		$this->assertSame($default, $result->get($name, $default), 'Do not move pointer');
	}

	public function provider_get_null()
	{
		$null = 'SELECT NULL AS value';

		return array
		(
			// data set #0
			array($null, FALSE, NULL, NULL),
			array($null, FALSE, NULL, 'asdf'),
			array($null, FALSE, 'value', NULL),
			array($null, FALSE, 'value', 'asdf'),

			// data set #4
			array($null, TRUE, NULL, NULL),
			array($null, TRUE, NULL, 'asdf'),
			array($null, TRUE, 'value', NULL),
			array($null, TRUE, 'value', 'asdf'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Result::get
	 * @dataProvider    provider_get_null
	 *
	 * @param   SQL_Expression  $query      SQL that returns a NULL value
	 * @param   string|boolean  $as_object
	 * @param   string          $name       First argument to method
	 * @param   mixed           $default    Second argument to method
	 */
	public function test_get_null($query, $as_object, $name, $default)
	{
		$result = Database::factory()->execute_query($query, $as_object);

		$this->assertSame($default, $result->get($name, $default));
		$this->assertSame($default, $result->get($name, $default), 'Do not move pointer');
	}

	public function provider_as_array()
	{
		$result = array();

		$empty = Database::factory()
			->select(array('*'))
			->from($this->_table)
			->where('value', '>', 1000);

		// data set #0
		$result[] = array($empty, FALSE, NULL, NULL, array());
		$result[] = array($empty, FALSE, NULL, 'id', array());
		$result[] = array($empty, FALSE, NULL, 'value', array());
		$result[] = array($empty, FALSE, 'id', 'value', array());
		$result[] = array($empty, FALSE, 'value', 'id', array());
		$result[] = array($empty, FALSE, 'id', NULL, array());
		$result[] = array($empty, FALSE, 'value', NULL, array());

		// data set #7
		$result[] = array($empty, TRUE, NULL, NULL, array());
		$result[] = array($empty, TRUE, NULL, 'id', array());
		$result[] = array($empty, TRUE, NULL, 'value', array());
		$result[] = array($empty, TRUE, 'id', 'value', array());
		$result[] = array($empty, TRUE, 'value', 'id', array());
		$result[] = array($empty, TRUE, 'id', NULL, array());
		$result[] = array($empty, TRUE, 'value', NULL, array());

		$entire = Database::factory()
			->select(array('*'))
			->from($this->_table);

		// data set #14
		$result[] = array($entire, FALSE, NULL, NULL, array(
			array('id' => 1, 'value' => 50),
			array('id' => 2, 'value' => 55),
			array('id' => 3, 'value' => 60),
			array('id' => 4, 'value' => 60),
			array('id' => 5, 'value' => 65),
			array('id' => 6, 'value' => 65),
			array('id' => 7, 'value' => 65),
		));
		$result[] = array($entire, FALSE, NULL, 'id', array(1, 2, 3, 4, 5, 6, 7));
		$result[] = array($entire, FALSE, NULL, 'value', array(50, 55, 60, 60, 65, 65, 65));
		$result[] = array($entire, FALSE, 'id', 'value', array(
			1 => 50,
			2 => 55,
			3 => 60,
			4 => 60,
			5 => 65,
			6 => 65,
			7 => 65,
		));
		$result[] = array($entire, FALSE, 'value', 'id', array(
			50 => 1,
			55 => 2,
			60 => 4,
			65 => 7,
		));
		$result[] = array($entire, FALSE, 'id', NULL, array(
			1 => array('id' => 1, 'value' => 50),
			2 => array('id' => 2, 'value' => 55),
			3 => array('id' => 3, 'value' => 60),
			4 => array('id' => 4, 'value' => 60),
			5 => array('id' => 5, 'value' => 65),
			6 => array('id' => 6, 'value' => 65),
			7 => array('id' => 7, 'value' => 65),
		));
		$result[] = array($entire, FALSE, 'value', NULL, array(
			50 => array('id' => 1, 'value' => 50),
			55 => array('id' => 2, 'value' => 55),
			60 => array('id' => 4, 'value' => 60),
			65 => array('id' => 7, 'value' => 65),
		));

		// data set #21
		$result[] = array($entire, TRUE, NULL, NULL, array(
			(object) array('id' => 1, 'value' => 50),
			(object) array('id' => 2, 'value' => 55),
			(object) array('id' => 3, 'value' => 60),
			(object) array('id' => 4, 'value' => 60),
			(object) array('id' => 5, 'value' => 65),
			(object) array('id' => 6, 'value' => 65),
			(object) array('id' => 7, 'value' => 65),
		));
		$result[] = array($entire, TRUE, NULL, 'id', array(1, 2, 3, 4, 5, 6, 7));
		$result[] = array($entire, TRUE, NULL, 'value', array(50, 55, 60, 60, 65, 65, 65));
		$result[] = array($entire, TRUE, 'id', 'value', array(
			1 => 50,
			2 => 55,
			3 => 60,
			4 => 60,
			5 => 65,
			6 => 65,
			7 => 65,
		));
		$result[] = array($entire, TRUE, 'value', 'id', array(
			50 => 1,
			55 => 2,
			60 => 4,
			65 => 7,
		));
		$result[] = array($entire, TRUE, 'id', NULL, array(
			1 => (object) array('id' => 1, 'value' => 50),
			2 => (object) array('id' => 2, 'value' => 55),
			3 => (object) array('id' => 3, 'value' => 60),
			4 => (object) array('id' => 4, 'value' => 60),
			5 => (object) array('id' => 5, 'value' => 65),
			6 => (object) array('id' => 6, 'value' => 65),
			7 => (object) array('id' => 7, 'value' => 65),
		));
		$result[] = array($entire, TRUE, 'value', NULL, array(
			50 => (object) array('id' => 1, 'value' => 50),
			55 => (object) array('id' => 2, 'value' => 55),
			60 => (object) array('id' => 4, 'value' => 60),
			65 => (object) array('id' => 7, 'value' => 65),
		));

		return $result;
	}

	/**
	 * @covers  Database_PostgreSQL_Result::as_array
	 * @dataProvider    provider_as_array
	 *
	 * @param   SQL_Expression  $query
	 * @param   string|boolean  $as_object
	 * @param   string          $key        First argument to method
	 * @param   string          $value      Second argument to method
	 * @param   array           $expected
	 */
	public function test_as_array($query, $as_object, $key, $value, $expected)
	{
		$result = Database::factory()->execute_query($query, $as_object);

		$this->assertEquals($expected, $result->as_array($key, $value));
	}
}

/**
 * Class to expose the arguments passed to a constructor. Remove if/when
 * constructors can be mocked.
 */
class Database_PostgreSQL_Result_Test_Constructor
{
	protected $_arguments;

	public function __construct()
	{
		$this->_arguments = func_get_args();
	}

	public function arguments()
	{
		return $this->_arguments;
	}
}
