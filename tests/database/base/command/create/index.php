<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_Command_Create_Index_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('CREATE INDEX "a" ON "pre_b" ()', $db->quote(new Database_Command_Create_Index('a', 'b')));
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c")', $db->quote(new Database_Command_Create_Index('a', 'b', array('c'))));
	}

	public function test_name()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Index('a', 'b');

		$this->assertSame($command, $command->name('c'));
		$this->assertSame('CREATE INDEX "c" ON "pre_b" ()', $db->quote($command));
	}

	public function test_unique()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Index('a', 'b');

		$this->assertSame($command, $command->unique(), 'Chainable (void)');
		$this->assertSame('CREATE UNIQUE INDEX "a" ON "pre_b" ()', $db->quote($command));

		$this->assertSame($command, $command->unique(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ()', $db->quote($command));

		$this->assertSame($command, $command->unique(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE UNIQUE INDEX "a" ON "pre_b" ()', $db->quote($command));
	}

	public function test_on()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Index('a', 'b');

		$this->assertSame($command, $command->on('c'));
		$this->assertSame('CREATE INDEX "a" ON "pre_c" ()', $db->quote($command));
	}

	public function test_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Index('a', 'b');

		$this->assertSame($command, $command->column('c'), 'Chainable (column)');
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c")', $db->quote($command));

		$this->assertSame($command, $command->column('d', 'asc'), 'Chainable (column, direction)');
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c", "d" ASC)', $db->quote($command));
	}

	public function test_columns()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_Index('a', 'b');

		$this->assertSame($command, $command->columns(array('c')));
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c")', $db->quote($command));
	}
}
