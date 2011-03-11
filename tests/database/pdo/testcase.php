<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * @package     RealDatabase
 * @subpackage  PDO
 * @author      Chris Bandy
 */
abstract class Database_PDO_TestCase extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * Return arguments to create a PDO connection based on a [Database_PDO]
	 * configuration array.
	 *
	 * @param   array   $array  Kohana Database_PDO configuration
	 * @return  array   Array of arguments to PDO::__construct()
	 */
	public static function configuration($array)
	{
		$result = array(
			'username'  => Arr::get($array['connection'], 'username'),
			'password'  => Arr::get($array['connection'], 'password'),
			'options'   => Arr::get($array['connection'], 'options', array()),
			'schema'    => '',
		);

		if ( ! empty($array['connection']['uri']))
		{
			$result['dsn'] = 'uri:'.$array['connection']['uri'];
		}
		else
		{
			$result['dsn'] = $array['connection']['dsn'];
		}

		return $result;
	}

	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');

		if ( ! Database::factory() instanceof Database_PDO)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PDO');

		// It would be nice if there was a static way to detect which drivers
		// PHPUnit can handle.
	}

	protected function getConnection()
	{
		// Load the configuration
		$config = Kohana::config('database.default');

		// Convert the configuration to PDO arguments
		$config = self::configuration($config);

		return $this->createDefaultDBConnection(
			new PDO(
				$config['dsn'],
				$config['username'],
				$config['password'],
				$config['options']
			),
			$config['schema']
		);
	}
}
