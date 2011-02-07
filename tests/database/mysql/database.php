<?php

require_once dirname(dirname(__FILE__)).'/abstract/database'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Database_Test extends Database_Abstract_Database_Test
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('MySQL extension not installed');

		if ( ! Database::factory() instanceof Database_MySQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for MySQL');
	}

	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();

		$db->execute_command('CREATE TEMPORARY TABLE '.$db->quote_table($this->_table).' (id bigint unsigned AUTO_INCREMENT PRIMARY KEY, value integer)');
		$db->execute_command('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (50), (55), (60)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @covers  Database_MySQL::alter
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array   $arguments
	 */
	public function test_alter_table($arguments)
	{
		$this->_test_method_type('alter', $arguments, 'Database_MySQL_Alter_Table');
	}

	/**
	 * @covers  Database_MySQL::create
	 * @dataProvider    provider_create_index
	 *
	 * @param   array   $arguments
	 */
	public function test_create_index($arguments)
	{
		$this->_test_method_type('create', $arguments, 'Database_MySQL_Create_Index');
	}

	/**
	 * @covers  Database_MySQL::create
	 * @dataProvider    provider_create_table
	 *
	 * @param   array   $arguments
	 */
	public function test_create_table($arguments)
	{
		$this->_test_method_type('create', $arguments, 'Database_MySQL_Create_Table');
	}

	/**
	 * @covers  Database_MySQL::create
	 * @dataProvider    provider_create_view
	 *
	 * @param   array   $arguments
	 */
	public function test_create_view($arguments)
	{
		$this->_test_method_type('create', $arguments, 'Database_MySQL_Create_View');
	}

	public function provider_datatype()
	{
		return array
		(
			array('tinyint unsigned zerofill', NULL, array('type' => 'integer', 'min' => '0', 'max' => '255')),
			array('point', NULL, array('type' => 'binary')),
		);
	}

	/**
	 * @covers  Database_MySQL::datatype
	 * @dataProvider provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	/**
	 * @covers  Database_MySQL::ddl_column
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array   $arguments
	 */
	public function test_ddl_column($arguments)
	{
		$this->_test_method_type('ddl_column', $arguments, 'Database_MySQL_DDL_Column');
	}

	/**
	 * @covers  Database_MySQL::_execute
	 * @dataProvider  provider_execute_command_error
	 * @expectedException Database_Exception
	 *
	 * @param   string|SQL_Expression   $value  Bad SQL statement
	 */
	public function test_execute_command_error($value)
	{
		parent::test_execute_command_error($value);
	}

	/**
	 * @covers  Database_MySQL::execute_command
	 */
	public function test_execute_command_expression()
	{
		$db = $this->sharedFixture;

		$this->assertSame(3, $db->execute_command(new SQL_Expression('DELETE FROM ?', array(new SQL_Table($this->_table)))));
	}

	/**
	 * @covers  Database_MySQL::execute_command
	 */
	public function test_execute_command_query()
	{
		$db = $this->sharedFixture;

		$this->assertSame(3, $db->execute_command('SELECT * FROM '.$db->quote_table($this->_table)), 'Number of returned rows');
	}

	/**
	 * @covers  Database_MySQL::execute_command
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_command()
	{
		$db = $this->sharedFixture;

		$db->execute_command('DELETE FROM '.$db->quote_table($this->_table).'; DELETE FROM '.$db->quote_table($this->_table));
	}

	/**
	 * @covers  Database_MySQL::execute_query
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_query()
	{
		$db = $this->sharedFixture;

		$db->execute_query('SELECT * FROM '.$db->quote_table($this->_table).'; SELECT * FROM '.$db->quote_table($this->_table));
	}

	/**
	 * @covers  Database_MySQL::execute_insert
	 */
	public function test_execute_insert()
	{
		$db = $this->sharedFixture;

		$this->assertSame(array(0,1), $db->execute_insert('', NULL), 'First identity from prior INSERT');
		$this->assertSame(array(1,4), $db->execute_insert('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (65)', NULL));
		$this->assertSame(array(2,5), $db->execute_insert('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (70), (75)', NULL), 'AUTO_INCREMENT of the first row');
	}

	/**
	 * @covers  Database_MySQL::execute_query
	 */
	public function test_execute_query_expression()
	{
		$db = Database::factory();

		$result = $db->execute_query(new SQL_Expression('SELECT ?', array(1)));

		$this->assertType('Database_MySQL_Result', $result);
		$this->assertSame(1, count($result));
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
			array("multiline\nstring", "'multiline\\nstring'"),
		);
	}

	/**
	 * @covers  Database_MySQL::quote_literal
	 * @dataProvider    provider_quote_literal
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_quote_literal($value, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->quote_literal($value));
	}
}
