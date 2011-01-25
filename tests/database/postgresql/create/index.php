<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Create_Index_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table('b');

		$this->assertSame('CREATE INDEX "a" ON '.$table.' ()', $db->quote(new Database_PostgreSQL_Create_Index('a', 'b')));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' ("c")', $db->quote(new Database_PostgreSQL_Create_Index('a', 'b', array('c'))));
	}

	public function test_column()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->column('c'), 'Chainable (column)');
		$this->assertSame('CREATE INDEX "a" ON '.$table.' ("c")', $db->quote($command));

		$this->assertSame($command, $command->column('d', 'asc'), 'Chainable (column, direction)');
		$this->assertSame('CREATE INDEX "a" ON '.$table.' ("c", "d" ASC)', $db->quote($command));

		$this->assertSame($command, $command->column('e', 'desc', 'first'), 'Chainable (column, direction, position)');
		$this->assertSame('CREATE INDEX "a" ON '.$table.' ("c", "d" ASC, "e" DESC NULLS FIRST)', $db->quote($command));

		$this->assertSame($command, $command->column(new SQL_Expression('f')), 'Chainable (expression)');
		$this->assertSame('CREATE INDEX "a" ON '.$table.' ("c", "d" ASC, "e" DESC NULLS FIRST, (f))', $db->quote($command));
	}

	public function test_tablespace()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->tablespace('c'));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () TABLESPACE "c"', $db->quote($command));
	}

	public function test_unique()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->unique(), 'Chainable (void)');
		$this->assertSame('CREATE UNIQUE INDEX "a" ON '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->unique(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE INDEX "a" ON '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->unique(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE UNIQUE INDEX "a" ON '.$table.' ()', $db->quote($command));
	}

	public function test_using()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->using('btree'));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' USING btree ()', $db->quote($command));
	}

	public function test_where()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->where(new SQL_Conditions(1)));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () WHERE 1', $db->quote($command));
	}

	public function test_with()
	{
		$db = $this->sharedFixture;
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->with(array('FILLFACTOR' => 50)));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () WITH (FILLFACTOR = 50)', $db->quote($command));
	}
}
