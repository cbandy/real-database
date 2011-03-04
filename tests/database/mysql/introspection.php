<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Introspection_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('MySQL extension not installed');

		if ( ! Database::factory() instanceof Database_MySQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for MySQL');
	}

	protected $_information_schema_defaults = array
	(
		'column_name'       => NULL,
		'ordinal_position'  => NULL,
		'column_default'    => NULL,
		'is_nullable'       => NULL,
		'data_type'         => NULL,
		'character_maximum_length'  => NULL,
		'numeric_precision' => NULL,
		'numeric_scale'     => NULL,
		'collation_name'    => NULL,
		'column_type'       => NULL,
		'column_key'        => NULL,
		'extra'             => NULL,
		'privileges'        => 'select,insert,update,references',
		'column_comment'    => NULL,
	);

	protected $_table = 'kohana_introspect_test_table';

	public function tearDown()
	{
		$db = Database::factory();

		$db->execute_command('DROP TABLE IF EXISTS '.$db->quote_table($this->_table));
	}

	/**
	 * Results for the TIMESTAMP type vary between MySQL versions
	 *
	 * @covers  Database_MySQL::table_columns
	 */
	public function test_table_column_timestamp()
	{
		$db = Database::factory();
		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'ordinal_position' => 1,
			'is_nullable' => 'YES',
			'column_default' => 'CURRENT_TIMESTAMP',
			'is_nullable' => 'NO',
			'data_type' => 'timestamp',
			'column_type' => 'timestamp',
		));

		if (version_compare($db->execute_query('SELECT VERSION()')->get(), '5.1', '>'))
		{
			$expected['extra'] = 'on update CURRENT_TIMESTAMP';
		}

		$db->execute_command('CREATE TABLE '.$db->quote_table($this->_table).' ( field timestamp )');

		$result = $db->table_columns($this->_table);

		$this->assertEquals($expected, $result['field']);
	}

	public function provider_table_column_type()
	{
		return array
		(
			// Binary

			array('binary(50)', array(
				'data_type' => 'binary',
				'character_maximum_length' => 50,
				'column_type' => 'binary(50)',
			)),
			array('varbinary(30)', array(
				'data_type' => 'varbinary',
				'character_maximum_length' => 30,
				'column_type' => 'varbinary(30)',
			)),

			array('blob', array(
				'data_type' => 'blob',
				'character_maximum_length' => 65535,
				'column_type' => 'blob',
			)),
			array('tinyblob', array(
				'data_type' => 'tinyblob',
				'character_maximum_length' => 255,
				'column_type' => 'tinyblob',
			)),
			array('mediumblob', array(
				'data_type' => 'mediumblob',
				'character_maximum_length' => 16777215,
				'column_type' => 'mediumblob',
			)),
			array('longblob', array(
				'data_type' => 'longblob',
				'character_maximum_length' => 4294967295,
				'column_type' => 'longblob',
			)),

			// Date and Time

			array('date', array(
				'data_type' => 'date',
				'column_type' => 'date',
			)),
			array('time', array(
				'data_type' => 'time',
				'column_type' => 'time',
			)),
			array('datetime', array(
				'data_type' => 'datetime',
				'column_type' => 'datetime',
			)),

			// Decimal

			array('decimal(13,7)', array(
				'data_type' => 'decimal',
				'numeric_precision' => 13,
				'numeric_scale' => 7,
				'column_type' => 'decimal(13,7)',
			)),
			array('decimal(5)', array(
				'data_type' => 'decimal',
				'numeric_precision' => 5,
				'numeric_scale' => 0,
				'column_type' => 'decimal(5,0)',
			)),
			array('decimal unsigned', array(
				'data_type' => 'decimal unsigned',
				'numeric_precision' => 10,
				'numeric_scale' => 0,
				'column_type' => 'decimal(10,0) unsigned',
			)),

			// Float

			array('double(50,30)', array(
				'data_type' => 'double',
				'numeric_precision' => 50,
				'numeric_scale' => 30,
				'column_type' => 'double(50,30)',
			)),

			array('float', array(
				'data_type' => 'float',
				'numeric_precision' => 12,
				'column_type' => 'float',
			)),

			// Integer

			array('integer', array(
				'data_type' => 'int',
				'numeric_precision' => 10,
				'numeric_scale' => 0,
				'column_type' => 'int(11)',
			)),
			array('tinyint', array(
				'data_type' => 'tinyint',
				'numeric_precision' => 3,
				'numeric_scale' => 0,
				'column_type' => 'tinyint(4)',
			)),
			array('smallint', array(
				'data_type' => 'smallint',
				'numeric_precision' => 5,
				'numeric_scale' => 0,
				'column_type' => 'smallint(6)',
			)),
			array('mediumint', array(
				'data_type' => 'mediumint',
				'numeric_precision' => 7,
				'numeric_scale' => 0,
				'column_type' => 'mediumint(9)',
			)),
			array('bigint', array(
				'data_type' => 'bigint',
				'numeric_precision' => 19,
				'numeric_scale' => 0,
				'column_type' => 'bigint(20)',
			)),

			array('integer unsigned', array(
				'data_type' => 'int unsigned',
				'numeric_precision' => 10,
				'numeric_scale' => 0,
				'column_type' => 'int(10) unsigned',
			)),
			array('tinyint unsigned', array(
				'data_type' => 'tinyint unsigned',
				'numeric_precision' => 3,
				'numeric_scale' => 0,
				'column_type' => 'tinyint(3) unsigned',
			)),
			array('smallint unsigned', array(
				'data_type' => 'smallint unsigned',
				'numeric_precision' => 5,
				'numeric_scale' => 0,
				'column_type' => 'smallint(5) unsigned',
			)),
			array('mediumint unsigned', array(
				'data_type' => 'mediumint unsigned',
				'numeric_precision' => 7,
				'numeric_scale' => 0,
				'column_type' => 'mediumint(8) unsigned',
			)),
			array('bigint unsigned', array(
				'data_type' => 'bigint unsigned',
				// MySQL 5.1.51
				'numeric_precision' => 20,
				'numeric_scale' => 0,
				'column_type' => 'bigint(20) unsigned',
			)),
		);
	}

	/**
	 * Test literal types. No `collation_name` is expected.
	 *
	 * @covers  Database_MySQL::table_columns
	 * @dataProvider    provider_table_column_type
	 */
	public function test_table_column_type($column, $expected)
	{
		$db = Database::factory();
		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'ordinal_position' => 1,
			'is_nullable' => 'YES',
		), $expected);

		$db->execute_command('CREATE TABLE '.$db->quote_table($this->_table)." ( field $column )");

		$result = $db->table_columns($this->_table);

		$this->assertEquals($expected, $result['field']);
	}

	public function provider_table_column_type_collation()
	{
		return array
		(
			// Character

			array('char(30)', array(
				'data_type' => 'char',
				'character_maximum_length' => 30,
				'column_type' => 'char(30)',
			)),
			array('varchar(40)', array(
				'data_type' => 'varchar',
				'character_maximum_length' => 40,
				'column_type' => 'varchar(40)',
			)),

			array('text', array(
				'data_type' => 'text',
				'character_maximum_length' => 65535,
				'column_type' => 'text',
			)),
			array('tinytext', array(
				'data_type' => 'tinytext',
				'character_maximum_length' => 255,
				'column_type' => 'tinytext',
			)),
			array('mediumtext', array(
				'data_type' => 'mediumtext',
				'character_maximum_length' => 16777215,
				'column_type' => 'mediumtext',
			)),
			array('longtext', array(
				'data_type' => 'longtext',
				'character_maximum_length' => 4294967295,
				'column_type' => 'longtext',
			)),

			// Enum and Set

			array("enum('a','b','c')", array(
				'data_type' => 'enum',
				'character_maximum_length' => 1,
				'column_type' => "enum('a','b','c')",
				'options' => array('a', 'b', 'c'),
			)),

			array("set('x','y','z')", array(
				'data_type' => 'set',
				'character_maximum_length' => 5,
				'column_type' => "set('x','y','z')",
				'options' => array('x', 'y', 'z'),
			)),
		);
	}

	/**
	 * Test collated data types. The default collation is expected.
	 *
	 * @covers  Database_MySQL::table_columns
	 * @dataProvider    provider_table_column_type_collation
	 */
	public function test_table_column_type_collation($column, $expected)
	{
		$db = Database::factory();
		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'ordinal_position' => 1,
			'is_nullable' => 'YES',
			'collation_name' => $db->execute_query('SELECT @@collation_database')->get(),
		), $expected);

		$db->execute_command('CREATE TABLE '.$db->quote_table($this->_table)." ( field $column )");

		$result = $db->table_columns($this->_table);

		$this->assertEquals($expected, $result['field']);
	}

	public function provider_table_columns_argument()
	{
		return array
		(
			array(array($this->_table)),
			array(new SQL_Identifier($this->_table)),
		);
	}

	/**
	 * Test different arguments to table_columns()
	 *
	 * @covers  Database_MySQL::table_columns
	 * @dataProvider    provider_table_columns_argument
	 */
	public function test_table_columns_argument($input)
	{
		$db = Database::factory();
		$db->execute_command('CREATE TABLE '.$db->quote_table($this->_table).' ( field date )');

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'column_type' => 'date',
			'data_type' => 'date',
			'ordinal_position' => 1,
			'is_nullable' => 'YES',
		));

		$result = $db->table_columns($input);

		$this->assertEquals($expected, $result['field']);
	}
}
