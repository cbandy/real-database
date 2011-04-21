<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  PDO
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Result_Test extends Database_PDO_TestCase
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

	public function provider_construct()
	{
		$all = Database::factory()
			->select(array('value'))
			->from($this->_table);

		return array
		(
			array($all, FALSE, array(
				array('value' => 50),
				array('value' => 55),
				array('value' => 60),
				array('value' => 60),
				array('value' => 65),
				array('value' => 65),
				array('value' => 65),
			)),
			array($all, TRUE, array(
				(object) array('value' => 50),
				(object) array('value' => 55),
				(object) array('value' => 60),
				(object) array('value' => 60),
				(object) array('value' => 65),
				(object) array('value' => 65),
				(object) array('value' => 65),
			)),
		);
	}

	/**
	 * @covers  Database_PDO_Result::__construct
	 * @dataProvider    provider_construct
	 *
	 * @param   SQL_Expression  $query
	 * @param   string|boolean  $as_object
	 * @param   array           $expected
	 */
	public function test_construct($query, $as_object, $expected)
	{
		$db = Database::factory();
		$statement = $db->prepare($db->quote_expression($query));
		$statement->execute();

		$result = new Database_PDO_Result($statement, $as_object, array());

		$this->assertEquals($expected, $result->as_array());
	}

	public function provider_construct_object_arguments()
	{
		return array
		(
			array(array()),
			array(array(1)),
			array(array('a', 'b')),
		);
	}

	/**
	 * @covers  Database_PDO_Result::__construct
	 *
	 * @dataProvider    provider_construct_object_arguments
	 *
	 * @todo This test would be better using a mocked constructor
	 *
	 * @param   array   $arguments
	 */
	public function test_construct_object_arguments($arguments)
	{
		$db = Database::factory();
		$statement = $db->prepare($db->quote_expression(
			$db->select(array('value'))->from($this->_table)
		));
		$statement->execute();

		$result = new Database_PDO_Result(
			$statement,
			'Database_PDO_Result_Test_Constructor',
			$arguments
		);

		$this->assertSame($arguments, $result->current()->arguments());
	}
}

/**
 * Class to expose the arguments passed to a constructor. Remove if/when
 * constructors can be mocked.
 */
class Database_PDO_Result_Test_Constructor
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
