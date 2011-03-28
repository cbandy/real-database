<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Insert_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_PostgreSQL_Insert::as_assoc
	 */
	public function test_as_assoc()
	{
		$command = new Database_PostgreSQL_Insert;

		$this->assertSame($command, $command->as_assoc(), 'Chainable');
		$this->assertSame(FALSE, $command->as_object);
	}

	public function provider_as_object()
	{
		return array
		(
			array(FALSE),
			array(TRUE),
			array('a'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::as_object
	 * @dataProvider    provider_as_object
	 *
	 * @param   string|boolean  $as_object  Expected value
	 */
	public function test_as_object($as_object)
	{
		$command = new Database_PostgreSQL_Insert;

		$this->assertSame($command, $command->as_object($as_object), 'Chainable');
		$this->assertSame($as_object, $command->as_object);
	}

	public function provider_identity()
	{
		return array
		(
			array(NULL, NULL, 'INSERT INTO "" DEFAULT VALUES'),
			array(
				'a',
				new SQL_Column('a'),
				'INSERT INTO "" DEFAULT VALUES RETURNING "a"',
			),
			array(
				new SQL_Expression('expr'),
				new SQL_Expression('expr'),
				'INSERT INTO "" DEFAULT VALUES RETURNING expr',
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::identity
	 * @dataProvider    provider_identity
	 *
	 * @param   mixed   $value
	 * @param   mixed   $column
	 * @param   string  $expected
	 */
	public function test_identity($value, $column, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Insert;

		$this->assertSame($command, $command->identity($value), 'Chainable');
		$this->assertSame($expected, $db->quote($command));
		$this->assertEquals($column, $command->identity);
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::returning
	 */
	public function test_returning()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_PostgreSQL_Insert;

		$this->assertSame($statement, $statement->returning('a'), 'Chainable');
		$this->assertSame(NULL, $statement->identity);
	}
}
