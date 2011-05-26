<?php

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Database_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO SQLite extension not installed');

		if ( ! Database::factory() instanceof Database_PDO_SQLite)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for SQLite using PDO');
	}

	protected $_table = 'kohana_test_table';

	public function provider_create_table()
	{
		return array(
			array(array(), new Database_SQLite_Create_Table),
			array(array('a'), new Database_SQLite_Create_Table('a')),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::create_table
	 *
	 * @dataProvider    provider_create_table
	 *
	 * @param   array                           $arguments
	 * @param   Database_SQLite_Create_Table    $expected
	 */
	public function test_create_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PDO_SQLite::create_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_datatype()
	{
		return array
		(
			array('blob', 'type', 'binary'),
			array('float', 'type', 'float'),
			array('integer', 'type', 'integer'),
			array('varchar', 'type', 'string'),

			array('varchar', NULL, array('type' => 'string')),

			array('not-a-type', 'type', NULL),
			array('not-a-type', NULL, array()),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::datatype
	 * @dataProvider    provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	public function provider_ddl_column()
	{
		return array(
			array(array(), new Database_SQLite_DDL_Column),
			array(array('a'), new Database_SQLite_DDL_Column('a')),
			array(array('a', 'b'), new Database_SQLite_DDL_Column('a', 'b')),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::ddl_column
	 *
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array                       $arguments
	 * @param   Database_SQLite_DDL_Column  $expected
	 */
	public function test_ddl_column($arguments, $expected)
	{
		$column = call_user_func_array('Database_PDO_SQLite::ddl_column', $arguments);
		$this->assertEquals($expected, $column);
	}

	public function provider_identical()
	{
		return array(
			array(array('a', '=', 'b'), new Database_SQLite_Identical('a', '=', 'b')),
			array(array('a', '<>', 'b'), new Database_SQLite_Identical('a', '<>', 'b')),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::identical
	 *
	 * @dataProvider    provider_identical
	 *
	 * @param   array                       $arguments
	 * @param   Database_SQLite_Identical   $expected
	 */
	public function test_identical($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database_PDO_SQLite::identical', $arguments)
		);
	}

	public function provider_insert()
	{
		return array(
			array(array(), new Database_SQLite_Insert),
			array(array('a'), new Database_SQLite_Insert('a')),
			array(
				array('a', array('b')),
				new Database_SQLite_Insert('a', array('b'))
			),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::insert
	 *
	 * @dataProvider    provider_insert
	 *
	 * @param   array                   $arguments
	 * @param   Database_SQLite_Insert  $expected
	 */
	public function test_insert($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PDO_SQLite::insert', $arguments);
		$this->assertEquals($expected, $statement);
	}

	/**
	 * @covers  Database_PDO::prepare
	 */
	public function test_prepare_error()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'syntax error', 'HY000');

		$db->prepare('kohana invalid sql');
	}

	public function provider_quote_literal()
	{
		return array
		(
			array(NULL, 'NULL'),
			array(FALSE, "'0'"),
			array(TRUE, "'1'"),

			array(0, '0'),
			array(-1, '-1'),
			array(51678, '51678'),
			array(12.345, '12.345000'),

			array('string', "'string'"),
			array("multiple\nlines", "'multiple\nlines'"),
			array("single'quote", "'single''quote'"),
			array("double\"quote", "'double\"quote'"),

			array(new Database_Binary("\x0"), "''"),
			array(new Database_Binary("\x1"), "'\x1'"),
		);
	}

	/**
	 * @covers  Database_PDO::escape_literal
	 * @covers  Database_PDO_SQLite::quote_literal
	 *
	 * @dataProvider provider_quote_literal
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_literal($value, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->quote_literal($value));
	}

	public function provider_table_columns()
	{
		return array
		(
			array('integer', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'integer',
			)),
			array('numeric', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'numeric',
			)),
			array('numeric(10)', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'numeric',
				'numeric_precision' => 10,
			)),
			array('numeric(10,5)', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'numeric',
				'numeric_precision' => 10,
				'numeric_scale' => 5,
			)),
			array('real', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'real',
			)),
			array('text', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'text',
			)),
			array('varchar(50)', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'varchar',
				'character_maximum_length' => 50,
			)),

			array('int DEFAULT 5', array(
				'column_default' => 5,
				'is_nullable' => 'YES',
				'data_type' => 'int',
			)),
			array('int DEFAULT 5 NOT NULL', array(
				'column_default' => 5,
				'is_nullable' => 'NO',
				'data_type' => 'int',
			)),
		);
	}

	/**
	 * @covers  Database_PDO_SQLite::table_columns
	 * @dataProvider provider_table_columns
	 */
	public function test_table_columns($column, $expected)
	{
		$this->markTestSkipped();

		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$expected = array_merge(array(
			'column_name'       => 'field',
			'ordinal_position'  => 1,
			'column_default'    => NULL,
			'is_nullable'       => NULL,
			'data_type'         => NULL,
			'character_maximum_length'  => NULL,
			'numeric_precision' => NULL,
			'numeric_scale'     => NULL,
		), $expected);

		$db->execute_command('DROP TABLE '.$table);
		$db->execute_command('CREATE TEMPORARY TABLE '.$table."( field $column )");

		$result = $db->table_columns($this->_table);

		$this->assertEquals($expected, $result['field']);
	}

	/**
	 * @covers  Database_PDO_SQLite::table_columns
	 */
	public function test_table_columns_no_table()
	{
		$db = Database::factory();

		$this->assertSame(array(), $db->table_columns('kohana-table-does-not-exist'));
	}
}
