<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
abstract class Database_MySQL_TestCase extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * Return arguments to create a PDO connection based on a MySQL
	 * configuration array.
	 *
	 * @param   array   $array  Kohana Database_MySQL configuration
	 * @return  array   Array of arguments to PDO::__construct()
	 */
	public static function configuration($array)
	{
		$result = array(
			'username' => $array['connection']['username'],
			'password' => $array['connection']['password'],
			'database' => $array['connection']['database'],
			'dsn' => 'mysql:dbname='.$array['connection']['database'],
			'options' => array(),
		);

		if ($array['connection']['hostname'][0] === '/')
		{
			$result['dsn'] .= ';unix_socket='.$array['connection']['hostname'];
		}
		else
		{
			$result['dsn'] .= ';host='.$array['connection']['hostname'];
		}

		if ( ! empty($array['connection']['port']))
		{
			$result['dsn'] .= ';port='.$array['connection']['port'];
		}

		if ( ! empty($array['charset']))
		{
			$result['dsn'] .= ';charset='.$array['charset'];
		}

		if ( ! empty($array['connection']['variables']))
		{
			foreach ($array['connection']['variables'] as $variable => $value)
			{
				$variables[] = "SESSION $variable = $value";
			}

			$result['options'][PDO::MYSQL_ATTR_INIT_COMMAND]
				= 'SET '.implode(', ', $variables);
		}

		return $result;
	}

	public static function setUpBeforeClass()
	{
		// Database_MySQL requirement
		if ( ! extension_loaded('mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('MySQL extension not installed');

		// PHPUnit requirement
		if ( ! extension_loaded('pdo_mysql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO MySQL extension not installed');

		if ( ! Database::factory() instanceof Database_MySQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for MySQL');
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
}
