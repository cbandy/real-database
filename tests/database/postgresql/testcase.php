<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
abstract class Database_PostgreSQL_TestCase extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * Return arguments to create a PDO connection based on a
	 * [Database_PostgreSQL] configuration array.
	 *
	 * @param   array   $array  Kohana Database_PostgreSQL configuration
	 * @return  array   Array of arguments to PDO::__construct()
	 */
	public static function configuration($array)
	{
		$result = array(
			'username' => $array['connection']['username'],
			'password' => $array['connection']['password'],
			'database' => $array['connection']['database'],
			'dsn' => 'pgsql:',
			'options' => array(),
		);

		if (isset($array['connection']['info']))
		{
			$result['dsn'] .= $array['connection']['info'];
		}
		else
		{
			$result['dsn'] .= ' '.Database_PostgreSQL::configuration($array);
		}

		return $result;
	}

	public static function setUpBeforeClass()
	{
		// Database_PostgreSQL requirement
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		// PHPUnit requirement
		if ( ! extension_loaded('pdo_pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	protected function getConnection()
	{
		// Load the configuration
		$config = Kohana::$config->load('database.default');

		// Convert the configuration to PDO arguments
		$config = self::configuration($config);

		return $this->createDefaultDBConnection(
			new PDO(
				$config['dsn'],
				$config['username'],
				$config['password'],
				$config['options']
			),
			$config['database']
		);
	}

	protected function getSetUpOperation()
	{
		return new PHPUnit_Extensions_Database_Operation_Composite(array(
			new Database_PostgreSQL_TestCase_Truncate,
			new PHPUnit_Extensions_Database_Operation_Insert,
		));
	}
}


/**
 * TRUNCATE operation that also restarts the sequences owned by the table.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class Database_PostgreSQL_TestCase_Truncate
	implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{
	public function execute(
		PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection,
		PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
	)
	{
		$version = $connection->getConnection()
			->getAttribute(PDO::ATTR_SERVER_VERSION);

		$has_restart_identity = version_compare($version, '8.4', '>=');

		/** @var $table PHPUnit_Extensions_Database_DataSet_ITable */
		foreach ($dataSet->getReverseIterator() as $table)
		{
			$sql = 'TRUNCATE '.$connection->quoteSchemaObject(
				$table->getTableMetaData()->getTableName()
			);

			if ($has_restart_identity)
			{
				$sql .= ' RESTART IDENTITY';
			}
			else
			{
				$table_sequences =
					'SELECT s.relname FROM pg_catalog.pg_class t'
					.' JOIN pg_catalog.pg_depend d'
					."   ON d.refobjid = t.oid AND d.deptype = 'a'"
					.' JOIN pg_catalog.pg_class s'
					."   ON s.oid = d.objid AND s.relkind = 'S'"
					.' WHERE t.relname = '
					.$connection->getConnection()->quote(
						$table->getTableMetaData()->getTableName()
					);

				$table_sequences = $connection->getConnection()
					->query($table_sequences, PDO::FETCH_COLUMN, 0);

				if ($table_sequences)
				{
					foreach ($table_sequences as $sequence)
					{
						$sql .= '; ALTER SEQUENCE '
							.$connection->quoteSchemaObject($sequence)
							.' RESTART';
					}
				}

				unset($table_sequences);
			}

			try
			{
				$connection->getConnection()->exec($sql);
			}
			catch (PDOException $e)
			{
				throw new PHPUnit_Extensions_Database_Operation_Exception(
					'TRUNCATE',
					$sql,
					array(),
					$table,
					$e->getMessage()
				);
			}
		}
	}
}
