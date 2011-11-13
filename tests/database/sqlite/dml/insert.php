<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_DML_Insert_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLite_DML_Insert::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$table = $db->quote_table('a');

		$this->assertSame('INSERT INTO '.$table.' DEFAULT VALUES', $db->quote(new Database_SQLite_DML_Insert('a')));
		$this->assertSame('INSERT INTO '.$table.' ("b") DEFAULT VALUES', $db->quote(new Database_SQLite_DML_Insert('a', array('b'))));
	}

	public function provider_values_empty()
	{
		return array(
			array(array(NULL), 'INSERT INTO "a" DEFAULT VALUES'),
			array(array(new SQL_Expression('b')), 'INSERT INTO "a" b'),
			array(array(array('b')), "INSERT INTO \"a\" VALUES ('b')"),
			array(
				array(array('b'), array('c')),
				"INSERT INTO \"a\" SELECT 'b' UNION ALL SELECT 'c'",
			),
		);
	}

	/**
	 * @covers  Database_SQLite_DML_Insert::values
	 *
	 * @dataProvider    provider_values_empty
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $expected
	 */
	public function test_values_empty($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_SQLite_DML_Insert('a');

		$this->assertSame(
			$statement,
			call_user_func_array(array($statement, 'values'), $arguments),
			'Chainable'
		);
		$this->assertSame($expected, $db->quote($statement));
	}

	public function provider_values_not_empty()
	{
		return array(
			array(array(NULL), array(NULL), 'INSERT INTO "a" DEFAULT VALUES'),
			array(
				array(NULL),
				array(new SQL_Expression('b')),
				'INSERT INTO "a" b',
			),
			array(
				array(NULL),
				array(array('b')),
				"INSERT INTO \"a\" VALUES ('b')",
			),
			array(
				array(NULL),
				array(array('b'), array('c')),
				"INSERT INTO \"a\" SELECT 'b' UNION ALL SELECT 'c'",
			),

			array(
				array(new SQL_Expression('b')),
				array(NULL),
				'INSERT INTO "a" DEFAULT VALUES',
			),
			array(
				array(new SQL_Expression('b')),
				array(new SQL_Expression('c')),
				'INSERT INTO "a" c',
			),

			array(
				array(array('b')),
				array(NULL),
				'INSERT INTO "a" DEFAULT VALUES',
			),
			array(
				array(array('b')),
				array(new SQL_Expression('c')),
				'INSERT INTO "a" c',
			),
			array(
				array(array('b')),
				array(array('c')),
				"INSERT INTO \"a\" SELECT 'b' UNION ALL SELECT 'c'",
			),
			array(
				array(array('b')),
				array(array('c'), array('d')),
				"INSERT INTO \"a\" SELECT 'b' UNION ALL SELECT 'c' UNION ALL SELECT 'd'",
			),

			array(
				array(array('b'), array('c')),
				array(NULL),
				'INSERT INTO "a" DEFAULT VALUES',
			),
			array(
				array(array('b'), array('c')),
				array(new SQL_Expression('d')),
				'INSERT INTO "a" d',
			),
			array(
				array(array('b'), array('c')),
				array(array('d')),
				"INSERT INTO \"a\" SELECT 'b' UNION ALL SELECT 'c' UNION ALL SELECT 'd'",
			),
			array(
				array(array('b'), array('c')),
				array(array('d'), array('e')),
				"INSERT INTO \"a\" SELECT 'b' UNION ALL SELECT 'c' UNION ALL SELECT 'd' UNION ALL SELECT 'e'",
			),
		);
	}

	/**
	 * @covers  Database_SQLite_DML_Insert::values
	 *
	 * @dataProvider    provider_values_not_empty
	 *
	 * @param   array   $args1      Arguments to the first call
	 * @param   array   $args2      Arguments to the second call
	 * @param   string  $expected
	 */
	public function test_values_not_empty($args1, $args2, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_SQLite_DML_Insert('a');

		call_user_func_array(array($statement, 'values'), $args1);

		$this->assertSame(
			$statement,
			call_user_func_array(array($statement, 'values'), $args2),
			'Chainable'
		);
		$this->assertSame($expected, $db->quote($statement));
	}

	public function provider_values_invalid()
	{
		return array(
			array(
				array(new SQL_Expression('b')),
				array(array('c')),
			),
			array(
				array(new SQL_Expression('b')),
				array(array('c'), array('d')),
			),
		);
	}

	/**
	 * @covers  Database_SQLite_DML_Insert::values
	 *
	 * @dataProvider    provider_values_invalid
	 *
	 * @param   array   $args1  Arguments to the first call
	 * @param   array   $args2  Arguments to the second call
	 */
	public function test_values_invalid($args1, $args2)
	{
		$statement = new Database_SQLite_DML_Insert;

		call_user_func_array(array($statement, 'values'), $args1);

		$this->setExpectedException('Kohana_Exception');

		call_user_func_array(array($statement, 'values'), $args2);
	}
}
