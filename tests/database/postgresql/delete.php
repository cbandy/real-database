<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Delete_Test extends PHPUnit_Framework_TestCase
{
	public function provider_limit()
	{
		return array
		(
			array(NULL, 'DELETE FROM ""'),
			array(0, 'DELETE FROM "" WHERE ctid IN (SELECT ctid FROM "" LIMIT 0)'),
			array(1, 'DELETE FROM "" WHERE ctid IN (SELECT ctid FROM "" LIMIT 1)'),
			array(5, 'DELETE FROM "" WHERE ctid IN (SELECT ctid FROM "" LIMIT 5)'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Delete::limit
	 * @dataProvider    provider_limit
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_limit($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Delete;

		$this->assertSame($command, $command->limit($value), 'Chainable');
		$this->assertSame($expected, $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Delete::limit
	 * @dataProvider    provider_limit
	 *
	 * @param   mixed   $value
	 */
	public function test_limit_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Delete;
		$command->limit($value);

		$command->limit(NULL);

		$this->assertSame('DELETE FROM ""', $db->quote($command));
	}

	public function provider_limit_using()
	{
		return array
		(
			array(0, 'a'),
			array(0, array('a')),

			array(1, 'a'),
			array(1, array('a')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Delete::limit
	 * @dataProvider    provider_limit_using
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $using
	 */
	public function test_limit_using($limit, $using)
	{
		$command = new Database_PostgreSQL_Delete;
		$command->using($using);

		$this->setExpectedException('Kohana_Exception');

		$command->limit($limit);
	}

	public function provider_limit_using_reset()
	{
		return array
		(
			array(NULL),

			array(''),
			array('a'),

			array(array()),
			array(array('a')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Delete::limit
	 * @dataProvider    provider_limit_using_reset
	 *
	 * @param   mixed   $using
	 */
	public function test_limit_using_reset($using)
	{
		$command = new Database_PostgreSQL_Delete;
		$command->using($using);

		$this->assertSame($command, $command->limit(NULL));
	}

	/**
	 * @covers  Database_PostgreSQL_Delete::using
	 * @dataProvider    provider_limit_using
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $using
	 */
	public function test_using_limit($limit, $using)
	{
		$command = new Database_PostgreSQL_Delete;
		$command->limit($limit);

		$this->setExpectedException('Kohana_Exception');

		$command->using($using);
	}

	public function provider_using_limit_reset()
	{
		return array
		(
			array(NULL, NULL),

			array(0, NULL),
			array(0, ''),
			array(0, array()),

			array(1, NULL),
			array(1, ''),
			array(1, array()),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Delete::using
	 * @dataProvider    provider_using_limit_reset
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $using
	 */
	public function test_using_limit_reset($limit, $using)
	{
		$command = new Database_PostgreSQL_Delete;
		$command->limit($limit);

		$this->assertSame($command, $command->using($using));
	}

	/**
	 * @covers  Database_PostgreSQL_Delete::__toString
	 */
	public function test_toString()
	{
		$command = new Database_PostgreSQL_Delete;

		$this->assertSame('DELETE FROM :table', (string) $command);

		$command
			->where(new SQL_Conditions)
			->limit(1)
			->returning(array('a'));

		$this->assertSame(
			'DELETE FROM :table WHERE ctid IN (SELECT ctid FROM :table WHERE :where LIMIT :limit) RETURNING :returning',
			(string) $command
		);
	}
}
