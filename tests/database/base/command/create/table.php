<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_Command_Create_Table_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Command_Create_Table::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('CREATE TABLE :name (:columns)', $db->quote(new Database_Command_Create_Table));

		$command = new Database_Command_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Create_Table::name
	 */
	public function test_name()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Create_Table;
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->name('a'), 'Chainable');
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Create_Table::column
	 */
	public function test_column()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Create_Table('a');

		$this->assertSame($command, $command->column(new SQL_DDL_Column('b', 'c')));
		$this->assertSame('CREATE TABLE "pre_a" ("b" c)', $db->quote($command));

		$this->assertSame($command, $command->column(new SQL_DDL_Column('d', 'e')));
		$this->assertSame('CREATE TABLE "pre_a" ("b" c, "d" e)', $db->quote($command));

		$this->assertSame($command, $command->column(NULL));
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Create_Table::constraint
	 */
	public function test_constraint()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->constraint(new SQL_DDL_Constraint_Primary(array('b'))));
		$this->assertSame('CREATE TABLE "pre_a" (, PRIMARY KEY ("b"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(new SQL_DDL_Constraint_Unique(array('c'))));
		$this->assertSame('CREATE TABLE "pre_a" (, PRIMARY KEY ("b"), UNIQUE ("c"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(NULL));
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Create_Table::query
	 */
	public function test_query()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Create_Table('a');

		$this->assertSame($command, $command->query(new Database_Query('b')));
		$this->assertSame('CREATE TABLE "pre_a" AS (b)', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Create_Table::temporary
	 */
	public function test_temporary()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->temporary(), 'Chainable (void)');
		$this->assertSame('CREATE TEMPORARY TABLE "pre_a" ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TEMPORARY TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Create_Table::__toString
	 */
	public function test_toString()
	{
		$command = new Database_Command_Create_Table;
		$command
			->temporary()
			->column(new SQL_DDL_Column('a', 'b'));

		$this->assertSame('CREATE TEMPORARY TABLE :name (:columns)', (string) $command);

		$command->constraint(new SQL_DDL_Constraint_Primary(array('c')));

		$this->assertSame('CREATE TEMPORARY TABLE :name (:columns, :constraints)', (string) $command);

		$command->query(new Database_Query('d'));

		$this->assertSame('CREATE TEMPORARY TABLE :name (:columns) AS (:query)', (string) $command);
	}
}
