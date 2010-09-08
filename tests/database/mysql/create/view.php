<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Create_View_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('CREATE VIEW `a` AS b', $db->quote(new Database_MySQL_Create_View('a', new Database_Query('b'))));
	}

	public function test_algorithm()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->algorithm('merge'));
		$this->assertSame('CREATE ALGORITHM = MERGE VIEW `a` AS b', $db->quote($command));
	}

	public function test_check()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->check('cascaded'));
		$this->assertSame('CREATE VIEW `a` AS b WITH CASCADED CHECK OPTION', $db->quote($command));
	}

	public function test_columns()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->columns(array('c')));
		$this->assertSame('CREATE VIEW `a` (`c`) AS b', $db->quote($command));
	}

	public function test_replace()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->replace(), 'Chainable (void)');
		$this->assertSame('CREATE OR REPLACE VIEW `a` AS b', $db->quote($command));

		$this->assertSame($command, $command->replace(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE VIEW `a` AS b', $db->quote($command));

		$this->assertSame($command, $command->replace(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE OR REPLACE VIEW `a` AS b', $db->quote($command));
	}
}
