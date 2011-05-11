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

	public function provider_parse_statement()
	{
		$db = Database::factory();

		$result = array(
			array(new SQL_Expression(''), new Database_Statement('')),

			// data set #1
			array(
				new SQL_Expression('?', array('a')),
				new Database_Statement('?', array(1 => 'a'))
			),
			array(
				new SQL_Expression('?', array(new SQL_Expression('a'))),
				new Database_Statement('a')
			),
			array(
				new SQL_Expression('?', array(new SQL_Identifier('a'))),
				new Database_Statement($db->quote_identifier('a'))
			),

			// data set #4
			array(
				new SQL_Expression(':a', array(':a' => 'b')),
				new Database_Statement('?', array(1 => 'b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => new SQL_Expression('b'))),
				new Database_Statement('b')
			),
			array(
				new SQL_Expression(':a', array(':a' => new SQL_Identifier('b'))),
				new Database_Statement($db->quote_identifier('b'))
			),

			// data set #7
			array(
				new SQL_Expression('?', array(array())),
				new Database_Statement('')
			),
			array(
				new SQL_Expression('?', array(array('a', 'b'))),
				new Database_Statement('?, ?', array(1 => 'a', 'b'))
			),

			// data set #9
			array(
				new SQL_Expression('?', array(array(new SQL_Expression('a'), 'b'))),
				new Database_Statement('a, ?', array(1 => 'b'))
			),
			array(
				new SQL_Expression('?', array(array(new SQL_Identifier('a'), 'b'))),
				new Database_Statement($db->quote_identifier('a').', ?', array(1 => 'b'))
			),

			// data set #11
			array(
				new SQL_Expression(':a', array(':a' => array('b', new SQL_Expression('c')))),
				new Database_Statement('?, c', array(1 => 'b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => array('b', new SQL_Identifier('c')))),
				new Database_Statement('?, '.$db->quote_identifier('c'), array(1 => 'b'))
			),
		);

		return $result;
	}

	/**
	 * @covers  Database_PDO::parse_statement
	 *
	 * @dataProvider    provider_parse_statement
	 *
	 * @param   SQL_Expression      $argument   Argument to the method
	 * @param   Database_Statement  $expected   Expected result
	 */
	public function test_parse_statement($argument, $expected)
	{
		$db = Database::factory();

		$this->assertEquals($expected, $db->parse_statement($argument));
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
		return array(
			array(
				new SQL_Expression('SELECT 1', array()),
				'SELECT 1', array()
			),
			array(
				new SQL_Expression('SELECT ?', array('a')),
				'SELECT ?', array(1 => 'a')
			),
			array(
				new SQL_Expression('SELECT :a', array(':a' => 'b')),
				'SELECT ?', array(1 => 'b')
			),

			array(
				new Database_Statement('SELECT 1', array()),
				'SELECT 1', array()
			),
			array(
				new Database_Statement('SELECT ?', array(1 => 'a')),
				'SELECT ?', array(1 => 'a'),
			),
		);
	}

	/**
	 * @covers  Database_PDO::prepare_statement
	 *
	 * @dataProvider    provider_prepare_statement
	 */
	public function test_prepare_statement($argument, $sql, $parameters)
	{
		$db = Database::factory();
		$prepared = $db->prepare_statement($argument);

		$this->assertType('Database_PDO_Statement', $prepared);
		$this->assertSame($sql, (string) $prepared);
		$this->assertSame($parameters, $prepared->parameters());
	}
}
