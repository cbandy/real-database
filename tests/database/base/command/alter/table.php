<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_Command_Alter_Table_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$command = new Database_Command_Alter_Table('a');
		$command->parameters[':actions'] = array();

		$this->assertSame('ALTER TABLE "pre_a" ', $db->quote($command));
	}

	public function test_name()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');
		$command->parameters[':actions'] = array();

		$this->assertSame($command, $command->name('b'));
		$this->assertSame('ALTER TABLE "pre_b" ', $db->quote($command));
	}

	public function test_add_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');

		$this->assertSame($command, $command->add_column(new Database_DDL_Column('b', 'c')));
		$this->assertSame('ALTER TABLE "pre_a" ADD "b" c', $db->quote($command));
	}

	public function test_add_constraint()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');

		$this->assertSame($command, $command->add_constraint(new Database_DDL_Constraint_Primary(array('b'))));
		$this->assertSame('ALTER TABLE "pre_a" ADD PRIMARY KEY ("b")', $db->quote($command));
	}

	public function test_drop_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');

		$this->assertSame($command, $command->drop_column('b'));
		$this->assertSame('ALTER TABLE "pre_a" DROP COLUMN "b"', $db->quote($command));
	}

	public function test_drop_constraint()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');

		$this->assertSame($command, $command->drop_constraint('primary', 'b'));
		$this->assertSame('ALTER TABLE "pre_a" DROP CONSTRAINT "b"', $db->quote($command));
	}

	public function test_drop_default()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');

		$this->assertSame($command, $command->drop_default('b'));
		$this->assertSame('ALTER TABLE "pre_a" ALTER "b" DROP DEFAULT', $db->quote($command));
	}

	public function test_rename()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');

		$this->assertSame($command, $command->rename('b'));
		$this->assertSame('ALTER TABLE "pre_a" RENAME TO "pre_b"', $db->quote($command));
	}

	public function test_set_default()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Alter_Table('a');

		$this->assertSame($command, $command->set_default('b', 1));
		$this->assertSame('ALTER TABLE "pre_a" ALTER "b" SET DEFAULT 1', $db->quote($command));
	}
}
