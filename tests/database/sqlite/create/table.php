<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_Create_Table_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table('a');

		$command = new Database_SQLite_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame('CREATE TABLE '.$table.' ()', $db->quote($command));
	}

	public function test_if_not_exists()
	{
		$db = $this->sharedFixture;
		$command = new Database_SQLite_Create_Table('a');
		$command->parameters[':columns'] = array();
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->if_not_exists(), 'Chainable (void)');
		$this->assertSame('CREATE TABLE IF NOT EXISTS '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE TABLE '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TABLE IF NOT EXISTS '.$table.' ()', $db->quote($command));
	}

	public function test_constraint()
	{
		$db = $this->sharedFixture;
		$command = new Database_SQLite_Create_Table('a');
		$command->parameters[':columns'] = array();
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->constraint(new Database_DDL_Constraint_Primary(array('b'))));
		$this->assertSame('CREATE TABLE '.$table.' (, PRIMARY KEY ("b"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(new Database_DDL_Constraint_Unique(array('c'))));
		$this->assertSame('CREATE TABLE '.$table.' (, PRIMARY KEY ("b"), UNIQUE ("c"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(NULL));
		$this->assertSame('CREATE TABLE '.$table.' ()', $db->quote($command));
	}

	public function test_query()
	{
		$db = $this->sharedFixture;
		$command = new Database_SQLite_Create_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->query(new Database_Query('b')));
		$this->assertSame('CREATE TABLE '.$table.' AS b', $db->quote($command));
	}

	public function test_temporary()
	{
		$db = $this->sharedFixture;
		$command = new Database_SQLite_Create_Table('a');
		$command->parameters[':columns'] = array();
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->temporary(), 'Chainable (void)');
		$this->assertSame('CREATE TEMPORARY TABLE '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE TABLE '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TEMPORARY TABLE '.$table.' ()', $db->quote($command));
	}
}
