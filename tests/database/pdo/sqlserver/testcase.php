<?php

require_once dirname(dirname(__FILE__)).'/testcase'.EXT;

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 */
abstract class Database_PDO_SQLServer_TestCase extends Database_PDO_TestCase
{
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
}
