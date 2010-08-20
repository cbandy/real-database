<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Driver_Introspection_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$db = $this->sharedFixture;

		if ( ! $db instanceof Database_iIntrospect)
			$this->markTestSkipped('Connection does not implement Database_iIntrospect');
	}

	public function test_table_columns_no_table()
	{
		$db = $this->sharedFixture;

		$this->assertSame(array(), $db->table_columns('table-does-not-exist'));
	}
}
