<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Create_Index_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DDL_Create_Index::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('CREATE INDEX "a" ON "pre_b" ()', $db->quote(new SQL_DDL_Create_Index('a', 'b')));
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c")', $db->quote(new SQL_DDL_Create_Index('a', 'b', array('c'))));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::name
	 */
	public function test_name()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_Index('a', 'b');

		$this->assertSame($command, $command->name('c'));
		$this->assertSame('CREATE INDEX "c" ON "pre_b" ()', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::unique
	 */
	public function test_unique()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_Index('a', 'b');

		$this->assertSame($command, $command->unique(), 'Chainable (void)');
		$this->assertSame('CREATE UNIQUE INDEX "a" ON "pre_b" ()', $db->quote($command));

		$this->assertSame($command, $command->unique(FALSE), 'Chainable (FALSE)');
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ()', $db->quote($command));

		$this->assertSame($command, $command->unique(TRUE), 'Chainable (TRUE)');
		$this->assertSame('CREATE UNIQUE INDEX "a" ON "pre_b" ()', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::on
	 */
	public function test_on()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_Index('a', 'b');

		$this->assertSame($command, $command->on('c'));
		$this->assertSame('CREATE INDEX "a" ON "pre_c" ()', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::column
	 */
	public function test_column()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_Index('a', 'b');

		$this->assertSame($command, $command->column('c'), 'Chainable (column)');
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c")', $db->quote($command));

		$this->assertSame($command, $command->column('d', 'asc'), 'Chainable (column, direction)');
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c", "d" ASC)', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::columns
	 */
	public function test_columns()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new SQL_DDL_Create_Index('a', 'b');

		$this->assertSame($command, $command->columns(array('c')));
		$this->assertSame('CREATE INDEX "a" ON "pre_b" ("c")', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::__toString
	 */
	public function test_toString()
	{
		$command = new SQL_DDL_Create_Index;
		$command->unique();

		$this->assertSame('CREATE :type INDEX :name ON :table (:columns)', (string) $command);
	}
}
