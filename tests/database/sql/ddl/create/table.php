<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Create_Table_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'CREATE TABLE "pre_" ()'),
			array(array('a'), 'CREATE TABLE "pre_a" ()'),
		);
	}

	/**
	 * @covers  SQL_DDL_Create_Table::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = new SQL('pre_');

		$class = new ReflectionClass('SQL_DDL_Create_Table');
		$statement = $class->newInstanceArgs($arguments);
		$statement->parameters[':columns'] = array();

		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Create_Table::name
	 */
	public function test_name()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Create_Table;
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->name('a'), 'Chainable');
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Table::column
	 */
	public function test_column()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Create_Table('a');

		$this->assertSame($command, $command->column(new SQL_DDL_Column('b', 'c')));
		$this->assertSame('CREATE TABLE "pre_a" ("b" c)', $db->quote($command));

		$this->assertSame($command, $command->column(new SQL_DDL_Column('d', 'e')));
		$this->assertSame('CREATE TABLE "pre_a" ("b" c, "d" e)', $db->quote($command));

		$this->assertSame($command, $command->column(NULL));
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Table::constraint
	 */
	public function test_constraint()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->constraint(new SQL_DDL_Constraint_Primary(array('b'))));
		$this->assertSame('CREATE TABLE "pre_a" (, PRIMARY KEY ("b"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(new SQL_DDL_Constraint_Unique(array('c'))));
		$this->assertSame('CREATE TABLE "pre_a" (, PRIMARY KEY ("b"), UNIQUE ("c"))', $db->quote($command));

		$this->assertSame($command, $command->constraint(NULL));
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Table::query
	 */
	public function test_query()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Create_Table('a');

		$this->assertSame($command, $command->query(new Database_Query('b')));
		$this->assertSame('CREATE TABLE "pre_a" AS (b)', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Table::temporary
	 */
	public function test_temporary()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Create_Table('a');
		$command->parameters[':columns'] = array();

		$this->assertSame($command, $command->temporary(), 'Chainable (void)');
		$this->assertSame('CREATE TEMPORARY TABLE "pre_a" ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE TABLE "pre_a" ()', $db->quote($command));

		$this->assertSame($command, $command->temporary(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TEMPORARY TABLE "pre_a" ()', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Table::__toString
	 */
	public function test_toString()
	{
		$command = new SQL_DDL_Create_Table;
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
