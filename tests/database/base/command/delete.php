<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Command_Delete_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Command_Delete::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('DELETE FROM "pre_"',         $db->quote(new Database_Command_Delete));
		$this->assertSame('DELETE FROM "pre_a"',        $db->quote(new Database_Command_Delete('a')));
		$this->assertSame('DELETE FROM "pre_a" AS "b"', $db->quote(new Database_Command_Delete('a', 'b')));
	}

	/**
	 * @covers  Database_Command_Delete::from
	 */
	public function test_from()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Delete;

		$this->assertSame($command, $command->from('a'), 'Chainable (string)');
		$this->assertSame('DELETE FROM "pre_a"', $db->quote($command));

		$this->assertSame($command, $command->from('b', 'c'), 'Chainable (string, string)');
		$this->assertSame('DELETE FROM "pre_b" AS "c"', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Delete::limit
	 */
	public function test_limit()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Delete('a');

		$this->assertSame($command, $command->limit(5), 'Chainable (integer)');
		$this->assertSame('DELETE FROM "pre_a" LIMIT 5', $db->quote($command));

		$this->assertSame($command, $command->limit(NULL), 'Chainable (NULL)');
		$this->assertSame('DELETE FROM "pre_a"', $db->quote($command));

		$this->assertSame($command, $command->limit(0), 'Chainable (zero)');
		$this->assertSame('DELETE FROM "pre_a" LIMIT 0', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Delete::using
	 */
	public function test_using()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Delete('a');

		$this->assertSame($command, $command->using('b'), 'Chainable (string)');
		$this->assertSame('DELETE FROM "pre_a" USING "pre_b"', $db->quote($command));

		$this->assertSame($command, $command->using('c', 'd'), 'Chainable (string, string)');
		$this->assertSame('DELETE FROM "pre_a" USING "pre_c" AS "d"', $db->quote($command));

		$from = new SQL_From('e', 'f');
		$from->join('g');

		$this->assertSame($command, $command->using($from), 'Chainable (SQL_From)');
		$this->assertSame('DELETE FROM "pre_a" USING "pre_e" AS "f" JOIN "pre_g"', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Delete::where
	 */
	public function test_where()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Delete('a');

		$this->assertSame($command, $command->where(new SQL_Conditions(new SQL_Column('b.c'), '=', 0)), 'Chainable (SQL_Conditions)');
		$this->assertSame('DELETE FROM "pre_a" WHERE "pre_b"."c" = 0', $db->quote($command));

		$this->assertSame($command, $command->where('d.e', '=', 1), 'Chainable (string, string, integer)');
		$this->assertSame('DELETE FROM "pre_a" WHERE "pre_d"."e" = 1', $db->quote($command));

		$conditions = new SQL_Conditions;
		$conditions->open(NULL)->add(NULL, new SQL_Column('f.g'), '=', 2)->close();

		$this->assertSame($command, $command->where($conditions, '=', TRUE), 'Chainable (SQL_Conditions, string, boolean)');
		$this->assertSame('DELETE FROM "pre_a" WHERE ("pre_f"."g" = 2) = \'1\'', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Delete::__toString
	 */
	public function test_toString()
	{
		$command = new Database_Command_Delete;
		$command
			->from('a')
			->using('b')
			->where('c', '=', 'd')
			->limit(1);

		$this->assertSame('DELETE FROM :table USING :using WHERE :where LIMIT :limit', (string) $command);
	}
}
