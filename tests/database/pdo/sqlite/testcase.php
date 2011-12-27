<?php

require_once dirname(dirname(__FILE__)).'/testcase'.EXT;

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 */
abstract class Database_PDO_SQLite_TestCase extends Database_PDO_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlite'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'PDO SQLite extension not installed'
			);

		if ( ! Database::factory() instanceof Database_PDO_SQLite)
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Database not configured for SQLite using PDO'
			);
	}

	protected function getSetUpOperation()
	{
		return new PHPUnit_Extensions_Database_Operation_Composite(array(
			new Database_PDO_SQLite_TestCase_Truncate,
			new PHPUnit_Extensions_Database_Operation_Insert,
		));
	}
}


/**
 * TRUNCATE operation that also restarts the sequences owned by the table.
 *
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @link http://www.sqlite.org/autoinc.html
 */
class Database_PDO_SQLite_TestCase_Truncate
	implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{
	public function execute(
		PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection,
		PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
	)
	{
		/** @var $table PHPUnit_Extensions_Database_DataSet_ITable */
		foreach ($dataSet->getReverseIterator() as $table)
		{
			$name = $table->getTableMetaData()->getTableName();
			$sql = 'DELETE FROM '.$connection->quoteSchemaObject($name);

			try
			{
				$connection->getConnection()->exec($sql);
			}
			catch (PDOException $e)
			{
				throw new PHPUnit_Extensions_Database_Operation_Exception(
					'DELETE',
					$sql,
					array(),
					$table,
					$e->getMessage()
				);
			}

			try
			{
				$connection->getConnection()->exec(
					'UPDATE sqlite_sequence SET seq = 0'
					." WHERE name = '".$name."'"
				);
			}
			catch (PDOException $e)
			{
				// The sqlite_sequence table only exists if the database
				// contains a table with an AUTOINCREMENT PRIMARY KEY
			}
		}
	}
}
