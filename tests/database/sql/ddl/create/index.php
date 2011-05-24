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
	public function provider_constructor()
	{
		return array(
			array(array(), 'CREATE INDEX "" ON "pre_" ()'),
			array(array('a'), 'CREATE INDEX "a" ON "pre_" ()'),
			array(array('a', 'b'), 'CREATE INDEX "a" ON "pre_b" ()'),
			array(array('a', 'b', array('c')), 'CREATE INDEX "a" ON "pre_b" ("c")'),
			array(array('a', 'b', array('c', 'd')), 'CREATE INDEX "a" ON "pre_b" ("c", "d")'),
		);
	}

	/**
	 * @covers  SQL_DDL_Create_Index::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$class = new ReflectionClass('SQL_DDL_Create_Index');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($statement));
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

	public function provider_column()
	{
		return array(
			array(array(NULL), 'CREATE INDEX "" ON "" ()'),
			array(array(NULL, 'any'), 'CREATE INDEX "" ON "" ()'),

			array(
				array('a'),
				'CREATE INDEX "" ON "" ("a")',
			),
			array(
				array('a', 'b'),
				'CREATE INDEX "" ON "" ("a" B)',
			),

			array(
				array(new SQL_Column('c')),
				'CREATE INDEX "" ON "" ("c")',
			),
			array(
				array(new SQL_Column('c'), 'd'),
				'CREATE INDEX "" ON "" ("c" D)',
			),

			array(
				array(new SQL_Expression('expr')),
				'CREATE INDEX "" ON "" (expr)'
			),
			array(
				array(new SQL_Expression('expr'), 'f'),
				'CREATE INDEX "" ON "" (expr F)'
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Create_Index::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_column($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Create_Index;

		$result = call_user_func_array(array($statement, 'column'), $arguments);

		$this->assertSame($statement, $result, 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_column_reset($arguments)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Create_Index;

		call_user_func_array(array($statement, 'column'), $arguments);

		$statement->column(NULL);

		$this->assertSame('CREATE INDEX "" ON "" ()', $db->quote($statement));
	}

	public function provider_columns()
	{
		return array(
			array(NULL, 'CREATE INDEX "" ON "" ()'),

			array(
				array('a'),
				'CREATE INDEX "" ON "" ("a")',
			),
			array(
				array('a', 'b'),
				'CREATE INDEX "" ON "" ("a", "b")',
			),

			array(
				array(new SQL_Column('a')),
				'CREATE INDEX "" ON "" ("a")',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'CREATE INDEX "" ON "" ("a", "b")',
			),

			array(
				array(new SQL_Expression('a')),
				'CREATE INDEX "" ON "" (a)',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'CREATE INDEX "" ON "" (a, b)',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Create_Index::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_columns($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Create_Index;

		$this->assertSame($statement, $statement->columns($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Create_Index::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_columns_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Create_Index;
		$statement->columns($value);

		$statement->columns(NULL);

		$this->assertSame('CREATE INDEX "" ON "" ()', $db->quote($statement));
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
