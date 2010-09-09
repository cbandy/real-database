<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Escape_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$db = $this->sharedFixture;

		if ( ! $db instanceof Database_iEscape)
			$this->markTestSkipped('Connection does not implement Database_iEscape');
	}

	public function test_escape()
	{
		$db = $this->sharedFixture;

		$this->assertNotEquals('asdf', $db->escape('asdf'));
	}

	public function test_quote_literal()
	{
		$db = $this->sharedFixture;

		$this->assertNotEquals('asdf', $db->quote_literal('asdf'));

		$this->assertSame('NULL', $db->quote_literal(NULL));
	}
}
