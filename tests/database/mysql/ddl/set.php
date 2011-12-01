<?php
/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DDL_Set_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'SET ()'),
			array(array(array()), 'SET (:values)'),

			array(array(array('a')), "SET ('a')"),
			array(array(array('a', 'b')), "SET ('a', 'b')"),
		);
	}

	/**
	 * @covers  Database_MySQL_DDL_Set
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));

		$class = new ReflectionClass('Database_MySQL_DDL_Set');
		$set = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($set));
	}

	public function provider_value()
	{
		return array(
			array(NULL, 'SET ()'),
			array('a', "SET ('a')"),
			array('b', "SET ('b')"),
		);
	}

	/**
	 * @covers  Database_MySQL_DDL_Set::value
	 *
	 * @dataProvider    provider_value
	 *
	 * @param   string  $value      Argument to the method
	 * @param   string  $expected
	 */
	public function test_value($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));

		$set = new Database_MySQL_DDL_Set;

		$this->assertSame($set, $set->value($value), 'Chainable');
		$this->assertSame($expected, $db->quote($set));
	}

	/**
	 * @covers  Database_MySQL_DDL_Set::value
	 *
	 * @dataProvider    provider_value
	 *
	 * @param   string  $value      Argument to the method
	 */
	public function test_value_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));

		$set = new Database_MySQL_DDL_Set;
		$set->value($value);

		$set->value(NULL);

		$this->assertSame('SET ()', $db->quote($set));
	}


	public function provider_values()
	{
		return array(
			array(NULL, 'SET ()'),
			array(array(), 'SET ()'),

			array(array('a'), "SET ('a')"),
			array(array('b', 'c'), "SET ('b', 'c')"),
		);
	}

	/**
	 * @covers  Database_MySQL_DDL_Set::values
	 *
	 * @dataProvider    provider_values
	 *
	 * @param   string  $values     Argument to the method
	 * @param   string  $expected
	 */
	public function test_values($values, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));

		$set = new Database_MySQL_DDL_Set;

		$this->assertSame($set, $set->values($values), 'Chainable');
		$this->assertSame($expected, $db->quote($set));
	}

	/**
	 * @covers  Database_MySQL_DDL_Set::values
	 *
	 * @dataProvider    provider_values
	 *
	 * @param   string  $values     Argument to the method
	 */
	public function test_values_reset($values)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));

		$set = new Database_MySQL_DDL_Set;
		$set->values($values);

		$set->values(NULL);

		$this->assertSame('SET ()', $db->quote($set));
	}
}
