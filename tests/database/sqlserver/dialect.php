<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlserver
 */
class Database_SQLServer_Dialect_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'kohana_test_table';

	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlsrv'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'PDO SQL Server extension not installed'
			);

		if ( ! Database::factory() instanceof Database_PDO_SQLServer)
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Database not configured for SQL Server using PDO'
			);
	}

	public function provider_offset_replacements()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array(
			array(
				// Common Table Expression
				'WITH cte AS ('
				.'SELECT TOP 2 value, ROW_NUMBER() OVER(ORDER BY id) AS num'
				.' FROM '.$table.') SELECT * FROM cte WHERE num > 1',
				array(array('value' => 55, 'num' => 2)),
			),
			array(
				// Subquery
				'SELECT * FROM ('
				.'SELECT TOP 2 value, ROW_NUMBER() OVER(ORDER BY id) AS num'
				.' FROM '.$table.') AS qry WHERE qry.num > 1',
				array(array('value' => 55, 'num' => 2)),
			),
		);
	}

	/**
	 * There are multiple ways to emulate OFFSET before SQL Server 2012.
	 *
	 * @covers  PDO::exec
	 *
	 * @dataProvider    provider_offset_replacements
	 *
	 * @param string    $statement
	 * @param array     $expected   Data set
	 */
	public function test_offset_replacements($statement, $expected)
	{
		$db = Database::factory();
		$result = $db->execute_query($statement);

		$this->assertEquals($expected, $result->as_array());
	}

	/**
	 * A common table expression cannot be used inside a subquery.
	 *
	 * @covers  PDO::exec
	 */
	public function test_cte_invalid_as_subquery()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$this->setExpectedException('Database_Exception', 'syntax', '42000');

		$db->execute_query(
			'SELECT * FROM '.$table.' WHERE value IN (WITH cte AS ('
			.'SELECT TOP 2 value, ROW_NUMBER() OVER(ORDER BY id) AS num'
			.' FROM '.$table.') SELECT value FROM cte WHERE num > 1)'
		);
	}
}
