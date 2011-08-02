<?php
/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_Dialect_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'PDO SQLite extension not installed'
			);

		if ( ! Database::factory() instanceof Database_PDO_SQLite)
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Database not configured for SQLite'
			);
	}

	public function provider_union()
	{
		return array(
			array(FALSE, "(SELECT 'a') UNION (SELECT 'b')", 'syntax error'),
			array(FALSE, "(SELECT 'a') UNION SELECT 'b'", 'syntax error'),
			array(
				TRUE,
				"SELECT 'a' UNION SELECT 'b'",
				array(
					array("'a'" => 'a'),
					array("'a'" => 'b'),
				),
			),

			array(TRUE, "SELECT 'a' LIMIT 0", array()),
			array(
				FALSE,
				"SELECT 'a' LIMIT 0 UNION SELECT 'b'",
				'LIMIT clause should come after UNION',
			),
			array(
				TRUE,
				"SELECT * FROM (SELECT 'a' LIMIT 0) UNION SELECT 'b'",
				array(
					array("'a'" => 'b'),
				),
			),
		);
	}

	/**
	 * Compound SELECTs cannot be wrapped in parentheses.
	 *
	 * @link http://www.sqlite.org/lang_select.html#compound
	 *
	 * @covers  PDO::exec
	 *
	 * @dataProvider    provider_union
	 *
	 * @param boolean       $valid
	 * @param string        $statement
	 * @param array|string  $expected   Rows or exception message
	 */
	public function test_union($valid, $statement, $expected)
	{
		$db = Database::factory();

		if ( ! $valid)
		{
			$this->setExpectedException(
				'Database_Exception', $expected, 'HY000'
			);
		}

		$result = $db->execute_query($statement);

		$this->assertType('Database_Result', $result);
		$this->assertSame($expected, $result->as_array());
	}
}
