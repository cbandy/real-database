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
	/**
	 * @covers  Database_MySQL_Create_Table::if_not_exists
	 */
	public function test_if_not_exists()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(), '`'));
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->if_not_exists(), 'Chainable (void)');
		$this->assertSame("CREATE TABLE IF NOT EXISTS $table", $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(FALSE), 'Chainable (FALSE)');
		$this->assertSame("CREATE TABLE $table", $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(TRUE), 'Chainable (TRUE)');
		$this->assertSame("CREATE TABLE IF NOT EXISTS $table", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_Create_Table::like
	 */
	public function test_like()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(), '`'));
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');
		$like = $db->quote_table('b');

		$this->assertSame($command, $command->like('b'));
		$this->assertSame("CREATE TABLE $table LIKE $like", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_Create_Table::options
	 */
	public function test_options()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(), '`'));
		$command = new Database_MySQL_Create_Table('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->options(array('ENGINE' => 'InnoDB', 'AUTO_INCREMENT' => 5)));
		$this->assertSame("CREATE TABLE $table ENGINE 'InnoDB', AUTO_INCREMENT 5", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_Create_Table::__toString
	 */
	public function test_toString()
	{
		$command = new Database_MySQL_Create_Table;
		$command
			->temporary()
			->if_not_exists()
			->column(new SQL_DDL_Column('a', 'b'))
			->constraint(new SQL_DDL_Constraint_Primary(array('c')))
			->options(array('d' => 'e'))
			->query(new SQL_Expression('f'));

		$this->assertSame('CREATE TEMPORARY TABLE IF NOT EXISTS :name (:columns, :constraints) :options AS :query', (string) $command);

		$command->like('g');

		$this->assertSame('CREATE TEMPORARY TABLE IF NOT EXISTS :name LIKE :like', (string) $command);
	}
}
