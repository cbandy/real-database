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

	/**
	 * @covers  Database_Query::execute
	 */
	public function test_execute()
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_query'));
		$db->expects($this->once())
			->method('execute_query')
			->with($this->equalTo('a'), FALSE);

		$query = new Database_Query('a');
		$query->execute($db);
	}

	public function provider_prepare()
	{
		return array
		(
			array('a', array()),
			array('b', array('c')),
			array('d ?', array('e')),
		);
	}

	/**
	 * @covers  Database_Query::prepare
	 * @dataProvider    provider_prepare
	 *
	 * @param   string  $sql        Expected SQL
	 * @param   array   $parameters Expected parameters
	 */
	public function test_prepare($sql, $parameters)
	{
		$db = $this->sharedFixture;

		$query = new Database_Query($sql, $parameters);
		$result = $query->prepare($db);

		$this->assertType('Database_Prepared_Query', $result);
		$this->assertSame($parameters, $result->parameters);
		$this->assertSame($sql, (string) $result);
	}
}
