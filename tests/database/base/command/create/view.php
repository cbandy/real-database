<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_Command_Create_View_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('CREATE VIEW "pre_a" AS b', $db->quote(new Database_Command_Create_View('a', new Database_Query('b'))));
	}

	public function test_name()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->name('c'));
		$this->assertSame('CREATE VIEW "pre_c" AS b', $db->quote($command));
	}

	public function test_query()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->query(new Database_Query('c')));
		$this->assertSame('CREATE VIEW "pre_a" AS c', $db->quote($command));
	}

	public function test_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->column('c'));
		$this->assertSame('CREATE VIEW "pre_a" ("c") AS b', $db->quote($command));

		$this->assertSame($command, $command->column('d'));
		$this->assertSame('CREATE VIEW "pre_a" ("c", "d") AS b', $db->quote($command));
	}

	public function test_columns()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->columns(array('c')));
		$this->assertSame('CREATE VIEW "pre_a" ("c") AS b', $db->quote($command));
	}

	public function test_replace()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->replace(), 'Chainable (void)');
		$this->assertSame('CREATE OR REPLACE VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->replace(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->replace(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE OR REPLACE VIEW "pre_a" AS b', $db->quote($command));
	}

	public function test_temporary()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->temporary(), 'Chainable (void)');
		$this->assertSame('CREATE TEMPORARY VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->temporary(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->temporary(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TEMPORARY VIEW "pre_a" AS b', $db->quote($command));
	}
}
