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

	/**
	 * @covers  Database_PDO::execute_command
	 */
	public function test_execute_command_query()
	{
		$this->markTestSkipped();

		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertSame(1, $db->execute_command('SELECT * FROM '.$table), 'Always one');
		$this->assertSame(2, $db->execute_command('DELETE FROM '.$table.' WHERE value < 60; SELECT * FROM '.$table), 'Count of first statement');
	}

	/**
	 * @covers  Database_PDO::execute_command
	 */
	public function test_execute_compound_command()
	{
		$this->markTestSkipped();

		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		// All statements executed
		$this->assertSame(2, $db->execute_command('DELETE FROM '.$table.' WHERE "id" = 1; DELETE FROM '.$table), 'Count of last statement');
	}

	/**
	 * @covers  Database_PDO::execute_command
	 */
	public function test_execute_compound_command_mixed()
	{
		$this->markTestSkipped();

		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertSame(3, $db->execute_command('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table), 'Count of last statement');
	}

	/**
	 * @covers  Database_PDO::execute_query
	 */
	public function test_execute_compound_query()
	{
		$this->markTestSkipped();

		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$result = $db->execute_query('SELECT * FROM '.$table.' WHERE value < 60; SELECT * FROM '.$table.' WHERE value < 70');

		$this->assertType('Database_Result', $result);
		$this->assertSame(2, count($result), 'First result');
		$this->assertEquals(array(50, 55), $result->as_array(NULL, 'value'), 'First result');

		$this->assertType('Database_Result', $db->execute_query('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table));
		$this->assertEquals(3, $db->execute_query('SELECT COUNT(*) FROM '.$table)->get(), 'Second statement is not executed');

		$this->assertNull($db->execute_query('DELETE FROM '.$table.' WHERE value = 50; DELETE FROM '.$table.' WHERE value = 55; SELECT * FROM '.$table));
		$this->assertEquals(2, $db->execute_query('SELECT COUNT(*) FROM '.$table)->get(), 'Only the first statement is executed');
	}

	/**
	 * @covers  Database_PDO::execute_query
	 */
	public function test_execute_compound_query_mixed()
	{
		$this->markTestSkipped();

		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->assertType('Database_Result', $db->execute_query('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table));

		$this->assertEquals(3, $db->execute_query('SELECT COUNT(*) FROM '.$table)->get(), 'Second statement is not executed');
	}

	/**
	 * @covers  Database_PDO::execute_insert
	 */
	public function test_execute_insert()
	{
		$this->markTestSkipped();

		$db = Database::factory();

		$this->assertEquals(array(0,3), $db->execute_insert('', NULL), 'Prior identity');
		$this->assertEquals(array(1,4), $db->execute_insert('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (65)', NULL));
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

	public function test_insert_execute()
	{
		$this->markTestSkipped();

		$db = Database::factory();

		$statement = new Database_SQLite_Insert($this->_table, array('value'));
		$statement->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(1,5), $db->execute($statement), 'Count is always one. Identity is INTEGER PRIMARY KEY of the last row');
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
		);
	}

	/**
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
