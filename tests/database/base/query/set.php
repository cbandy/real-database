<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Set_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Query_Set::as_assoc
	 */
	public function test_as_assoc()
	{
		$query = new Database_Query_Set;

		$this->assertSame($query, $query->as_assoc(), 'Chainable');
		$this->assertSame(FALSE, $query->as_object);
	}

	public function provider_as_object()
	{
		return array
		(
			array(array(FALSE), FALSE, array()),
			array(array(TRUE), TRUE, array()),
			array(array('b'), 'b', array()),
			array(array('b', array('c')), 'b', array('c')),
		);
	}

	/**
	 * @covers  Database_Query_Set::as_object
	 *
	 * @dataProvider    provider_as_object
	 *
	 * @param   array           $arguments  Arguments to the method
	 * @param   string|boolean  $as_object  Expected $as_object value
	 * @param   array           $expected   Expected $arguments value
	 */
	public function test_as_object($arguments, $as_object, $expected)
	{
		$query = new Database_Query_Set;

		$this->assertSame($query, call_user_func_array(array($query, 'as_object'), $arguments), 'Chainable');
		$this->assertSame($as_object, $query->as_object);
		$this->assertSame($expected, $query->arguments);
	}
}
