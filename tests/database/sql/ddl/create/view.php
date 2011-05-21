<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Create_View_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'CREATE VIEW "pre_" AS NULL'),
			array(array('a'), 'CREATE VIEW "pre_a" AS NULL'),
			array(array('a', new SQL_Expression('b')), 'CREATE VIEW "pre_a" AS b'),
		);
	}

	/**
	 * @covers  SQL_DDL_Create_View::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$class = new ReflectionClass('SQL_DDL_Create_View');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Create_View::name
	 */
	public function test_name()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->name('c'));
		$this->assertSame('CREATE VIEW "pre_c" AS b', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::query
	 */
	public function test_query()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->query(new Database_Query('c')));
		$this->assertSame('CREATE VIEW "pre_a" AS c', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::column
	 */
	public function test_column()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->column('c'));
		$this->assertSame('CREATE VIEW "pre_a" ("c") AS b', $db->quote($command));

		$this->assertSame($command, $command->column('d'));
		$this->assertSame('CREATE VIEW "pre_a" ("c", "d") AS b', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::columns
	 */
	public function test_columns()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->columns(array('c')));
		$this->assertSame('CREATE VIEW "pre_a" ("c") AS b', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::replace
	 */
	public function test_replace()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->replace(), 'Chainable (void)');
		$this->assertSame('CREATE OR REPLACE VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->replace(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->replace(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE OR REPLACE VIEW "pre_a" AS b', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::temporary
	 */
	public function test_temporary()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->temporary(), 'Chainable (void)');
		$this->assertSame('CREATE TEMPORARY VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->temporary(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE VIEW "pre_a" AS b', $db->quote($command));

		$this->assertSame($command, $command->temporary(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE TEMPORARY VIEW "pre_a" AS b', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::__toString
	 */
	public function test_toString()
	{
		$command = new SQL_DDL_Create_View;
		$command
			->replace()
			->temporary()
			->name('a')
			->columns(array('b'))
			->query(new Database_Query('c'));

		$this->assertSame('CREATE OR REPLACE TEMPORARY VIEW :name (:columns) AS :query', (string) $command);
	}
}
