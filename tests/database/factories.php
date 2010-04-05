<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.factories
 */
class Database_Factories_Test extends PHPUnit_Framework_TestCase
{
	public function test_conditions()
	{
		$this->assertType('Database_Conditions', Database::conditions());
	}

	public function test_delete()
	{
		$this->assertType('Database_Query_Delete', Database::delete());
	}

	public function test_expression()
	{
		$this->assertType('Database_Expression', Database::expression(''));
	}

	public function test_from()
	{
		$this->assertType('Database_From', Database::from());
	}

	public function test_insert()
	{
		$this->assertType('Database_Query_Insert', Database::insert());
	}

	public function test_select()
	{
		$this->assertType('Database_Query_Select', Database::select());
	}

	public function test_update()
	{
		$this->assertType('Database_Query_Update', Database::update());
	}
}
