<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.sqlserver
 */
class Database_SQLServer_Savepoints_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLServer_Savepoints::push
	 */
	public function test_push()
	{
		$stack = new Database_SQLServer_Savepoints;

		$this->assertSame(1, $stack->push('a'));
		$this->assertSame(2, $stack->push('b'));
		$this->assertSame(3, $stack->push('c'));

		$this->assertSame(3, $stack->count());
		$this->assertSame(3, $stack->uncommitted_count());
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::push
	 */
	public function test_push_duplicate()
	{
		$stack = new Database_SQLServer_Savepoints;

		$this->assertSame(1, $stack->push('a'));
		$this->assertSame(2, $stack->push('b'));
		$this->assertSame(3, $stack->push('a'));

		$this->assertSame(3, $stack->count());
		$this->assertSame(3, $stack->uncommitted_count());
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::pop
	 */
	public function test_pop()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('b');
		$stack->push('c');

		$this->assertSame('c', $stack->pop());
		$this->assertSame('b', $stack->pop());
		$this->assertSame('a', $stack->pop());

		$this->assertSame(0, $stack->count());
		$this->assertSame(0, $stack->uncommitted_count());
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::pop
	 */
	public function test_pop_duplicate()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('b');
		$stack->push('a');

		$this->assertSame('a', $stack->pop());
		$this->assertSame('b', $stack->pop());
		$this->assertSame('a', $stack->pop());

		$this->assertSame(0, $stack->count());
		$this->assertSame(0, $stack->uncommitted_count());
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::position_uncommitted
	 * @covers  Database_SQLServer_Savepoints::uncommitted_position
	 */
	public function test_positions()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('b');
		$stack->push('c');

		$this->assertSame(1, $stack->position_uncommitted('a'));
		$this->assertSame(2, $stack->position_uncommitted('b'));
		$this->assertSame(3, $stack->position_uncommitted('c'));
		$this->assertSame(NULL, $stack->position_uncommitted('d'));

		$this->assertSame(1, $stack->uncommitted_position('a'));
		$this->assertSame(2, $stack->uncommitted_position('b'));
		$this->assertSame(3, $stack->uncommitted_position('c'));
		$this->assertSame(NULL, $stack->uncommitted_position('d'));
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::position_uncommitted
	 * @covers  Database_SQLServer_Savepoints::uncommitted_position
	 */
	public function test_positions_duplicate()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('b');
		$stack->push('a');

		$this->assertSame(3, $stack->position_uncommitted('a'));
		$this->assertSame(2, $stack->position_uncommitted('b'));

		$this->assertSame(3, $stack->uncommitted_position('a'));
		$this->assertSame(2, $stack->uncommitted_position('b'));
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::commit
	 */
	public function test_commit()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('b');
		$stack->push('c');

		$this->assertSame('c', $stack->commit());
		$this->assertSame('b', $stack->commit());
		$this->assertSame('a', $stack->commit());

		$this->assertSame(3, $stack->count());
		$this->assertSame(0, $stack->uncommitted_count());
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::commit
	 */
	public function test_commit_duplicate()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('b');
		$stack->push('a');

		$this->assertSame('a', $stack->commit());
		$this->assertSame('b', $stack->commit());
		$this->assertSame('a', $stack->commit());

		$this->assertSame(3, $stack->count());
		$this->assertSame(0, $stack->uncommitted_count());
	}

	public function provider_commit_to()
	{
		return array(
			array(array('a', 'b', 'c'), 'a', 1, 0),
			array(array('a', 'b', 'c'), 'b', 2, 1),
			array(array('a', 'b', 'c'), 'c', 3, 2),

			array(array('a', 'b', 'a'), 'b', 2, 1),
			array(array('a', 'b', 'a'), 'a', 3, 2),
		);
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::commit_to
	 * @covers  Database_SQLServer_Savepoints::uncommitted_count
	 *
	 * @dataProvider    provider_commit_to
	 *
	 * @param   array   $values     Values for the stack
	 * @param   string  $name       Name to commit
	 * @param   integer $position   Expected position
	 * @param   integer $count      Expected uncommitted count
	 */
	public function test_commit_to($values, $name, $position, $count)
	{
		$stack = new Database_SQLServer_Savepoints;

		foreach ($values as $value)
		{
			$stack->push($value);
		}

		$this->assertSame($position, $stack->commit_to($name));
		$this->assertSame($count, $stack->uncommitted_count());
	}

	/**
	 * @covers  Database_SQLServer_Savepoints::reset
	 */
	public function test_reset()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('b');
		$stack->push('c');

		$this->assertNull($stack->reset());
		$this->assertSame(0, $stack->count());
		$this->assertSame(0, $stack->uncommitted_count());
	}

	/**
	 * @covers  Database_SQLServer_Savepoints
	 */
	public function test_usage_position_uncommitted()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('a');
		$stack->push('a');
		$stack->commit('a');

		// Indicates that the uncommitted name has unreleased duplicates still
		// on the server
		$this->assertTrue(
			$stack->position('a') > $stack->position_uncommitted('a')
		);
	}

	/**
	 * @covers  Database_SQLServer_Savepoints
	 */
	public function test_usage_uncommitted_position()
	{
		$stack = new Database_SQLServer_Savepoints;
		$stack->push('a');
		$stack->push('a');
		$stack->commit('a');

		// Indicates that uncommitted name is for the entire/outer transaction
		$this->assertSame(1, $stack->uncommitted_position('a'));
	}
}
