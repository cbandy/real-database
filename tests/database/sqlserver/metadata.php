<?php

/**
 * DBUnit MetaData implementation for Microsoft SQL Server
 */
class Database_SQLServer_MetaData extends PHPUnit_Extensions_Database_DB_MetaData_InformationSchema
{
	/**
	 * @link http://github.com/sebastianbergmann/dbunit/issues/36
	 */
	protected $truncateCommand = 'TRUNCATE TABLE';

	/**
	 * Loads column info.
	 *
	 * Copied from [PHPUnit_Extensions_Database_DB_MetaData_InformationSchema::loadColumnInfo]
	 *
	 * @param string $tableName
	 */
	protected function loadColumnInfo($tableName)
	{
		$this->columns[$tableName] = array();
		$this->keys[$tableName] = array();

		$columnQuery = "
			SELECT
				COLUMN_NAME
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE
				TABLE_NAME = ? AND
				TABLE_SCHEMA = ?
			ORDER BY ORDINAL_POSITION
		";

		$columnStatement = $this->pdo->prepare($columnQuery);
		$columnStatement->execute(array($tableName, $this->getSchema()));

		while ($columName = $columnStatement->fetchColumn(0))
		{
			$this->columns[$tableName][] = $columName;
		}

		$keyQuery = "
			SELECT
				KCU.COLUMN_NAME
			FROM
				INFORMATION_SCHEMA.TABLE_CONSTRAINTS as TC,
				INFORMATION_SCHEMA.KEY_COLUMN_USAGE as KCU
			WHERE
				TC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME AND
				TC.TABLE_NAME = KCU.TABLE_NAME AND
				TC.TABLE_SCHEMA = KCU.TABLE_SCHEMA AND
				TC.CONSTRAINT_TYPE = 'PRIMARY KEY' AND
				TC.TABLE_NAME = ? AND
				TC.TABLE_SCHEMA = ?
			ORDER BY
				KCU.ORDINAL_POSITION ASC
		";

		$keyStatement = $this->pdo->prepare($keyQuery);
		$keyStatement->execute(array($tableName, $this->getSchema()));

		while ($columName = $keyStatement->fetchColumn(0))
		{
			$this->keys[$tableName][] = $columName;
		}
	}
}

PHPUnit_Extensions_Database_DB_MetaData::registerClassWithDriver(
	'Database_SQLServer_MetaData',
	'sqlsrv'
);
