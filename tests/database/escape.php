<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.escape
 */
class Database_Escape_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;

	public function setUp()
	{
		$this->_db = Database::instance('testing');

		if ( ! $this->_db instanceof Database_Escape)
			$this->markTestSkipped('Database instance not Database_Escape');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_escape()
	{
		$this->assertNotEquals('asdf', $this->_db->escape('asdf'));
	}
}
