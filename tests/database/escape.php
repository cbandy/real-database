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

		if ( ! $this->_db instanceof Database_iEscape)
			$this->markTestSkipped('Database instance not Database_iEscape');
	}

	public function tearDown()
	{
		if ( ! $this->_db)
			return;

		$this->_db->disconnect();
	}

	public function test_escape()
	{
		$this->assertNotEquals('asdf', $this->_db->escape('asdf'));
	}

	public function test_quote_literal()
	{
		$this->assertNotEquals('asdf', $this->_db->quote_literal('asdf'));

		$this->assertSame('NULL', $this->_db->quote_literal(NULL));
	}
}
