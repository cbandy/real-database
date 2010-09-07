<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Alter_Table_Test extends PHPUnit_Framework_TestCase
{
	public function test_drop_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Alter_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->drop_column('b'), 'Chainable (string)');
		$this->assertSame('ALTER TABLE '.$table.' DROP COLUMN "b"', $db->quote($command));

		$this->assertSame($command, $command->drop_column('c', FALSE), 'Chainable (string, FALSE)');
		$this->assertSame('ALTER TABLE '.$table.' DROP COLUMN "b", DROP COLUMN "c" RESTRICT', $db->quote($command));

		$this->assertSame($command, $command->drop_column('d', TRUE), 'Chainable (string, TRUE)');
		$this->assertSame('ALTER TABLE '.$table.' DROP COLUMN "b", DROP COLUMN "c" RESTRICT, DROP COLUMN "d" CASCADE', $db->quote($command));
	}

	public function test_drop_constraint()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Alter_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->drop_constraint('b', 'c'), 'Chainable (string, string)');
		$this->assertSame('ALTER TABLE '.$table.' DROP CONSTRAINT "c"', $db->quote($command));

		$this->assertSame($command, $command->drop_constraint('d', 'e', FALSE), 'Chainable (string, string, FALSE)');
		$this->assertSame('ALTER TABLE '.$table.' DROP CONSTRAINT "c", DROP CONSTRAINT "e" RESTRICT', $db->quote($command));

		$this->assertSame($command, $command->drop_constraint('f', 'g', TRUE), 'Chainable (string, string, TRUE)');
		$this->assertSame('ALTER TABLE '.$table.' DROP CONSTRAINT "c", DROP CONSTRAINT "e" RESTRICT, DROP CONSTRAINT "g" CASCADE', $db->quote($command));
	}

	public function test_rename_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Alter_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->rename_column('b', 'c'));
		$this->assertSame('ALTER TABLE '.$table.' RENAME "b" TO "c"', $db->quote($command));
	}

	public function test_set_not_null()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Alter_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->set_not_null('b'), 'Chainable (string)');
		$this->assertSame('ALTER TABLE '.$table.' SET NOT NULL "b"', $db->quote($command));

		$this->assertSame($command, $command->set_not_null('c', FALSE), 'Chainable (string, FALSE)');
		$this->assertSame('ALTER TABLE '.$table.' SET NOT NULL "b", DROP NOT NULL "c"', $db->quote($command));

		$this->assertSame($command, $command->set_not_null('d', TRUE), 'Chainable (string, TRUE)');
		$this->assertSame('ALTER TABLE '.$table.' SET NOT NULL "b", DROP NOT NULL "c", SET NOT NULL "d"', $db->quote($command));
	}

	public function test_type()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Alter_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->type('b', 'c'), 'Chainable (string, string)');
		$this->assertSame('ALTER TABLE '.$table.' ALTER "b" TYPE c', $db->quote($command));

		$this->assertSame($command, $command->type('d', 'e', 'f'), 'Chainable (string, string, string)');
		$this->assertSame('ALTER TABLE '.$table.' ALTER "b" TYPE c, ALTER "d" TYPE e USING f', $db->quote($command));
	}
}
