<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Statement_Test extends PHPUnit_Framework_TestCase
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
		$table = $db->quote_table($this->_table);

		$db->execute_command('CREATE TEMPORARY TABLE '.$db->quote_table($this->_table).' (id bigint unsigned AUTO_INCREMENT PRIMARY KEY, value integer)');
		$db->execute_command('INSERT INTO '.$db->quote_table($this->_table).' (value) VALUES (50), (55), (60), (65), (65)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function provider_constructor_name()
	{
		return array
		(
			array('a'),
			array('b'),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::__construct
	 * @covers  Database_MySQL_Statement::__toString
	 * @dataProvider    provider_constructor_name
	 *
	 * @param   string  $value      Statement name
	 */
	public function test_constructor_name($value)
	{
		$db = Database::factory();
		$statement = new Database_MySQL_Statement($db, $value);

		$this->assertSame($db->quote_identifier($value), (string) $statement);
	}

	public function provider_constructor_parameters()
	{
		return array
		(
			array(array('a')),
			array(array('b' => 'c')),
			array(array('d', 'e' => 'f')),
			array(array('g' => 'h', 'i')),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::__construct
	 * @dataProvider    provider_constructor_parameters
	 *
	 * @param   array   $value  Statement parameters
	 */
	public function test_constructor_parameters($value)
	{
		$db = Database::factory();
		$statement = new Database_MySQL_Statement($db, 'name', $value);

		$this->assertSame($value, $statement->parameters);
	}

	public function provider_bind()
	{
		return array
		(
			array(FALSE, TRUE),
			array('a', 'b'),
			array(1, 2),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::bind
	 * @dataProvider    provider_bind
	 *
	 * @param   mixed   $initial    Value used when first binding
	 * @param   mixed   $changed    Value used to alter the bound variable
	 */
	public function test_bind($initial, $changed)
	{
		$db = Database::factory();
		$statement = new Database_MySQL_Statement($db, 'name');

		$var = $initial;
		$this->assertSame($statement, $statement->bind('$1', $var), 'Chainable');
		$this->assertSame($initial, $var, 'Not modified during bind');
		$this->assertSame($initial, $statement->parameters['$1'], 'Parameter visible');

		$var = $changed;
		$this->assertSame($changed, $statement->parameters['$1'], 'Changed by reference');
	}

	/**
	 * @covers  Database_MySQL_Statement::deallocate
	 */
	public function test_deallocate()
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, 'SELECT 1');
		$statement = new Database_MySQL_Statement($db, $name);

		$this->assertNull($statement->deallocate());

		try
		{
			$statement->deallocate();
			$this->fail('Calling deallocate() twice should fail with a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	public function provider_execute_command()
	{
		return array
		(
			array(1, 'INSERT INTO $table (value) VALUES (10)', array()),
			array(2, 'DELETE FROM $table WHERE value = ?', array(65)),
			array(1, 'UPDATE $table SET value = ? WHERE value = 60', array(20)),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::_set_variables
	 * @covers  Database_MySQL_Statement::execute_command
	 * @dataProvider    provider_execute_command
	 *
	 * @param   integer $expected   Expected result
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 */
	public function test_execute_command($expected, $statement, $parameters)
	{
		$db = $this->sharedFixture;
		$name = $db->prepare(NULL, strtr($statement, array('$table' => $db->quote_table($this->_table))));
		$statement = new Database_MySQL_Statement($db, $name, $parameters);

		$this->assertSame($expected, $statement->execute_command());
	}

	public function provider_execute_insert()
	{
		return array
		(
			array(array(2, 6), 'INSERT INTO $table (value) VALUES (10), (20)', array()),
			array(array(1, 6), 'INSERT INTO $table (value) VALUES (?)', array(50)),
			array(array(2, 6), 'INSERT INTO $table (value) VALUES (?), (?)', array(70, 80)),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::_set_variables
	 * @covers  Database_MySQL_Statement::execute_insert
	 * @dataProvider    provider_execute_insert
	 *
	 * @param   array   $expected   Expected result
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 */
	public function test_execute_insert($expected, $statement, $parameters)
	{
		$db = $this->sharedFixture;
		$name = $db->prepare(NULL, strtr($statement, array('$table' => $db->quote_table($this->_table))));
		$statement = new Database_MySQL_Statement($db, $name, $parameters);

		$this->assertEquals($expected, $statement->execute_insert());
	}

	public function provider_execute_query()
	{
		return array
		(
			array('SELECT * FROM $table WHERE value < 60', array(), array(
				array('id' => 1, 'value' => 50),
				array('id' => 2, 'value' => 55),
			)),
			array('SELECT * FROM $table WHERE value < ?', array(60), array(
				array('id' => 1, 'value' => 50),
				array('id' => 2, 'value' => 55),
			)),
			array('SELECT * FROM $table WHERE value > ?', array(55), array(
				array('id' => 3, 'value' => 60),
				array('id' => 4, 'value' => 65),
				array('id' => 5, 'value' => 65),
			)),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::_set_variables
	 * @covers  Database_MySQL_Statement::execute_query
	 * @dataProvider    provider_execute_query
	 *
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 * @param   array   $expected   Expected result
	 */
	public function test_execute_query($statement, $parameters, $expected)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$name = $db->prepare(NULL, strtr($statement, array('$table' => $db->quote_table($this->_table))));
		$statement = new Database_MySQL_Statement($db, $name, $parameters);

		$result = $statement->execute_query();

		$this->assertType('Database_MySQL_Result', $result);
		$this->assertEquals($expected, $result->as_array());
	}

	public function provider_param()
	{
		return array
		(
			array(NULL),
			array(FALSE),
			array(TRUE),
			array(0),
			array(1),
			array('a'),
			array('b'),
		);
	}

	/**
	 * @covers  Database_MySQL_Statement::param
	 * @dataProvider    provider_param
	 *
	 * @param   mixed   $value  Value to assign
	 */
	public function test_param($value)
	{
		$db = Database::factory();
		$statement = new Database_MySQL_Statement($db, 'name');

		$this->assertSame($statement, $statement->param('$1', $value), 'Chainable');
		$this->assertSame($value, $statement->parameters['$1'], 'Parameter visible');
	}

	/**
	 * @covers  Database_MySQL_Statement::parameters
	 */
	public function test_parameters()
	{
		$db = Database::factory();
		$statement = new Database_MySQL_Statement($db, 'name');

		$this->assertSame($statement, $statement->parameters(array('a', 'b')), 'Chainable (1)');
		$this->assertSame(array('a', 'b'), $statement->parameters);

		$this->assertSame($statement, $statement->parameters(array('c' => 'd')), 'Chainable (2)');
		$this->assertSame(array('c' => 'd', 'a', 'b'), $statement->parameters);

		$this->assertSame($statement, $statement->parameters(array(1 => 'e')), 'Chainable (3)');
		$this->assertSame(array(1 => 'e', 'c' => 'd', 0 => 'a'), $statement->parameters);

		$this->assertSame($statement, $statement->parameters(array('c' => 'f')), 'Chainable (4)');
		$this->assertSame(array('c' => 'f', 1 => 'e', 0 => 'a'), $statement->parameters);
	}
}
