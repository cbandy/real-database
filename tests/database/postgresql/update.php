<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Update_Test extends PHPUnit_Framework_TestCase
{
	public function provider_from_limit()
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
	 * @covers  Database_PostgreSQL_Update::from
	 * @dataProvider    provider_from_limit
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $from
	 */
	public function test_from_limit($limit, $from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->limit($limit);

		$this->setExpectedException('Kohana_Exception');

		$command->from($from);
	}

	public function provider_from_limit_reset()
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
	 * @covers  Database_PostgreSQL_Update::from
	 * @dataProvider    provider_from_limit_reset
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $from
	 */
	public function test_from_limit_reset($limit, $from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->limit($limit);

		$this->assertSame($command, $command->from($from));
	}

	public function provider_limit()
	{
		return array
		(
			array(NULL, 'UPDATE "" SET '),
			array(0, 'UPDATE "" SET  WHERE ctid IN (SELECT ctid FROM "" LIMIT 0)'),
			array(1, 'UPDATE "" SET  WHERE ctid IN (SELECT ctid FROM "" LIMIT 1)'),
			array(5, 'UPDATE "" SET  WHERE ctid IN (SELECT ctid FROM "" LIMIT 5)'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_limit
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_limit($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Update;

		$this->assertSame($command, $command->limit($value), 'Chainable');
		$this->assertSame($expected, $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_from_limit
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $from
	 */
	public function test_limit_from($limit, $from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->from($from);

		$this->setExpectedException('Kohana_Exception');

		$command->limit($limit);
	}

	public function provider_limit_from_reset()
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
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_limit_from_reset
	 *
	 * @param   mixed   $from
	 */
	public function test_limit_from_reset($from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->from($from);

		$this->assertSame($command, $command->limit(NULL));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_limit
	 *
	 * @param   mixed   $value
	 */
	public function test_limit_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Update;
		$command->limit($value);

		$command->limit(NULL);

		$this->assertSame('UPDATE "" SET ', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::__toString
	 */
	public function test_toString()
	{
		$command = new Database_PostgreSQL_Update;

		$this->assertSame('UPDATE :table SET :values', (string) $command);

		$command
			->where(new SQL_Conditions)
			->limit(1)
			->returning('a');

		$this->assertSame('UPDATE :table SET :values WHERE ctid IN (SELECT ctid FROM :table WHERE :where LIMIT :limit) RETURNING :returning', (string) $command);
	}
}
