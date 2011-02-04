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
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::column
	 */
	public function test_column()
	{
		$db = Database::factory();
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

	/**
	 * @covers  Database_PostgreSQL_Create_Index::tablespace
	 */
	public function test_tablespace()
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->tablespace('c'));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () TABLESPACE "c"', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::using
	 */
	public function test_using()
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->using('btree'));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' USING btree ()', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::where
	 */
	public function test_where()
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->where(new SQL_Conditions(1)));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () WHERE 1', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::with
	 */
	public function test_with()
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->with(array('FILLFACTOR' => 50)));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () WITH (FILLFACTOR = 50)', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::__toString
	 */
	public function test_toString()
	{
		$command = new Database_PostgreSQL_Create_Index;
		$command
			->unique()
			->using('a')
			->with(array('b' => 'c'))
			->tablespace('d')
			->where(new SQL_Conditions);

		$this->assertSame('CREATE :type INDEX :name ON :table USING a (:columns) WITH (:with) TABLESPACE :tablespace WHERE :where', (string) $command);
	}
}
