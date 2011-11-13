<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_DML_Insert_Test extends PHPUnit_Framework_TestCase
{
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
	 * @covers  Database_PostgreSQL_DML_Insert::identity
	 * @dataProvider    provider_identity
	 *
	 * @param   mixed   $value
	 * @param   mixed   $column
	 * @param   string  $expected
	 */
	public function test_identity($value, $column, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_DML_Insert;

		$this->assertSame($command, $command->identity($value), 'Chainable');
		$this->assertSame($expected, $db->quote($command));
		$this->assertEquals($column, $command->identity);
		$this->assertNull($command->returning);
	}

	/**
	 * @covers  Database_PostgreSQL_DML_Insert::identity
	 *
	 * @dataProvider    provider_identity
	 *
	 * @param   mixed   $value
	 */
	public function test_identity_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_PostgreSQL_DML_Insert;
		$statement->identity($value);

		$this->assertSame($statement, $statement->identity(NULL), 'Chainable');
		$this->assertSame('INSERT INTO "" DEFAULT VALUES', $db->quote($statement));
		$this->assertNull($statement->identity);
		$this->assertNull($statement->returning);
	}

	public function provider_identity_returning()
	{
		return array
		(
			array(NULL, NULL, NULL, NULL, 'INSERT INTO "" DEFAULT VALUES'),
			array(NULL, 'a', NULL, new SQL_Column('a'), 'INSERT INTO "" DEFAULT VALUES RETURNING "a"'),

			array(
				array('a'), NULL,
				array(new SQL_Column('a')), NULL,
				'INSERT INTO "" DEFAULT VALUES RETURNING "a"',
			),

			array(
				array('a'), 'b',
				NULL, new SQL_Column('b'),
				'INSERT INTO "" DEFAULT VALUES RETURNING "b"',
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_DML_Insert::identity
	 *
	 * @dataProvider    provider_identity_returning
	 *
	 * @param   mixed                           $returning
	 * @param   mixed                           $identity
	 * @param   mixed                           $expected_returning
	 * @param   SQL_Expression|SQL_Identifier   $expected_identity
	 * @param   string                          $expected_sql
	 */
	public function test_identity_returning($returning, $identity, $expected_returning, $expected_identity, $expected_sql)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_PostgreSQL_DML_Insert;
		$statement->returning($returning);

		$this->assertSame($statement, $statement->identity($identity), 'Chainable');
		$this->assertSame($expected_sql, $db->quote($statement));
		$this->assertEquals($expected_identity, $statement->identity);
		$this->assertEquals($expected_returning, $statement->returning);
	}

	/**
	 * @covers  Database_PostgreSQL_DML_Insert::returning
	 */
	public function test_returning_identity()
	{
		$statement = new Database_PostgreSQL_DML_Insert;
		$statement->identity('a');

		$this->assertSame($statement, $statement->returning(array('b')), 'Chainable');
		$this->assertNull($statement->identity);
		$this->assertEquals(array(new SQL_Column('b')), $statement->returning);
	}
}
