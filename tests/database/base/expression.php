<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_Base_Expression_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(''), '', array()),
			array(array('', array()), '', array()),
			array(array('a'), 'a', array()),
			array(array('b', array('c')), 'b', array('c')),
			array(array('d', array(1 => 'e')), 'd', array(1 => 'e')),
			array(array('f', array('g' => 2)), 'f', array('g' => 2)),
			array(array('h', array('i' => 'j')), 'h', array('i' => 'j')),
		);
	}

	/**
	 * @covers  Database_Expression::__construct
	 * @dataProvider  provider_constructor
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $value      Expected value
	 * @param   array   $parameters Expected parameters
	 */
	public function test_constructor($arguments, $value, $parameters)
	{
		if (count($arguments) === 1)
		{
			$expression = new Database_Expression(reset($arguments));
		}
		elseif (count($arguments) === 2)
		{
			$expression = new Database_Expression(reset($arguments), next($arguments));
		}

		$this->assertSame($value, (string) $expression);
		$this->assertSame($parameters, $expression->parameters);
	}

	public function provider_toString()
	{
		return array(
			array('', ''),
			array('a', 'a'),
			array(NULL, ''),
			array(FALSE, ''),
			array(TRUE, '1'),
			array(0, '0'),
			array(1, '1'),
			array(array(), 'Array'),
		);
	}

	/**
	 * @covers  Database_Expression::__toString
	 * @dataProvider  provider_toString
	 *
	 * @param   mixed   $argument   Argument to the constructor
	 * @param   string  $expected   Expected value
	 */
	public function test_toString($argument, $expected)
	{
		$expression = new Database_Expression($argument);

		$this->assertSame($expected, (string) $expression);
	}

	/**
	 * The __toString method of the internal object is called once for each
	 * call to Database_Expression::__toString.
	 *
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  Database_Expression::__toString
	 */
	public function test_toString_object()
	{
		$object = $this->getMock('stdClass', array('__toString'));
		$object->expects($this->exactly(2))
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$expression = new Database_Expression($object);

		$this->assertSame('object__toString', (string) $expression, 'Once');
		$this->assertSame('object__toString', (string) $expression, 'Twice');
	}

	/**
	 * @covers  Database_Expression::bind
	 */
	public function test_bind()
	{
		$expression = new Database_Expression('');

		$this->assertSame($expression, $expression->bind(0, $var), 'Chainable (integer)');
		$this->assertSame(NULL, $var, 'Variable created');

		$this->assertSame(array(0 => NULL), $expression->parameters);

		$var = 1;
		$this->assertSame(array(0 => 1), $expression->parameters);

		$this->assertSame($expression, $expression->bind(':a', $var), 'Chainable (string)');
		$this->assertSame(1, $var, 'Variable unchanged');

		$this->assertSame(array(0 => 1, ':a' => 1), $expression->parameters);

		$var = 2;
		$this->assertSame(array(0 => 2, ':a' => 2), $expression->parameters);
	}

	/**
	 * @covers  Database_Expression::param
	 */
	public function test_param()
	{
		$expression = new Database_Expression('');

		$this->assertSame($expression, $expression->param(0, NULL), 'Chainable (integer, NULL)');
		$this->assertSame(array(0 => NULL), $expression->parameters);

		$this->assertSame($expression, $expression->param(0, 1), 'Chainable (integer, integer)');
		$this->assertSame(array(0 => 1), $expression->parameters);

		$this->assertSame($expression, $expression->param(':a', NULL), 'Chainable (string, NULL)');
		$this->assertSame(array(0 => 1, ':a' => NULL), $expression->parameters);

		$this->assertSame($expression, $expression->param(':a', 2), 'Chainable (string, integer)');
		$this->assertSame(array(0 => 1, ':a' => 2), $expression->parameters);
	}

	/**
	 * @covers  Database_Expression::parameters
	 */
	public function test_parameters()
	{
		$expression = new Database_Expression('');

		$this->assertSame($expression, $expression->parameters(array()), 'Chainable (empty)');
		$this->assertSame(array(), $expression->parameters);

		$this->assertSame($expression, $expression->parameters(array(1)), 'Chainable (one indexed integer)');
		$this->assertSame(array(1), $expression->parameters);

		$this->assertSame($expression, $expression->parameters(array('a')), 'Chainable (one indexed string)');
		$this->assertSame(array('a'), $expression->parameters);

		$this->assertSame($expression, $expression->parameters(array(2, 'b')), 'Chainable (two indexed)');
		$this->assertSame(array(2, 'b'), $expression->parameters);

		$this->assertSame($expression, $expression->parameters(array('c')), 'Chainable (one indexed string) 2');
		$this->assertSame(array('c', 'b'), $expression->parameters);

		$this->assertSame($expression, $expression->parameters(array(':d' => 3)), 'Chainable (one associative integer)');
		$this->assertSame(array(':d' => 3, 'c', 'b'), $expression->parameters);

		$this->assertSame($expression, $expression->parameters(array(':d' => 'e')), 'Chainable (one associative string)');
		$this->assertSame(array(':d' => 'e', 'c', 'b'), $expression->parameters);
	}
}
