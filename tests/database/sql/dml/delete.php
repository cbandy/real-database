<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_SQL_DML_Delete_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DML_Delete::__construct
	 */
	public function test_constructor()
	{
		$db = new SQL('pre_');

		$this->assertSame('DELETE FROM "pre_"',         $db->quote(new SQL_DML_Delete));
		$this->assertSame('DELETE FROM "pre_a"',        $db->quote(new SQL_DML_Delete('a')));
		$this->assertSame('DELETE FROM "pre_a" AS "b"', $db->quote(new SQL_DML_Delete('a', 'b')));
	}

	/**
	 * @covers  SQL_DML_Delete::from
	 */
	public function test_from()
	{
		$db = new SQL('pre_');
		$command = new SQL_DML_Delete;

		$this->assertSame($command, $command->from('a'), 'Chainable (string)');
		$this->assertSame('DELETE FROM "pre_a"', $db->quote($command));

		$this->assertSame($command, $command->from('b', 'c'), 'Chainable (string, string)');
		$this->assertSame('DELETE FROM "pre_b" AS "c"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Delete::limit
	 */
	public function test_limit()
	{
		$db = new SQL('pre_');
		$command = new SQL_DML_Delete('a');

		$this->assertSame($command, $command->limit(5), 'Chainable (integer)');
		$this->assertSame('DELETE FROM "pre_a" LIMIT 5', $db->quote($command));

		$this->assertSame($command, $command->limit(NULL), 'Chainable (NULL)');
		$this->assertSame('DELETE FROM "pre_a"', $db->quote($command));

		$this->assertSame($command, $command->limit(0), 'Chainable (zero)');
		$this->assertSame('DELETE FROM "pre_a" LIMIT 0', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Delete::using
	 */
	public function test_using()
	{
		$db = new SQL('pre_');
		$command = new SQL_DML_Delete('a');

		$this->assertSame($command, $command->using('b'), 'Chainable (string)');
		$this->assertSame('DELETE FROM "pre_a" USING "pre_b"', $db->quote($command));

		$this->assertSame($command, $command->using('c', 'd'), 'Chainable (string, string)');
		$this->assertSame('DELETE FROM "pre_a" USING "pre_c" AS "d"', $db->quote($command));

		$from = new SQL_Table_Reference('e', 'f');
		$from->join('g');

		$this->assertSame($command, $command->using($from), 'Chainable (SQL_Table_Reference)');
		$this->assertSame('DELETE FROM "pre_a" USING "pre_e" AS "f" JOIN "pre_g"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Delete::where
	 */
	public function test_where()
	{
		$db = new SQL('pre_');
		$command = new SQL_DML_Delete('a');

		$this->assertSame($command, $command->where(new SQL_Conditions(new SQL_Column('b.c'), '=', 0)), 'Chainable (SQL_Conditions)');
		$this->assertSame('DELETE FROM "pre_a" WHERE "pre_b"."c" = 0', $db->quote($command));

		$this->assertSame($command, $command->where('d.e', '=', 1), 'Chainable (string, string, integer)');
		$this->assertSame('DELETE FROM "pre_a" WHERE "pre_d"."e" = 1', $db->quote($command));

		$conditions = new SQL_Conditions;
		$conditions->open(NULL)->add(NULL, new SQL_Column('f.g'), '=', 2)->close();

		$this->assertSame($command, $command->where($conditions, '=', TRUE), 'Chainable (SQL_Conditions, string, boolean)');
		$this->assertSame('DELETE FROM "pre_a" WHERE ("pre_f"."g" = 2) = \'1\'', $db->quote($command));
	}

	public function provider_returning()
	{
		return array
		(
			array(NULL, 'DELETE FROM ""'),

			array(
				array('a'),
				'DELETE FROM "" RETURNING "a"',
			),
			array(
				array('a', 'b'),
				'DELETE FROM "" RETURNING "a", "b"',
			),
			array(
				array('a' => 'b'),
				'DELETE FROM "" RETURNING "b" AS "a"',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				'DELETE FROM "" RETURNING "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Column('a')),
				'DELETE FROM "" RETURNING "a"',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'DELETE FROM "" RETURNING "a", "b"',
			),
			array(
				array('a' => new SQL_Column('b')),
				'DELETE FROM "" RETURNING "b" AS "a"',
			),
			array(
				array('a' => new SQL_Column('b'), 'c' => new SQL_Column('d')),
				'DELETE FROM "" RETURNING "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Expression('a')),
				'DELETE FROM "" RETURNING a',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'DELETE FROM "" RETURNING a, b',
			),
			array(
				array('a' => new SQL_Expression('b')),
				'DELETE FROM "" RETURNING b AS "a"',
			),
			array(
				array('a' => new SQL_Expression('b'), 'c' => new SQL_Expression('d')),
				'DELETE FROM "" RETURNING b AS "a", d AS "c"',
			),
		);
	}

	/**
	 * @covers  SQL_DML_Delete::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_returning($value, $expected)
	{
		$db = new SQL;
		$statement = new SQL_DML_Delete;

		$this->assertSame($statement, $statement->returning($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Delete::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_returning_reset($value)
	{
		$db = new SQL;
		$statement = new SQL_DML_Delete;
		$statement->returning($value);

		$statement->returning(NULL);

		$this->assertSame('DELETE FROM ""', $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Delete::__toString
	 */
	public function test_toString()
	{
		$statement = new SQL_DML_Delete;
		$statement
			->from('a')
			->using('b')
			->where('c', '=', 'd')
			->limit(1)
			->returning(array('e'));

		$this->assertSame(
			'DELETE FROM :table USING :using WHERE :where LIMIT :limit RETURNING :returning',
			(string) $statement
		);
	}
}
