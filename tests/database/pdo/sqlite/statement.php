<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Statement_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO SQLite extension not installed');

		if ( ! Database::factory() instanceof Database_PDO_SQLite)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for SQLite using PDO');
	}

	protected $_table = 'temp_test_table';
	protected $_column = 'value';

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' ('.$column.' integer)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function provider_bind()
	{
		return array
		(
			array(NULL, NULL),
			array(FALSE, FALSE),
			array(TRUE, TRUE),
			array(0, 0),
			array(1, 1),
			array('a', 'a'),
			array(new Database_Binary('b'), 'b'),
		);
	}

	/**
	 * Binding a variable can convert the value to string.
	 *
	 * @link    http://bugs.php.net/38334
	 *
	 * @covers  PDOStatement::bindParam
	 * @dataProvider    provider_bind
	 *
	 * @param   mixed   $initial    Value used when preparing the statement
	 * @param   mixed   $bound      Value after the variable has been bound
	 */
	public function test_bind($initial, $bound)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$statement = $db->prepare_statement(
			new SQL_Expression("DELETE FROM $table WHERE $column = ?", array($initial))
		);

		$var = $initial;
		$this->assertSame($statement, $statement->bind(1, $var), 'Chainable');
		$this->assertSame($bound, $var, 'Modified by PDO during bind');
	}

	protected function _test_bind_execute($value, $expected)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$statement = $db->prepare_statement(
			new SQL_Expression("DELETE FROM $table WHERE $column = ?", array($value))
		);

		$var = $value;
		$statement->bind(1, $var);
		$statement->execute_command();

		$this->assertSame($expected, $var, 'Modified by PDO during execution');
	}

	public function provider_bind_execute_52()
	{
		return array
		(
			array(NULL, '0'),
			array(FALSE, '0'),
			array(TRUE, '1'),
			array(0, '0'),
			array(1, '1'),
			array('a', 'a'),
			array(new Database_Binary('b'), 'b'),
		);
	}

	/**
	 * Executing a statement with a bound variable will convert the value to
	 * string in PHP 5.2.
	 *
	 * @covers  PDOStatement::bindParam
	 * @covers  PDOStatement::execute
	 * @dataProvider    provider_bind_execute_52
	 *
	 * @param   mixed   $value      Value used when preparing the statement
	 * @param   string  $expected   Value after the statement is executed
	 */
	public function test_bind_execute_52($value, $expected)
	{
		if (version_compare(PHP_VERSION, '5.3', '>='))
			$this->markTestSkipped();

		$this->_test_bind_execute($value, $expected);
	}

	public function provider_bind_execute()
	{
		return array
		(
			array(NULL, NULL),
			array(FALSE, 0),
			array(TRUE, 1),
			array(0, 0),
			array(1, 1),
			array('a', 'a'),
			array(new Database_Binary('b'), 'b'),
		);
	}

	/**
	 * Executing a statement with a bound variable will convert values other
	 * than NULL to integer or string.
	 *
	 * @covers  PDOStatement::bindParam
	 * @covers  PDOStatement::execute
	 * @dataProvider    provider_bind_execute
	 *
	 * @param   mixed   $value      Value used when preparing the statement
	 * @param   mixed   $expected   Value after the statement is executed
	 */
	public function test_bind_execute($value, $expected)
	{
		if (version_compare(PHP_VERSION, '5.3', '<'))
			$this->markTestSkipped();

		$this->_test_bind_execute($value, $expected);
	}
}