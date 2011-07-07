<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlserver
 */
class Database_PDO_SQLServer_Introspection_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlsrv'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'PDO SQL Server extension not installed'
			);

		if ( ! Database::factory() instanceof Database_PDO_SQLServer)
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Database not configured for SQL Server using PDO'
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
		'datetime_precision' => NULL,
	);

	protected $_table = 'kohana_introspect_test_table';

	public function setUp()
	{
		$db = Database::factory();

		$db->execute_command(
			new Database_Statement(
				'IF OBJECT_ID(?,?) IS NOT NULL DROP TABLE '
					.$db->quote_table($this->_table),
				array($db->table_prefix().$this->_table, 'U')
			)
		);
	}

	public function provider_table_columns_argument()
	{
		return array(
			array($this->_table),
			array(array($this->_table)),
			array(new SQL_Table($this->_table)),
		);
	}

	/**
	 * Test different arguments to table_columns().
	 *
	 * @covers  Database_PDO_SQLServer::table_columns
	 *
	 * @dataProvider    provider_table_columns_argument
	 *
	 * @param   mixed   $input  Argument to the method
	 */
	public function test_table_columns_argument($input)
	{
		$db = Database::factory();
		$db->execute_command(
			'CREATE TABLE '.$db->quote_table($this->_table).' (field bit)'
		);

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'data_type' => 'bit',
			'ordinal_position' => '1',
			'is_nullable' => 'YES',
		));

		$result = $db->table_columns($input);

		$this->assertSame($expected, $result['field']);
	}

	public function provider_table_columns_constraints()
	{
		return array(
			array('bit DEFAULT NULL', array(
				'column_default' => '(NULL)',
				'data_type' => 'bit',
				'is_nullable' => 'YES',
			)),
			array("bit DEFAULT 'FALSE'", array(
				'column_default' => "('FALSE')",
				'data_type' => 'bit',
				'is_nullable' => 'YES',
			)),
			array("bit DEFAULT 'TRUE'", array(
				'column_default' => "('TRUE')",
				'data_type' => 'bit',
				'is_nullable' => 'YES',
			)),

			array('int DEFAULT NULL', array(
				'column_default' => '(NULL)',
				'data_type' => 'int',
				'is_nullable' => 'YES',
				'numeric_precision' => '10',
				'numeric_scale' => '0',
			)),
			array('int DEFAULT 0', array(
				'column_default' => '((0))',
				'data_type' => 'int',
				'is_nullable' => 'YES',
				'numeric_precision' => '10',
				'numeric_scale' => '0',
			)),
			array('int DEFAULT 1', array(
				'column_default' => '((1))',
				'data_type' => 'int',
				'is_nullable' => 'YES',
				'numeric_precision' => '10',
				'numeric_scale' => '0',
			)),

			array('real DEFAULT RAND()', array(
				'column_default' => '(rand())',
				'data_type' => 'real',
				'is_nullable' => 'YES',
				'numeric_precision' => '24',
			)),

			array('varchar(1) DEFAULT NULL', array(
				'character_maximum_length' => '1',
				'column_default' => '(NULL)',
				'data_type' => 'varchar',
				'is_nullable' => 'YES',
			)),
			array("varchar(1) DEFAULT ''", array(
				'character_maximum_length' => '1',
				'column_default' => "('')",
				'data_type' => 'varchar',
				'is_nullable' => 'YES',
			)),
			array("varchar(1) DEFAULT 'a'", array(
				'character_maximum_length' => '1',
				'column_default' => "('a')",
				'data_type' => 'varchar',
				'is_nullable' => 'YES',
			)),

			array('bit NOT NULL', array(
				'data_type' => 'bit',
				'is_nullable' => 'NO',
			)),
			array('int NOT NULL', array(
				'data_type' => 'int',
				'is_nullable' => 'NO',
				'numeric_precision' => '10',
				'numeric_scale' => '0',
			)),
			array("varchar(1) NOT NULL", array(
				'character_maximum_length' => '1',
				'data_type' => 'varchar',
				'is_nullable' => 'NO',
			)),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::table_columns
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
			'ordinal_position' => '1',
		), $expected);

		$result = $db->table_columns($this->_table);

		$this->assertSame($expected, $result['field']);
	}

	/**
	 * @covers  Database_PDO_SQLServer::table_columns
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

			// Boolean

			array('bit', array(
				'data_type' => 'bit',
			)),

			// Binary

			array('binary', array(
				'character_maximum_length' => '1',
				'data_type' => 'binary',
			)),
			array('varbinary(10)', array(
				'character_maximum_length' => '10',
				'data_type' => 'varbinary',
			)),

			// Character

			array('char(30)', array(
				'character_maximum_length' => '30',
				'data_type' => 'char',
			)),
			array('varchar(40)', array(
				'character_maximum_length' => '40',
				'data_type' => 'varchar',
			)),
			array('varchar', array(
				'character_maximum_length' => '1',
				'data_type' => 'varchar',
			)),
			array('text', array(
				'character_maximum_length' => '2147483647',
				'data_type' => 'text',
			)),

			array('nchar(30)', array(
				'character_maximum_length' => '30',
				'data_type' => 'nchar',
			)),
			array('nvarchar(40)', array(
				'character_maximum_length' => '40',
				'data_type' => 'nvarchar',
			)),
			array('nvarchar', array(
				'character_maximum_length' => '1',
				'data_type' => 'nvarchar',
			)),
			array('ntext', array(
				'character_maximum_length' => '1073741823',
				'data_type' => 'ntext',
			)),

			// Date and Time

			array('date', array(
				'data_type' => 'date',
				'datetime_precision' => '0',
			)),
			array('time(3)', array(
				'data_type' => 'time',
				'datetime_precision' => '3',
			)),
			array('time', array(
				'data_type' => 'time',
				'datetime_precision' => '7',
			)),

			// Fixed-Point

			array('numeric(13,7)', array(
				'data_type' => 'numeric',
				'numeric_precision' => '13',
				'numeric_scale' => '7',
			)),
			array('numeric(5)', array(
				'data_type' => 'numeric',
				'numeric_precision' => '5',
				'numeric_scale' => '0',
			)),
			array('numeric', array(
				'data_type' => 'numeric',
				'numeric_precision' => '18',
				'numeric_scale' => '0',
			)),

			// Floating-Point

			array('float', array(
				'data_type' => 'float',
				'numeric_precision' => '53',
			)),
			array('float(7)', array(
				'data_type' => 'real',
				'numeric_precision' => '24',
			)),
			array('real', array(
				'data_type' => 'real',
				'numeric_precision' => '24',
			)),

			// Integer

			array('integer', array(
				'data_type' => 'int',
				'numeric_precision' => '10',
				'numeric_scale' => '0',
			)),
			array('tinyint', array(
				'data_type' => 'tinyint',
				'numeric_precision' => '3',
				'numeric_scale' => '0',
			)),
			array('smallint', array(
				'data_type' => 'smallint',
				'numeric_precision' => '5',
				'numeric_scale' => '0',
			)),
			array('bigint', array(
				'data_type' => 'bigint',
				'numeric_precision' => '19',
				'numeric_scale' => '0',
			)),

			// Miscellaneous

			array('hierarchyid', array(
				'character_maximum_length' => '892',
				'data_type' => 'hierarchyid',
			)),
			array('money', array(
				'data_type' => 'money',
				'numeric_precision' => '19',
				'numeric_scale' => '4',
			)),
			array('smallmoney', array(
				'data_type' => 'smallmoney',
				'numeric_precision' => '10',
				'numeric_scale' => '4',
			)),
			array('sql_variant', array(
				'character_maximum_length' => '0',
				'data_type' => 'sql_variant',
			)),
			array('uniqueidentifier', array(
				'data_type' => 'uniqueidentifier',
			)),
			array('xml', array(
				'character_maximum_length' => '-1',
				'data_type' => 'xml',
			)),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::table_columns
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
			'ordinal_position' => '1',
			'is_nullable' => 'YES',
		), $expected);

		$result = $db->table_columns($this->_table);

		$this->assertSame($expected, $result['field']);
	}
}
