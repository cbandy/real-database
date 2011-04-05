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
	 *
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

	public function provider_bind_execute()
	{
		$result = array
		(
			array('0', '0'),
			array('1', '1'),
			array('a', 'a'),
			array(new Database_Binary('b'), 'b'),
		);

		if (version_compare(PHP_VERSION, '5.3', '<'))
		{
			// PHP 5.2.x

			$result[] = array(NULL, '0');
			$result[] = array(FALSE, '0');
			$result[] = array(TRUE, '1');
			$result[] = array(0, '0');
			$result[] = array(1, '1');
		}
		else
		{
			// PHP 5.3.x

			$result[] = array(NULL, NULL);
			$result[] = array(FALSE, 0);
			$result[] = array(TRUE, 1);
			$result[] = array(0, 0);
			$result[] = array(1, 1);
		}

		return $result;
	}

	/**
	 * Executing a statement with a bound variable can change the datatype.
	 *
	 * In PHP 5.2, all values are converted to string.
	 * In PHP 5.3, values other than NULL are converted to integer or string.
	 *
	 * @covers  PDOStatement::bindParam
	 * @covers  PDOStatement::execute
	 *
	 * @dataProvider    provider_bind_execute
	 *
	 * @param   mixed   $value      Value used when preparing the statement
	 * @param   mixed   $expected   Value after the statement is executed
	 */
	public function test_bind_execute($value, $expected)
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
}
