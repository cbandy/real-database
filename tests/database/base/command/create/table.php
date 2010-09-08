<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_Command_Create_Table_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$command = new Database_Command_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	public function test_name()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->name('b'));
		$this->assertSame('CREATE TABLE "pre_b" ()', $db->quote($command));
	}

	public function test_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Table('a');

		$this->assertSame($command, $command->column(new Database_DDL_Column('b', 'c')));
		$this->assertSame('CREATE TABLE "pre_a" ("b" c)', $db->quote($command));

		$this->assertSame($command, $command->column(new Database_DDL_Column('d', 'e')));
		$this->assertSame('CREATE TABLE "pre_a" ("b" c, "d" e)', $db->quote($command));

		$this->assertSame($command, $command->column(NULL));
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	public function test_constraint()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->constraint(new Database_DDL_Constraint_Primary(array('b'))));
		$this->assertSame('CREATE TABLE "pre_a" (, PRIMARY KEY ("b"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(new Database_DDL_Constraint_Unique(array('c'))));
		$this->assertSame('CREATE TABLE "pre_a" (, PRIMARY KEY ("b"), UNIQUE ("c"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(NULL));
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	public function test_query()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Table('a');

		$this->assertSame($command, $command->query(new Database_Query('b')));
		$this->assertSame('CREATE TABLE "pre_a" AS (b)', $db->quote($command));
	}

	public function test_query_columns()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Table('a');
		$command->column(new Database_DDL_Column('b', 'c'));
		$command->query(new Database_Query('d'));

		$this->assertSame('CREATE TABLE "pre_a" ("b" c) AS (d)', $db->quote($command));
	}

	public function test_temporary()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->temporary(), 'Chainable (void)');
		$this->assertSame('CREATE TEMPORARY TABLE "pre_a" ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TEMPORARY TABLE "pre_a" ()', $db->quote($command));
	}
}
