<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 */
abstract class Database_Abstract_TestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @return  Database
	 */
	protected function _database()
	{
		return Database::factory();
	}
}
