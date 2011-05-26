<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Alter_Table_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DDL_Alter_Table::__construct
	 */
	public function test_constructor()
	{
		$db = new SQL('pre_');

		$this->assertSame('ALTER TABLE :name :actions', $db->quote(new SQL_DDL_Alter_Table));

		$command = new SQL_DDL_Alter_Table('a');
		$command->parameters[':actions'] = array();

		$this->assertSame('ALTER TABLE "pre_a" ', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::name
	 */
	public function test_name()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');
		$command->parameters[':actions'] = array();

		$this->assertSame($command, $command->name('b'));
		$this->assertSame('ALTER TABLE "pre_b" ', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::add_column
	 */
	public function test_add_column()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->add_column(new SQL_DDL_Column('b', 'c')));
		$this->assertSame('ALTER TABLE "pre_a" ADD "b" c', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::add_constraint
	 */
	public function test_add_constraint()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->add_constraint(new SQL_DDL_Constraint_Primary(array('b'))));
		$this->assertSame('ALTER TABLE "pre_a" ADD PRIMARY KEY ("b")', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::drop_column
	 */
	public function test_drop_column()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->drop_column('b'));
		$this->assertSame('ALTER TABLE "pre_a" DROP COLUMN "b"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::drop_constraint
	 */
	public function test_drop_constraint()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->drop_constraint('primary', 'b'));
		$this->assertSame('ALTER TABLE "pre_a" DROP CONSTRAINT "b"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::drop_default
	 */
	public function test_drop_default()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->drop_default('b'));
		$this->assertSame('ALTER TABLE "pre_a" ALTER "b" DROP DEFAULT', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::rename
	 */
	public function test_rename()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->rename('b'));
		$this->assertSame('ALTER TABLE "pre_a" RENAME TO "pre_b"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Alter_Table::set_default
	 */
	public function test_set_default()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->set_default('b', 1));
		$this->assertSame('ALTER TABLE "pre_a" ALTER "b" SET DEFAULT 1', $db->quote($command));
	}
}
