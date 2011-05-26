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
		$db = new SQL('pre_');

		$class = new ReflectionClass('SQL_DDL_Create_View');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($statement));
	}

	public function provider_column()
	{
		return array(
			array(array(NULL), 'CREATE VIEW "" AS NULL'),

			array(
				array('a'),
				'CREATE VIEW "" ("a") AS NULL',
			),

			array(
				array(new SQL_Column('b')),
				'CREATE VIEW "" ("b") AS NULL',
			),

			array(
				array(new SQL_Expression('expr')),
				'CREATE VIEW "" (expr) AS NULL'
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Create_View::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_column($arguments, $expected)
	{
		$db = new SQL;
		$statement = new SQL_DDL_Create_View;

		$result = call_user_func_array(array($statement, 'column'), $arguments);

		$this->assertSame($statement, $result, 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Create_View::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_column_reset($arguments)
	{
		$db = new SQL;
		$statement = new SQL_DDL_Create_View;

		call_user_func_array(array($statement, 'column'), $arguments);

		$statement->column(NULL);

		$this->assertSame('CREATE VIEW "" AS NULL', $db->quote($statement));
	}

	public function provider_columns()
	{
		return array(
			array(NULL, 'CREATE VIEW "" AS NULL'),

			array(
				array('a'),
				'CREATE VIEW "" ("a") AS NULL',
			),
			array(
				array('a', 'b'),
				'CREATE VIEW "" ("a", "b") AS NULL',
			),

			array(
				array(new SQL_Column('a')),
				'CREATE VIEW "" ("a") AS NULL',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'CREATE VIEW "" ("a", "b") AS NULL',
			),

			array(
				array(new SQL_Expression('a')),
				'CREATE VIEW "" (a) AS NULL',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'CREATE VIEW "" (a, b) AS NULL',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Create_View::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_columns($value, $expected)
	{
		$db = new SQL;
		$statement = new SQL_DDL_Create_View;

		$this->assertSame($statement, $statement->columns($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Create_View::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_columns_reset($value)
	{
		$db = new SQL;
		$statement = new SQL_DDL_Create_View;
		$statement->columns($value);

		$statement->columns(NULL);

		$this->assertSame('CREATE VIEW "" AS NULL', $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Create_View::name
	 */
	public function test_name()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->name('c'));
		$this->assertSame('CREATE VIEW "pre_c" AS b', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::query
	 */
	public function test_query()
	{
		$db = new SQL('pre_');
		$command = new SQL_DDL_Create_View('a', new Database_Query('b'));

		$this->assertSame($command, $command->query(new Database_Query('c')));
		$this->assertSame('CREATE VIEW "pre_a" AS c', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Create_View::replace
	 */
	public function test_replace()
	{
		$db = new SQL('pre_');
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
		$db = new SQL('pre_');
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
