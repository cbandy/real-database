<?php

require_once dirname(dirname(dirname(__FILE__))).'/abstract/database'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Database_Test extends Database_Abstract_Database_Test
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO SQLite extension not installed');

		if ( ! Database::factory() instanceof Database_PDO_SQLite)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for SQLite using PDO');
	}

	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();
		$table = $db->quote_table($this->_table);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' (id INTEGER PRIMARY KEY, value INTEGER)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (50)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (55)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (60)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @covers  Database_PDO_SQLite::create
	 * @dataProvider    provider_create_index
	 *
	 * @param   array   $arguments
	 */
	public function test_create_index($arguments)
	{
		return parent::test_create_index($arguments);
	}

	/**
	 * @covers  Database_PDO_SQLite::create
	 * @dataProvider    provider_create_table
	 *
	 * @param   array   $arguments
	 */
	public function test_create_table($arguments)
	{
		$this->_test_method_type('create', $arguments, 'Database_SQLite_Create_Table');
	}

	/**
	 * @covers  Database_PDO_SQLite::create
	 * @dataProvider    provider_create_view
	 *
	 * @param   array   $arguments
	 */
	public function test_create_view($arguments)
	{
		return parent::test_create_view($arguments);
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
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	/**
	 * @covers  Database_PDO_SQLite::ddl_column
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array   $arguments
	 */
	public function test_ddl_column($arguments)
	{
		$this->_test_method_type('ddl_column', $arguments, 'Database_SQLite_DDL_Column');
	}

	public function test_execute_command_query()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertSame(1, $db->execute_command('SELECT * FROM '.$table), 'Always one');
		$this->assertSame(2, $db->execute_command('DELETE FROM '.$table.' WHERE value < 60; SELECT * FROM '.$table), 'Count of first statement');
	}

	public function test_execute_compound_command()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		// All statements executed
		$this->assertSame(2, $db->execute_command('DELETE FROM '.$table.' WHERE "id" = 1; DELETE FROM '.$table), 'Count of last statement');
	}

	public function test_execute_compound_command_mixed()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertSame(3, $db->execute_command('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table), 'Count of last statement');
	}

	public function test_execute_compound_query()
	{
		$db = $this->sharedFixture;
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

	public function test_execute_compound_query_mixed()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertType('Database_Result', $db->execute_query('SELECT * FROM '.$table.' WHERE value < 60; DELETE FROM '.$table));

		$this->assertEquals(3, $db->execute_query('SELECT COUNT(*) FROM '.$table)->get(), 'Second statement is not executed');
	}

	public function test_execute_insert()
	{
		$db = $this->sharedFixture;

		$this->assertEquals(array(0,3), $db->execute_insert('', NULL), 'Prior identity');
		$this->assertEquals(array(1,4), $db->execute_insert('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (65)', NULL));
	}

	/**
	 * @covers  Database_PDO_SQLite::insert
	 * @dataProvider    provider_insert
	 *
	 * @param   array   $arguments
	 */
	public function test_insert($arguments)
	{
		$this->_test_method_type('insert', $arguments, 'Database_SQLite_Insert');
	}

	public function test_insert_execute()
	{
		$db = $this->sharedFixture;

		$statement = new Database_SQLite_Insert($this->_table, array('value'));
		$statement->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(1,5), $db->execute($statement), 'Count is always one. Identity is INTEGER PRIMARY KEY of the last row');
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
		$db = $this->sharedFixture;
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
}
