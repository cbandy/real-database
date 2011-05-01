<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Base_Statement_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(''),    '', array()),
			array(array('a'),   'a', array()),

			array(array('', array()),               '', array()),
			array(array('', array('b')),            '', array('b')),
			array(array('c', array('d')),           'c', array('d')),
			array(array('e', array(1 => 'f')),      'e', array(1 => 'f')),
			array(array('g', array('h' => 2)),      'g', array('h' => 2)),
			array(array('i', array('j' => 'k')),    'i', array('j' => 'k')),
		);
	}

	/**
	 * @covers  Database_Statement::__construct
	 *
	 * @dataProvider  provider_constructor
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $value      Expected value
	 * @param   array   $parameters Expected parameters
	 */
	public function test_constructor($arguments, $value, $parameters)
	{
		$class = new ReflectionClass('Database_Statement');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($value, (string) $statement);
		$this->assertSame($parameters, $statement->parameters());
	}

	public function provider_toString()
	{
		return array(
			array('',   ''),
			array('a',  'a'),
		);
	}

	/**
	 * @covers  Database_Statement::__toString
	 *
	 * @dataProvider  provider_toString
	 *
	 * @param   string  $argument   Argument to the constructor
	 * @param   string  $expected   Expected value
	 */
	public function test_toString($argument, $expected)
	{
		$statement = new Database_Statement($argument);

		$this->assertSame($expected, (string) $statement);
	}

	/**
	 * @covers  Database_Statement::bind
	 */
	public function test_bind()
	{
		$statement = new Database_Statement('');

		$this->assertSame($statement, $statement->bind(0, $var), 'Chainable (integer)');
		$this->assertSame(NULL, $var, 'Variable created');

		$this->assertSame(array(0 => NULL), $statement->parameters());

		$var = 1;
		$this->assertSame(array(0 => 1), $statement->parameters());

		$this->assertSame($statement, $statement->bind(':a', $var), 'Chainable (string)');
		$this->assertSame(1, $var, 'Variable unchanged');

		$this->assertSame(array(0 => 1, ':a' => 1), $statement->parameters());

		$var = 2;
		$this->assertSame(array(0 => 2, ':a' => 2), $statement->parameters());
	}

	/**
	 * @covers  Database_Statement::param
	 */
	public function test_param()
	{
		$statement = new Database_Statement('');

		$this->assertSame($statement, $statement->param(0, NULL), 'Chainable (integer, NULL)');
		$this->assertSame(array(0 => NULL), $statement->parameters());

		$this->assertSame($statement, $statement->param(0, 1), 'Chainable (integer, integer)');
		$this->assertSame(array(0 => 1), $statement->parameters());

		$this->assertSame($statement, $statement->param(':a', NULL), 'Chainable (string, NULL)');
		$this->assertSame(array(0 => 1, ':a' => NULL), $statement->parameters());

		$this->assertSame($statement, $statement->param(':a', 2), 'Chainable (string, integer)');
		$this->assertSame(array(0 => 1, ':a' => 2), $statement->parameters());
	}

	public function provider_parameters_empty()
	{
		return array(
			array(array()),

			array(array(1)),
			array(array('a')),
			array(array(2 => 'b')),
			array(array('c' => 3)),

			array(array(4, 5)),
			array(array('d', 'e')),
			array(array(6 => 'f', 7 => 'g')),
			array(array('h' => 8, 'i' => 9)),
		);
	}

	/**
	 * @covers  Database_Statement::parameters
	 *
	 * @dataProvider    provider_parameters_empty
	 *
	 * @param   array   $values Argument to the method
	 */
	public function test_parameters_empty($values)
	{
		$statement = new Database_Statement('');

		$this->assertSame($statement, $statement->parameters($values), 'Chainable');
		$this->assertSame($values, $statement->parameters());
	}

	public function provider_parameters()
	{
		return array(
			array(array(1), array(2), array(2)),
			array(array('a'), array('b'), array('b')),

			array(array(3, 4), array(5), array(5, 4)),
			array(array('c' ,'d'), array('e'), array('e', 'd')),

			array(array(6 => 'f'), array(7 => 'g'), array(7 => 'g', 6 => 'f')),
			array(array('h' => 8), array('i' => 9), array('i' => 9, 'h' => 8)),
		);
	}

	/**
	 * @covers  Database_Statement::parameters
	 *
	 * @dataProvider    provider_parameters
	 *
	 * @param   array   $initial    Values to initialize
	 * @param   array   $values     Argument to the method
	 * @param   array   $expected   Expected values
	 */
	public function test_parameters($initial, $values, $expected)
	{
		$statement = new Database_Statement('');
		$statement->parameters($initial);

		$this->assertSame($statement, $statement->parameters($values), 'Chainable');
		$this->assertSame($expected, $statement->parameters());
	}
}
