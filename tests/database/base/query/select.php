<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Select_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Query_Select::as_assoc
	 */
	public function test_as_assoc()
	{
		$query = new Database_Query_Select;

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
	 * @covers  Database_Query_Select::as_object
	 * @dataProvider    provider_as_object
	 *
	 * @param   string|boolean  $as_object  Expected value
	 */
	public function test_as_object($as_object)
	{
		$query = new Database_Query_Select;

		$this->assertSame($query, $query->as_object($as_object), 'Chainable');
		$this->assertSame($as_object, $query->as_object);
	}

	/**
	 * @covers  Database_Query_Select::execute
	 */
	public function test_execute()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new Database_Query_Select;

		$db->expects($this->once())
			->method('execute_query')
			->with($this->equalTo($query), FALSE);

		$query->execute($db);
	}

	/**
	 * @covers  Database_Query_Select::execute
	 * @dataProvider    provider_as_object
	 *
	 * @param   string|boolean  $as_object  Expected value
	 */
	public function test_execute_as_object($as_object)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new Database_Query_Select;
		$query->as_object($as_object);

		$db->expects($this->once())
			->method('execute_query')
			->with($this->equalTo($query), $as_object);

		$query->execute($db);
	}
}
