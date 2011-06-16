<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Base_Savepoint_Stack_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Savepoint_Stack::count
	 */
	public function test_constructor()
	{
		$stack = new Database_Savepoint_Stack;

		$this->assertSame(0, count($stack));
	}

	/**
	 * @covers  Database_Savepoint_Stack::push
	 */
	public function test_push()
	{
		$stack = new Database_Savepoint_Stack;

		$this->assertSame(1, $stack->push('a'));
		$this->assertSame(2, $stack->push('b'));
		$this->assertSame(3, $stack->push('c'));

		$this->assertSame(3, count($stack));
	}

	/**
	 * @covers  Database_Savepoint_Stack::push
	 */
	public function test_push_duplicate()
	{
		$stack = new Database_Savepoint_Stack;

		$this->assertSame(1, $stack->push('a'));
		$this->assertSame(2, $stack->push('b'));
		$this->assertSame(2, $stack->push('a'));

		$this->assertSame(2, count($stack));
	}

	/**
	 * @covers  Database_Savepoint_Stack::pop
	 */
	public function test_pop()
	{
		$stack = new Database_Savepoint_Stack;
		$stack->push('a');
		$stack->push('b');
		$stack->push('c');

		$this->assertSame('c', $stack->pop());
		$this->assertSame('b', $stack->pop());
		$this->assertSame('a', $stack->pop());

		$this->assertSame(0, count($stack));
	}

	/**
	 * @covers  Database_Savepoint_Stack::pop
	 */
	public function test_pop_duplicate()
	{
		$stack = new Database_Savepoint_Stack;

		$stack->push('a');
		$stack->push('b');
		$stack->push('a');

		$this->assertSame('a', $stack->pop());
		$this->assertSame('b', $stack->pop());

		$this->assertSame(0, count($stack));
	}

	/**
	 * @covers  Database_Savepoint_Stack::position
	 */
	public function test_position()
	{
		$stack = new Database_Savepoint_Stack;
		$stack->push('a');
		$stack->push('b');
		$stack->push('c');

		$this->assertSame(1, $stack->position('a'));
		$this->assertSame(2, $stack->position('b'));
		$this->assertSame(3, $stack->position('c'));
		$this->assertSame(NULL, $stack->position('d'));
	}

	/**
	 * @covers  Database_Savepoint_Stack::position
	 */
	public function test_position_duplicate()
	{
		$stack = new Database_Savepoint_Stack;

		$stack->push('a');
		$stack->push('b');
		$stack->push('a');

		$this->assertSame(2, $stack->position('a'));
		$this->assertSame(1, $stack->position('b'));
	}

	public function provider_pop_until()
	{
		return array(
			array(array('a', 'b', 'c'), 'a', 1),
			array(array('a', 'b', 'c'), 'b', 2),
			array(array('a', 'b', 'c'), 'c', 3),

			array(array('a', 'b', 'a'), 'b', 1),
			array(array('a', 'b', 'a'), 'a', 2),
		);
	}

	/**
	 * @covers  Database_Savepoint_Stack::pop_until
	 *
	 * @dataProvider    provider_pop_until
	 *
	 * @param   array   $values     Values for the stack
	 * @param   string  $name       Name to pop
	 * @param   integer $position   Expected position and count
	 */
	public function test_pop_until($values, $name, $position)
	{
		$stack = new Database_Savepoint_Stack;

		foreach ($values as $value)
		{
			$stack->push($value);
		}

		$this->assertSame($position, $stack->pop_until($name));
		$this->assertSame($position, count($stack));
	}

	/**
	 * @covers  Database_Savepoint_Stack::reset
	 */
	public function test_reset()
	{
		$stack = new Database_Savepoint_Stack;
		$stack->push('a');
		$stack->push('b');
		$stack->push('c');

		$this->assertNull($stack->reset());
		$this->assertSame(0, count($stack));
	}
}
