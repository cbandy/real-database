<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expressions
 */
class Database_Base_From_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @param   string  $method     Method to call
	 * @param   string  $expected   Expected join type
	 */
	protected function _test_join_helper($method, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$from = new SQL_From('one');

		$this->assertSame($from, $from->$method('two'), 'Chainable (string)');
		$this->assertSame('"pre_one" '.$expected.' JOIN "pre_two"', $db->quote($from));

		$this->assertSame($from, $from->$method('three', 'a'), 'Chainable (string, string)');
		$this->assertSame('"pre_one" '.$expected.' JOIN "pre_two" '.$expected.' JOIN "pre_three" AS "a"', $db->quote($from));
	}

	/**
	 * @covers  SQL_From::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('', $db->quote(new SQL_From));
		$this->assertSame('"pre_one"', $db->quote(new SQL_From('one')));
		$this->assertSame('"pre_one" AS "a"', $db->quote(new SQL_From('one', 'a')));
	}

	/**
	 * @covers  SQL_From::_add
	 * @covers  SQL_From::add
	 */
	public function test_add()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$from = new SQL_From('one');

		$this->assertSame($from, $from->add('two', 'b'));
		$this->assertSame('"pre_one", "pre_two" AS "b"', $db->quote($from));
	}

	/**
	 * @covers  SQL_From::join
	 */
	public function test_join()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$from = new SQL_From('one');

		$this->assertSame($from, $from->join('two', 'b'));
		$this->assertSame('"pre_one" JOIN "pre_two" AS "b"', $db->quote($from));

		$this->assertSame($from, $from->join('three', NULL, 'left'));
		$this->assertSame('"pre_one" JOIN "pre_two" AS "b" LEFT JOIN "pre_three"', $db->quote($from));
	}

	/**
	 * @covers  SQL_From::cross_join
	 */
	public function test_cross_join()
	{
		$this->_test_join_helper('cross_join', 'CROSS');
	}

	/**
	 * @covers  SQL_From::full_join
	 */
	public function test_full_join()
	{
		$this->_test_join_helper('full_join', 'FULL');
	}

	/**
	 * @covers  SQL_From::inner_join
	 */
	public function test_inner_join()
	{
		$this->_test_join_helper('inner_join', 'INNER');
	}

	/**
	 * @covers  SQL_From::left_join
	 */
	public function test_left_join()
	{
		$this->_test_join_helper('left_join', 'LEFT');
	}

	/**
	 * @covers  SQL_From::right_join
	 */
	public function test_right_join()
	{
		$this->_test_join_helper('right_join', 'RIGHT');
	}

	/**
	 * @covers  SQL_From::natural_full_join
	 */
	public function test_natural_full_join()
	{
		$this->_test_join_helper('natural_full_join', 'NATURAL FULL');
	}

	/**
	 * @covers  SQL_From::natural_inner_join
	 */
	public function test_natural_inner_join()
	{
		$this->_test_join_helper('natural_inner_join', 'NATURAL INNER');
	}

	/**
	 * @covers  SQL_From::natural_left_join
	 */
	public function test_natural_left_join()
	{
		$this->_test_join_helper('natural_left_join', 'NATURAL LEFT');
	}

	/**
	 * @covers  SQL_From::natural_right_join
	 */
	public function test_natural_right_join()
	{
		$this->_test_join_helper('natural_right_join', 'NATURAL RIGHT');
	}

	/**
	 * @covers  SQL_From::on
	 */
	public function test_on()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$from = new SQL_From('one');
		$from->join('two');

		$conditions = new SQL_Conditions(new SQL_Column('one.x'), '=', new SQL_Column('two.x'));

		$this->assertSame($from, $from->on($conditions), 'Chainable (conditions)');
		$this->assertSame('"pre_one" JOIN "pre_two" ON ("pre_one"."x" = "pre_two"."x")', $db->quote($from));

		$from = new SQL_From('one');
		$from->join('two');

		$this->assertSame($from, $from->on('one.y', '=', 'two.y'), 'Chainable (operands)');
		$this->assertSame('"pre_one" JOIN "pre_two" ON ("pre_one"."y" = "pre_two"."y")', $db->quote($from));
	}

	/**
	 * @covers  SQL_From::open
	 * @covers  SQL_From::close
	 */
	public function test_parentheses()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$from = new SQL_From;

		$this->assertSame($from, $from->open());
		$this->assertSame('(', $db->quote($from));

		$from->add('one', 'a');
		$this->assertSame('("pre_one" AS "a"', $db->quote($from));

		$this->assertSame($from, $from->open());
		$this->assertSame('("pre_one" AS "a", (', $db->quote($from));

		$from->add('two');
		$this->assertSame('("pre_one" AS "a", ("pre_two"', $db->quote($from));

		$from->join('three');
		$this->assertSame('("pre_one" AS "a", ("pre_two" JOIN "pre_three"', $db->quote($from));

		$this->assertSame($from, $from->close());
		$this->assertSame('("pre_one" AS "a", ("pre_two" JOIN "pre_three")', $db->quote($from));

		$this->assertSame($from, $from->close());
		$this->assertSame('("pre_one" AS "a", ("pre_two" JOIN "pre_three"))', $db->quote($from));
	}

	/**
	 * @covers  SQL_From::using
	 */
	public function test_using()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$from = new SQL_From('one');
		$from->join('two');

		$this->assertSame($from, $from->using(array('x', 'y')));
		$this->assertSame('"pre_one" JOIN "pre_two" USING ("x", "y")', $db->quote($from));
	}
}
