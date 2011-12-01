<?php
/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DDL_Enum_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'ENUM ()'),
			array(array(array()), 'ENUM (:values)'),

			array(array(array('a')), "ENUM ('a')"),
			array(array(array('a', 'b')), "ENUM ('a', 'b')"),
		);
	}

	/**
	 * @covers  Database_MySQL_DDL_Enum
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

		$class = new ReflectionClass('Database_MySQL_DDL_Enum');
		$enum = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($enum));
	}

	public function provider_value()
	{
		return array(
			array(NULL, 'ENUM ()'),
			array('a', "ENUM ('a')"),
			array('b', "ENUM ('b')"),
		);
	}

	/**
	 * @covers  Database_MySQL_DDL_Enum::value
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

		$enum = new Database_MySQL_DDL_Enum;

		$this->assertSame($enum, $enum->value($value), 'Chainable');
		$this->assertSame($expected, $db->quote($enum));
	}

	/**
	 * @covers  Database_MySQL_DDL_Enum::value
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

		$enum = new Database_MySQL_DDL_Enum;
		$enum->value($value);

		$enum->value(NULL);

		$this->assertSame('ENUM ()', $db->quote($enum));
	}


	public function provider_values()
	{
		return array(
			array(NULL, 'ENUM ()'),
			array(array(), 'ENUM ()'),

			array(array('a'), "ENUM ('a')"),
			array(array('b', 'c'), "ENUM ('b', 'c')"),
		);
	}

	/**
	 * @covers  Database_MySQL_DDL_Enum::values
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

		$enum = new Database_MySQL_DDL_Enum;

		$this->assertSame($enum, $enum->values($values), 'Chainable');
		$this->assertSame($expected, $db->quote($enum));
	}

	/**
	 * @covers  Database_MySQL_DDL_Enum::values
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

		$enum = new Database_MySQL_DDL_Enum;
		$enum->values($values);

		$enum->values(NULL);

		$this->assertSame('ENUM ()', $db->quote($enum));
	}
}
