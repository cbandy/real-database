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
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO SQLite extension not installed');

		if ( ! Database::factory() instanceof Database_PDO_SQLite)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for SQLite using PDO');
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
			$sql = 'DELETE FROM '.$connection->quoteSchemaObject(
				$table->getTableMetaData()->getTableName()
			);

			$sql .= '; UPDATE sqlite_sequence SET seq = 0 WHERE'
				." name = '".$table->getTableMetaData()->getTableName()."'";

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
		}
	}
}
