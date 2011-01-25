<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Create_Table_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table('a');

		$this->assertSame("CREATE TABLE $table", $db->quote(new Database_MySQL_Create_Table('a')));
	}

	public function test_if_not_exists()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->if_not_exists(), 'Chainable (void)');
		$this->assertSame("CREATE TABLE IF NOT EXISTS $table", $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(FALSE), 'Chainable (FALSE)');
		$this->assertSame("CREATE TABLE $table", $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(TRUE), 'Chainable (TRUE)');
		$this->assertSame("CREATE TABLE IF NOT EXISTS $table", $db->quote($command));
	}

	public function test_like()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');
		$like = $db->quote_table('b');

		$this->assertSame($command, $command->like('b'));
		$this->assertSame("CREATE TABLE $table LIKE $like", $db->quote($command));
	}

	public function test_options()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->options(array('ENGINE' => 'InnoDB', 'AUTO_INCREMENT' => 5)));
		$this->assertSame("CREATE TABLE $table ENGINE 'InnoDB', AUTO_INCREMENT 5", $db->quote($command));
	}

	public function test_query()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->query(new Database_Query('b')));
		$this->assertSame("CREATE TABLE $table AS b", $db->quote($command));
	}

	public function test_query_columns()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Table('a');
		$command->column(new SQL_DDL_Column('b', 'c'));
		$command->query(new Database_Query('d'));
		$table = $db->quote_table('a');

		$this->assertSame("CREATE TABLE $table (`b` c) AS d", $db->quote($command));
	}

	public function test_query_columns_constraints()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Table('a');
		$command->column(new SQL_DDL_Column('b', 'c'));
		$command->constraint(new SQL_DDL_Constraint_Unique(array('d')));
		$command->query(new Database_Query('e'));
		$table = $db->quote_table('a');

		$this->assertSame("CREATE TABLE $table (`b` c, UNIQUE (`d`)) AS e", $db->quote($command));
	}

	public function test_temporary()
	{
		$db = $this->sharedFixture;
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->temporary(), 'Chainable (void)');
		$this->assertSame("CREATE TEMPORARY TABLE $table", $db->quote($command));

		$this->assertSame($command, $command->temporary(FALSE), 'Chainable (FALSE)');
		$this->assertSame("CREATE TABLE $table", $db->quote($command));

		$this->assertSame($command, $command->temporary(TRUE), 'Chainable (TRUE)');
		$this->assertSame("CREATE TEMPORARY TABLE $table", $db->quote($command));
	}
}
