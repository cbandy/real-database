<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_SQL_DML_Update_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DML_Update::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('UPDATE "pre_" SET ',                 $db->quote(new SQL_DML_Update));
		$this->assertSame('UPDATE "pre_a" SET ',                $db->quote(new SQL_DML_Update('a')));
		$this->assertSame('UPDATE "pre_b" AS "c" SET ',         $db->quote(new SQL_DML_Update('b', 'c')));
		$this->assertSame('UPDATE "pre_d" AS "e" SET "f" = 0',  $db->quote(new SQL_DML_Update('d', 'e', array('f' => 0))));
		$this->assertSame('UPDATE "pre_g" SET "h" = 1',         $db->quote(new SQL_DML_Update('g', NULL, array('h' => 1))));
		$this->assertSame('UPDATE "pre_" SET "i" = 2',          $db->quote(new SQL_DML_Update(NULL, NULL, array('i' => 2))));
	}

	/**
	 * @covers  SQL_DML_Update::table
	 */
	public function test_table()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Update;

		$this->assertSame($command, $command->table('a'), 'Chainable (string)');
		$this->assertSame('UPDATE "pre_a" SET ', $db->quote($command));

		$this->assertSame($command, $command->table('b', 'c'), 'Chainable (string, string)');
		$this->assertSame('UPDATE "pre_b" AS "c" SET ', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Update::set
	 */
	public function test_set()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Update('a');

		$this->assertSame($command, $command->set(array('b' => 0, 'c' => 1)), 'Chainable (array)');
		$this->assertSame('UPDATE "pre_a" SET "b" = 0, "c" = 1', $db->quote($command));

		$this->assertSame($command, $command->set(new SQL_Expression('d')), 'Chainable (SQL_Expression)');
		$this->assertSame('UPDATE "pre_a" SET d', $db->quote($command));

		$this->assertSame($command, $command->set(NULL), 'Chainable (NULL)');
		$this->assertSame('UPDATE "pre_a" SET ', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Update::value
	 */
	public function test_value()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Update('a');

		$this->assertSame($command, $command->value('b', 0));
		$this->assertSame('UPDATE "pre_a" SET "b" = 0', $db->quote($command));

		$this->assertSame($command, $command->value('c', 1));
		$this->assertSame('UPDATE "pre_a" SET "b" = 0, "c" = 1', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Update::from
	 */
	public function test_from()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Update('a', 'b', array('c' => 0));

		$this->assertSame($command, $command->from('d'), 'Chainable (string)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 FROM "pre_d"', $db->quote($command));

		$this->assertSame($command, $command->from('e', 'f'), 'Chainable (string, string)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 FROM "pre_e" AS "f"', $db->quote($command));

		$from = new SQL_Table_Reference('g', 'h');
		$from->join('i');

		$this->assertSame($command, $command->from($from), 'Chainable (SQL_Table_Reference)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 FROM "pre_g" AS "h" JOIN "pre_i"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Update::where
	 */
	public function test_where()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Update('a', 'b', array('c' => 0));

		$this->assertSame($command, $command->where(new SQL_Conditions(new SQL_Column('d'), '=', 1)), 'Chainable (SQL_Conditions)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 WHERE "d" = 1', $db->quote($command));

		$this->assertSame($command, $command->where('e', '=', 2), 'Chainable (string, string, integer)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 WHERE "e" = 2', $db->quote($command));

		$conditions = new SQL_Conditions;
		$conditions->open(NULL)->add(NULL, new SQL_Column('f'), '=', 3)->close();

		$this->assertSame($command, $command->where($conditions, '=', TRUE), 'Chainable (SQL_Conditions, string, boolean)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 WHERE ("f" = 3) = \'1\'', $db->quote($command));
	}

	/**
	 * @covers  SQL_DML_Update::limit
	 */
	public function test_limit()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DML_Update('a', 'b', array('c' => 0));

		$this->assertSame($command, $command->limit(5), 'Chainable (integer)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 LIMIT 5', $db->quote($command));

		$this->assertSame($command, $command->limit(NULL), 'Chainable (NULL)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0', $db->quote($command));

		$this->assertSame($command, $command->limit(0), 'Chainable (zero)');
		$this->assertSame('UPDATE "pre_a" AS "b" SET "c" = 0 LIMIT 0', $db->quote($command));
	}

	public function provider_returning()
	{
		return array
		(
			array(NULL, 'UPDATE "" SET '),

			array(
				array('a'),
				'UPDATE "" SET  RETURNING "a"',
			),
			array(
				array('a', 'b'),
				'UPDATE "" SET  RETURNING "a", "b"',
			),
			array(
				array('a' => 'b'),
				'UPDATE "" SET  RETURNING "b" AS "a"',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				'UPDATE "" SET  RETURNING "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Column('a')),
				'UPDATE "" SET  RETURNING "a"',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'UPDATE "" SET  RETURNING "a", "b"',
			),
			array(
				array('a' => new SQL_Column('b')),
				'UPDATE "" SET  RETURNING "b" AS "a"',
			),
			array(
				array('a' => new SQL_Column('b'), 'c' => new SQL_Column('d')),
				'UPDATE "" SET  RETURNING "b" AS "a", "d" AS "c"',
			),

			array(new SQL_Expression('expr'), 'UPDATE "" SET  RETURNING expr'),
		);
	}

	/**
	 * @covers  SQL_DML_Update::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_returning($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Update;

		$this->assertSame($statement, $statement->returning($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Update::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_returning_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Update;
		$statement->returning($value);

		$statement->returning(NULL);

		$this->assertSame('UPDATE "" SET ', $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Update::__toString
	 */
	public function test_toString()
	{
		$statement = new SQL_DML_Update;
		$statement
			->table('a')
			->set(array('b' => 0))
			->from('c')
			->where('d', '=', 1)
			->limit(2)
			->returning(array('e'));

		$this->assertSame('UPDATE :table SET :values FROM :from WHERE :where LIMIT :limit RETURNING :returning', (string) $statement);
	}
}
