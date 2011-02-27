<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Query::as_assoc
	 */
	public function test_as_assoc()
	{
		$query = new Database_Query('a');

		$this->assertSame($query, $query->as_assoc(), 'Chainable');
		$this->assertSame(FALSE, $query->as_object);
	}

	public function provider_as_object()
	{
		return array
		(
			array(FALSE),
			array(TRUE),
			array('b'),
		);
	}

	/**
	 * @covers  Database_Query::as_object
	 * @dataProvider    provider_as_object
	 *
	 * @param   string|boolean  $as_object  Expected value
	 */
	public function test_as_object($as_object)
	{
		$query = new Database_Query('a');

		$this->assertSame($query, $query->as_object($as_object), 'Chainable');
		$this->assertSame($as_object, $query->as_object);
	}
}
