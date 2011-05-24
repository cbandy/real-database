<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_SQL_DML_Insert_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DML_Insert::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('INSERT INTO "pre_" DEFAULT VALUES',          $db->quote(new SQL_DML_Insert));
		$this->assertSame('INSERT INTO "pre_a" DEFAULT VALUES',         $db->quote(new SQL_DML_Insert('a')));
		$this->assertSame('INSERT INTO "pre_a" ("b") DEFAULT VALUES',   $db->quote(new SQL_DML_Insert('a', array('b'))));
	}

	/**
	 * @covers  SQL_DML_Insert::into
	 */
	public function test_into()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Insert;

		$this->assertSame($command, $command->into('a'), 'Chainable (string)');
		$this->assertSame('INSERT INTO "pre_a" DEFAULT VALUES', $db->quote($command));
	}

	public function provider_columns()
	{
		return array(
			array(NULL, 'INSERT INTO "" DEFAULT VALUES'),

			array(
				array('a'),
				'INSERT INTO "" ("a") DEFAULT VALUES',
			),
			array(
				array('a', 'b'),
				'INSERT INTO "" ("a", "b") DEFAULT VALUES',
			),

			array(
				array(new SQL_Column('a')),
				'INSERT INTO "" ("a") DEFAULT VALUES',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'INSERT INTO "" ("a", "b") DEFAULT VALUES',
			),

			array(
				array(new SQL_Expression('a')),
				'INSERT INTO "" (a) DEFAULT VALUES',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'INSERT INTO "" (a, b) DEFAULT VALUES',
			),
		);
	}

	/**
	 * @covers  SQL_DML_Insert::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_columns($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Insert;

		$this->assertSame($statement, $statement->columns($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Insert::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_columns_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Insert;
		$statement->columns($value);

		$statement->columns(NULL);

		$this->assertSame('INSERT INTO "" DEFAULT VALUES', $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Insert::values
	 */
	public function test_values()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Insert('a', array('b','c'));

		$this->assertSame($command, $command->values(array(0,1)), 'Chainable (array)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") VALUES (0, 1)', $db->quote($command));

		$this->assertSame($command, $command->values(new SQL_Expression('d')), 'Chainable (SQL_Expression)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") d', $db->quote($command));

		$this->assertSame($command, $command->values(NULL), 'Chainable (NULL)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") DEFAULT VALUES', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Insert::values
	 */
	public function test_values_arrays()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Insert('a', array('b','c'));

		$command->values(array(0,1));

		$this->assertSame($command, $command->values(array(2,3), array(4,5)), 'Chainable (array, array)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") VALUES (0, 1), (2, 3), (4, 5)', $db->quote($command));
	}

	public function provider_returning()
	{
		return array
		(
			array(NULL, 'INSERT INTO "" DEFAULT VALUES'),

			array(
				array('a'),
				'INSERT INTO "" DEFAULT VALUES RETURNING "a"',
			),
			array(
				array('a', 'b'),
				'INSERT INTO "" DEFAULT VALUES RETURNING "a", "b"',
			),
			array(
				array('a' => 'b'),
				'INSERT INTO "" DEFAULT VALUES RETURNING "b" AS "a"',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				'INSERT INTO "" DEFAULT VALUES RETURNING "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Column('a')),
				'INSERT INTO "" DEFAULT VALUES RETURNING "a"',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'INSERT INTO "" DEFAULT VALUES RETURNING "a", "b"',
			),
			array(
				array('a' => new SQL_Column('b')),
				'INSERT INTO "" DEFAULT VALUES RETURNING "b" AS "a"',
			),
			array(
				array('a' => new SQL_Column('b'), 'c' => new SQL_Column('d')),
				'INSERT INTO "" DEFAULT VALUES RETURNING "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Expression('a')),
				'INSERT INTO "" DEFAULT VALUES RETURNING a',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'INSERT INTO "" DEFAULT VALUES RETURNING a, b',
			),
			array(
				array('a' => new SQL_Expression('b')),
				'INSERT INTO "" DEFAULT VALUES RETURNING b AS "a"',
			),
			array(
				array('a' => new SQL_Expression('b'), 'c' => new SQL_Expression('d')),
				'INSERT INTO "" DEFAULT VALUES RETURNING b AS "a", d AS "c"',
			),
		);
	}

	/**
	 * @covers  SQL_DML_Insert::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_returning($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Insert;

		$this->assertSame($statement, $statement->returning($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Insert::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_returning_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Insert;
		$statement->returning($value);

		$statement->returning(NULL);

		$this->assertSame('INSERT INTO "" DEFAULT VALUES', $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Insert::__toString
	 */
	public function test_toString()
	{
		$statement = new SQL_DML_Insert;
		$statement
			->into('a')
			->columns(array('b'))
			->returning(array('c'));

		$this->assertSame('INSERT INTO :table (:columns) DEFAULT VALUES RETURNING :returning', (string) $statement);

		$statement->values(array('d'));

		$this->assertSame('INSERT INTO :table (:columns) VALUES :values RETURNING :returning', (string) $statement);

		$statement->values(new SQL_Expression('e'));

		$this->assertSame('INSERT INTO :table (:columns) :values RETURNING :returning', (string) $statement);
	}
}
