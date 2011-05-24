<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Drop_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array('a'), 'DROP A '),
			array(array('a', 'b'), 'DROP A "b"'),

			array(array('a', 'b', FALSE), 'DROP A "b" RESTRICT'),
			array(array('a', 'b', TRUE), 'DROP A "b" CASCADE'),
		);
	}

	/**
	 * @covers  SQL_DDL_Drop::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$class = new ReflectionClass('SQL_DDL_Drop');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Drop::cascade
	 */
	public function test_cascade()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new SQL_DDL_Drop('a', 'b');

		$this->assertSame($command, $command->cascade(), 'Chainable (void)');
		$this->assertSame('DROP A "b" CASCADE', $db->quote($command));

		$this->assertSame($command, $command->cascade(FALSE), 'Chainable (FALSE)');
		$this->assertSame('DROP A "b" RESTRICT', $db->quote($command));

		$this->assertSame($command, $command->cascade(TRUE), 'Chainable (TRUE)');
		$this->assertSame('DROP A "b" CASCADE', $db->quote($command));

		$this->assertSame($command, $command->cascade(NULL), 'Chainable (NULL)');
		$this->assertSame('DROP A "b"', $db->quote($command));
	}

	/**
	 * @covers  SQL_DDL_Drop::if_exists
	 */
	public function test_if_exists()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new SQL_DDL_Drop('a', 'b');

		$this->assertSame($command, $command->if_exists(), 'Chainable (void)');
		$this->assertSame('DROP A IF EXISTS "b"', $db->quote($command));

		$this->assertSame($command, $command->if_exists(FALSE), 'Chainable (FALSE)');
		$this->assertSame('DROP A "b"', $db->quote($command));

		$this->assertSame($command, $command->if_exists(TRUE), 'Chainable (TRUE)');
		$this->assertSame('DROP A IF EXISTS "b"', $db->quote($command));
	}

	public function provider_name()
	{
		return array(
			array(NULL, 'DROP X '),
			array('a', 'DROP X "a"'),
			array(new SQL_Identifier('b'), 'DROP X "b"'),
			array(new SQL_Expression('expr'), 'DROP X expr'),
		);
	}

	/**
	 * @covers  SQL_DDL_Drop::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_name($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Drop('x');

		$this->assertSame($statement, $statement->name($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Drop::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_name_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Drop('x');
		$statement->name($value);

		$statement->name(NULL);

		$this->assertSame('DROP X ', $db->quote($statement));
	}

	public function provider_names()
	{
		return array(
			array(NULL, 'DROP X '),

			array(array('a'), 'DROP X "a"'),
			array(array('a', 'b'), 'DROP X "a", "b"'),

			array(
				array(new SQL_Identifier('a')),
				'DROP X "a"',
			),
			array(
				array(new SQL_Identifier('a'), new SQL_Identifier('b')),
				'DROP X "a", "b"',
			),

			array(
				array(new SQL_Expression('a')),
				'DROP X a',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'DROP X a, b',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Drop::names
	 *
	 * @dataProvider    provider_names
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_names($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Drop('x');

		$this->assertSame($statement, $statement->names($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Drop::names
	 *
	 * @dataProvider    provider_names
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_names_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DDL_Drop('x');
		$statement->names($value);

		$statement->names(NULL);

		$this->assertSame('DROP X ', $db->quote($statement));
	}

	/**
	 * @covers  SQL_DDL_Drop::__toString
	 */
	public function test_toString()
	{
		$command = new SQL_DDL_Drop('a');
		$command
			->if_exists()
			->cascade();

		$this->assertSame('DROP A IF EXISTS :names CASCADE', (string) $command);

		$command->cascade(FALSE);
		$this->assertSame('DROP A IF EXISTS :names RESTRICT', (string) $command);
	}
}
