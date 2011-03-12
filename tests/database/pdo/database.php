<?php

require_once dirname(dirname(__FILE__)).'/abstract/database'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Database_Test extends Database_Abstract_Database_Test
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');

		if ( ! Database::factory() instanceof Database_PDO)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PDO');
	}

	protected $_table = 'kohana_test_table';

	/**
	 * @covers  Database_PDO::execute_query
	 */
	public function test_execute_query_command()
	{
		$db = Database::factory();

		$this->assertNull($db->execute_query('DELETE FROM '.$db->quote_table($this->_table)));
	}

	/**
	 * @covers  Database_PDO::prepare
	 */
	public function test_prepare()
	{
		$db = Database::factory();
		$statement = $db->prepare('SELECT * FROM '.$db->quote_table($this->_table));

		$this->assertType('PDOStatement', $statement);
	}

	public function provider_prepare_statement()
	{
		return array
		(
			array(
				'DELETE FROM $table', array(),
				'DELETE FROM $table',
			),
			array(
				'DELETE FROM ?', array(new SQL_Table($this->_table)),
				'DELETE FROM $table',
			),
			array(
				'DELETE FROM :table', array(':table' => new SQL_Table($this->_table)),
				'DELETE FROM $table',
			),
			array(
				'DELETE FROM $table WHERE ?', array(new SQL_Conditions(new SQL_Column('value'), '=', 60)),
				'DELETE FROM $table WHERE $column = ?',
			),
			array(
				'DELETE FROM $table WHERE :condition', array(':condition' => new SQL_Conditions(new SQL_Column('value'), '=', 60)),
				'DELETE FROM $table WHERE $column = ?',
			),
			array(
				'DELETE FROM $table WHERE :condition AND :condition', array(':condition' => new SQL_Conditions(new SQL_Column('value'), '=', 60)),
				'DELETE FROM $table WHERE $column = ? AND $column = ?', array(1 => 60, 60),
			),
			array(
				'DELETE FROM $table WHERE $column = ?', array(60),
				'DELETE FROM $table WHERE $column = ?',
			),
			array(
				'DELETE FROM $table WHERE $column = :value', array(':value' => 60),
				'DELETE FROM $table WHERE $column = ?',
			),
			array(
				'DELETE FROM $table WHERE $column = :value AND $column = :value', array(':value' => 60),
				'DELETE FROM $table WHERE $column = ? AND $column = ?',
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(60, 70, 80)),
				'DELETE FROM $table WHERE $column IN (?, ?, ?)',
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(60, 70, array(80))),
				'DELETE FROM $table WHERE $column IN (?, ?, ?)',
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(60, new SQL_Expression(':name', array(':name' => 70)), 80)),
				'DELETE FROM $table WHERE $column IN (?, ?, ?)',
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(new SQL_Identifier('value'), 70, 80)),
				'DELETE FROM $table WHERE $column IN ($column, ?, ?)',
			),
		);
	}

	/**
	 * @covers  Database::_parse
	 * @covers  Database::_parse_value
	 * @covers  Database_PDO::prepare_statement
	 * @dataProvider    provider_prepare_statement
	 */
	public function test_prepare_statement($input_sql, $input_params, $expected_sql)
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column('value');

		$input_sql = strtr($input_sql, array('$table' => $table, '$column' => $column));
		$expected_sql = strtr($expected_sql, array('$table' => $table, '$column' => $column));

		$prepared = $db->prepare_statement(new SQL_Expression($input_sql, $input_params));

		$this->assertType('Database_PDO_Statement', $prepared);
		$this->assertSame($expected_sql, (string) $prepared);
	}
}
