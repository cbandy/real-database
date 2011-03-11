<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Statement_Test extends Database_PDO_SQLite_TestCase
{
	protected $_table = 'kohana_test_table';

	protected function getDataSet()
	{
		$dataset = new PHPUnit_Extensions_Database_DataSet_CsvDataSet;
		$dataset->addTable(
			Database::factory()->table_prefix().$this->_table,
			dirname(dirname(dirname(__FILE__))).'/datasets/values.csv'
		);

		return $dataset;
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
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$statement = $db->prepare_statement(
			new SQL_Expression("DELETE FROM $table WHERE value = ?", array($initial))
		);

		$var = $initial;
		$this->assertSame($statement, $statement->bind(1, $var), 'Chainable');
		$this->assertSame($bound, $var, 'Modified by PDO during bind');
	}

	protected function _test_bind_execute($value, $expected)
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$statement = $db->prepare_statement(
			new SQL_Expression("DELETE FROM $table WHERE value = ?", array($value))
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
