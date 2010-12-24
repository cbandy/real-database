<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Prepared_Query_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Prepared_Query::as_assoc
	 */
	public function test_as_assoc()
	{
		$db = $this->sharedFixture;
		$query = new Database_Prepared_Query($db, 'a', array());

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
	 * @covers  Database_Prepared_Query::as_object
	 * @dataProvider    provider_as_object
	 *
	 * @param   string|boolean  $as_object  Expected value
	 */
	public function test_as_object($as_object)
	{
		$db = $this->sharedFixture;
		$query = new Database_Prepared_Query($db, 'a', array());

		$this->assertSame($query, $query->as_object($as_object), 'Chainable');
		$this->assertSame($as_object, $query->as_object);
	}

	public function provider_execute()
	{
		return array
		(
			array('a', array(), FALSE, 'a'),
			array('b', array('c'), FALSE, 'b'),
			array('d ?', array('e'), FALSE, "d 'e'"),

			array('f', array(), TRUE, 'f'),
			array('g', array('h'), TRUE, 'g'),
			array('i ?', array('j'), TRUE, "i 'j'"),
		);
	}

	/**
	 * @covers  Database_Prepared_Query::execute
	 * @dataProvider    provider_execute
	 *
	 * @param   mixed           $statement  SQL
	 * @param   array           $parameters Unquoted parameters
	 * @param   string|boolean  $as_object  Row result class
	 * @param   string          $expected   Expected SQL
	 */
	public function test_execute($statement, $parameters, $as_object, $expected)
	{
		$db = $this->getMock('Database_Base_TestSuite_Database', array('execute_query'));
		$db->expects($this->once())
			->method('execute_query')
			->with($this->equalTo($expected), $as_object);

		$query = new Database_Prepared_Query($db, $statement, $parameters);
		$query->as_object($as_object);
		$query->execute($db);
	}
}
