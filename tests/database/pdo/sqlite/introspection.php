<?php
/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Introspection_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'PDO SQLite extension not installed'
			);

		if ( ! Database::factory() instanceof Database_PDO_SQLite)
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Database not configured for SQLite using PDO'
			);
	}

	protected $_information_schema_defaults = array(
		'column_name'       => NULL,
		'ordinal_position'  => NULL,
		'column_default'    => NULL,
		'is_nullable'       => NULL,
		'data_type'         => NULL,
		'character_maximum_length'  => NULL,
		'numeric_precision' => NULL,
		'numeric_scale'     => NULL,
	);

	protected $_table = 'kohana_introspect_test_table';

	public function setUp()
	{
		$db = Database::factory();

		$db->execute_command(
			'DROP TABLE IF EXISTS '.$db->quote_table($this->_table)
		);
	}

	public function provider_table_columns_argument()
	{
		return array(
			array(array($this->_table)),
			array(new SQL_Table($this->_table)),
		);
	}

	/**
	 * Test different arguments to table_columns().
	 *
	 * @covers  Database_PDO_SQLite::table_columns
	 *
	 * @dataProvider    provider_table_columns_argument
	 *
	 * @param   mixed   $input  Argument to the method
	 */
	public function test_table_columns_argument($input)
	{
		$db = Database::factory();
		$db->execute_command(
			'CREATE TABLE '.$db->quote_table($this->_table).' (field int)'
		);

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'data_type' => 'int',
			'is_nullable' => 'YES',
			'ordinal_position' => 1,
		));

		$result = $db->table_columns($input);

		$this->assertSame($expected, $result['field']);
	}

	public function provider_table_columns_constraints()
	{
		return array(
			array('int DEFAULT NULL', array(
				'column_default' => 'NULL',
				'data_type' => 'int',
				'is_nullable' => 'YES',
			)),
			array('int DEFAULT 0', array(
				'column_default' => '0',
				'data_type' => 'int',
				'is_nullable' => 'YES',
			)),
			array('int DEFAULT 1', array(
				'column_default' => '1',
				'data_type' => 'int',
				'is_nullable' => 'YES',
			)),
			array('int DEFAULT ( random() )', array(
				'column_default' => 'random()',
				'data_type' => 'int',
				'is_nullable' => 'YES',
			)),

			array('varchar DEFAULT NULL', array(
				'column_default' => 'NULL',
				'data_type' => 'varchar',
				'is_nullable' => 'YES',
			)),
			array("varchar DEFAULT ''", array(
				'column_default' => "''",
				'data_type' => 'varchar',
				'is_nullable' => 'YES',
			)),
			array("varchar DEFAULT 'a'", array(
				'column_default' => "'a'",
				'data_type' => 'varchar',
				'is_nullable' => 'YES',
			)),
			array("varchar DEFAULT 'NULL'", array(
				'column_default' => "'NULL'",
				'data_type' => 'varchar',
				'is_nullable' => 'YES',
			)),

			array('int NOT NULL', array(
				'data_type' => 'int',
				'is_nullable' => 'NO',
			)),
			array('varchar NOT NULL', array(
				'data_type' => 'varchar',
				'is_nullable' => 'NO',
			)),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::table_columns
	 *
	 * @dataProvider    provider_table_columns_constraints
	 *
	 * @param   string  $column     Column definition
	 * @param   array   $expected   Expected column attributes
	 */
	public function test_table_columns_constraints($column, $expected)
	{
		$db = Database::factory();
		$db->execute_command(
			'CREATE TABLE '.$db->quote_table($this->_table)." (field $column)"
		);

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'ordinal_position' => 1,
		), $expected);

		$result = $db->table_columns($this->_table);

		$this->assertSame($expected, $result['field']);
	}

	/**
	 * @covers  Database_PDO_SQLite::table_columns
	 */
	public function test_table_columns_no_table()
	{
		$db = Database::factory();

		$this->assertSame(
			array(), $db->table_columns('kohana-table-does-not-exist')
		);
	}

	public function provider_table_columns_types()
	{
		return array(

			// Binary

			array('blob', array(
				'data_type' => 'blob',
			)),

			// Character

			array('character(30)', array(
				'data_type' => 'character',
				'character_maximum_length' => '30',
			)),
			array('varchar(40)', array(
				'data_type' => 'varchar',
				'character_maximum_length' => '40',
			)),

			array('text', array(
				'data_type' => 'text',
			)),

			// Fixed-point

			array('decimal(13,7)', array(
				'data_type' => 'decimal',
				'numeric_precision' => '13',
				'numeric_scale' => '7',
			)),
			array('decimal(5)', array(
				'data_type' => 'decimal',
				'numeric_precision' => '5',
			)),
			array('decimal', array(
				'data_type' => 'decimal',
			)),

			// Floating-point

			array('double(50,30)', array(
				'data_type' => 'double',
				'numeric_precision' => '50',
				'numeric_scale' => '30',
			)),

			array('float', array(
				'data_type' => 'float',
			)),

			// Integer

			array('integer', array(
				'data_type' => 'integer',
			)),
			array('bigint', array(
				'data_type' => 'bigint',
			)),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::table_columns
	 *
	 * @dataProvider    provider_table_columns_types
	 *
	 * @param   string  $column     Column data type
	 * @param   array   $expected   Expected column attributes
	 */
	public function test_table_columns_types($column, $expected)
	{
		$db = Database::factory();
		$db->execute_command(
			'CREATE TABLE '.$db->quote_table($this->_table)." (field $column)"
		);

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'is_nullable' => 'YES',
			'ordinal_position' => 1,
		), $expected);

		$result = $db->table_columns($this->_table);

		$this->assertSame($expected, $result['field']);
	}
}
