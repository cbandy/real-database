<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_DDL_Create_Table_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLite_DDL_Create_Table::if_not_exists
	 */
	public function test_if_not_exists()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_SQLite_DDL_Create_Table('a');
		$command->parameters[':columns'] = array();
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->if_not_exists(), 'Chainable (void)');
		$this->assertSame('CREATE TABLE IF NOT EXISTS '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE TABLE '.$table.' ()', $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TABLE IF NOT EXISTS '.$table.' ()', $db->quote($command));
	}

	/**
	 * @covers  Database_SQLite_DDL_Create_Table::__toString
	 */
	public function test_toString()
	{
		$command = new Database_SQLite_DDL_Create_Table;
		$command
			->temporary()
			->if_not_exists()
			->name('a');

		$this->assertSame('CREATE TEMPORARY TABLE IF NOT EXISTS :name (:columns)', (string) $command);

		$command->constraint(new SQL_DDL_Constraint_Primary(array('b')));
		$this->assertSame('CREATE TEMPORARY TABLE IF NOT EXISTS :name (:columns, :constraints)', (string) $command);

		$command->query(new Database_Query('c'));
		$this->assertSame('CREATE TEMPORARY TABLE IF NOT EXISTS :name AS :query', (string) $command);
	}
}
