<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Set_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Query_Set::__construct
	 */
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('', $db->quote(new Database_Query_Set));
		$this->assertSame('(a)', $db->quote(new Database_Query_Set(new Database_Query('a'))));
	}

	/**
	 * @covers  Database_Query_Set::add
	 */
	public function test_add()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set;

		$this->assertSame($query, $query->add('a', new Database_Query('b')));
		$this->assertSame('(b)', $db->quote($query));

		$this->assertSame($query, $query->add('c', new Database_Query('d')));
		$this->assertSame('(b) C (d)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::open
	 */
	public function test_open()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set;

		$this->assertSame($query, $query->open('a'), 'Chainable (void)');
		$this->assertSame('(', $db->quote($query));

		$this->assertSame($query, $query->open('b', new Database_Query('c')), 'Chainable (query, empty)');
		$this->assertSame('(((c)', $db->quote($query));

		$this->assertSame($query, $query->open('d', new Database_Query('e')), 'Chainable (query, not empty)');
		$this->assertSame('(((c) D ((e)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::close
	 */
	public function test_close()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set;

		$this->assertSame($query, $query->close(), 'Chainable (1)');
		$this->assertSame(')', $db->quote($query));

		$this->assertSame($query, $query->close(), 'Chainable (2)');
		$this->assertSame('))', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::close
	 * @covers  Database_Query_Set::open
	 */
	public function test_close_open()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set;

		$this->assertSame(') A (',      $db->quote($query->close()->open('a')));
		$this->assertSame(') A () B (', $db->quote($query->close()->open('b')));
	}

	/**
	 * @covers  Database_Query_Set::except
	 */
	public function test_except()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->except(new Database_Query('b')), 'Chainable (query)');
		$this->assertSame('(a) EXCEPT (b)', $db->quote($query));

		$this->assertSame($query, $query->except(new Database_Query('c'), FALSE), 'Chainable (query, FALSE)');
		$this->assertSame('(a) EXCEPT (b) EXCEPT (c)', $db->quote($query));

		$this->assertSame($query, $query->except(new Database_Query('d'), TRUE), 'Chainable (query, TRUE)');
		$this->assertSame('(a) EXCEPT (b) EXCEPT (c) EXCEPT ALL (d)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::except_open
	 */
	public function test_except_open()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->except_open(), 'Chainable (void)');
		$this->assertSame('(a) EXCEPT (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->except_open(NULL), 'Chainable (NULL)');
		$this->assertSame('(a) EXCEPT () EXCEPT (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->except_open(NULL, FALSE), 'Chainable (NULL, FALSE)');
		$this->assertSame('(a) EXCEPT () EXCEPT () EXCEPT (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->except_open(NULL, TRUE), 'Chainable (NULL, TRUE)');
		$this->assertSame('(a) EXCEPT () EXCEPT () EXCEPT () EXCEPT ALL (', $db->quote($query));

		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->except_open(new Database_Query('b')), 'Chainable (query)');
		$this->assertSame('(a) EXCEPT ((b)', $db->quote($query));

		$this->assertSame($query, $query->except_open(new Database_Query('c'), FALSE), 'Chainable (query, FALSE)');
		$this->assertSame('(a) EXCEPT ((b) EXCEPT ((c)', $db->quote($query));

		$this->assertSame($query, $query->except_open(new Database_Query('d'), TRUE), 'Chainable (query, TRUE)');
		$this->assertSame('(a) EXCEPT ((b) EXCEPT ((c) EXCEPT ALL ((d)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::intersect
	 */
	public function test_intersect()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->intersect(new Database_Query('b')), 'Chainable (query)');
		$this->assertSame('(a) INTERSECT (b)', $db->quote($query));

		$this->assertSame($query, $query->intersect(new Database_Query('c'), FALSE), 'Chainable (query, FALSE)');
		$this->assertSame('(a) INTERSECT (b) INTERSECT (c)', $db->quote($query));

		$this->assertSame($query, $query->intersect(new Database_Query('d'), TRUE), 'Chainable (query, TRUE)');
		$this->assertSame('(a) INTERSECT (b) INTERSECT (c) INTERSECT ALL (d)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::intersect_open
	 */
	public function test_intersect_open()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->intersect_open(), 'Chainable (void)');
		$this->assertSame('(a) INTERSECT (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->intersect_open(NULL), 'Chainable (NULL)');
		$this->assertSame('(a) INTERSECT () INTERSECT (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->intersect_open(NULL, FALSE), 'Chainable (NULL, FALSE)');
		$this->assertSame('(a) INTERSECT () INTERSECT () INTERSECT (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->intersect_open(NULL, TRUE), 'Chainable (NULL, TRUE)');
		$this->assertSame('(a) INTERSECT () INTERSECT () INTERSECT () INTERSECT ALL (', $db->quote($query));

		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->intersect_open(new Database_Query('b')), 'Chainable (query)');
		$this->assertSame('(a) INTERSECT ((b)', $db->quote($query));

		$this->assertSame($query, $query->intersect_open(new Database_Query('c'), FALSE), 'Chainable (query, FALSE)');
		$this->assertSame('(a) INTERSECT ((b) INTERSECT ((c)', $db->quote($query));

		$this->assertSame($query, $query->intersect_open(new Database_Query('d'), TRUE), 'Chainable (query, TRUE)');
		$this->assertSame('(a) INTERSECT ((b) INTERSECT ((c) INTERSECT ALL ((d)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::union
	 */
	public function test_union()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->union(new Database_Query('b')), 'Chainable (query)');
		$this->assertSame('(a) UNION (b)', $db->quote($query));

		$this->assertSame($query, $query->union(new Database_Query('c'), FALSE), 'Chainable (query, FALSE)');
		$this->assertSame('(a) UNION (b) UNION (c)', $db->quote($query));

		$this->assertSame($query, $query->union(new Database_Query('d'), TRUE), 'Chainable (query, TRUE)');
		$this->assertSame('(a) UNION (b) UNION (c) UNION ALL (d)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::union_open
	 */
	public function test_union_open()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->union_open(), 'Chainable (void)');
		$this->assertSame('(a) UNION (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->union_open(NULL), 'Chainable (NULL)');
		$this->assertSame('(a) UNION () UNION (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->union_open(NULL, FALSE), 'Chainable (NULL, FALSE)');
		$this->assertSame('(a) UNION () UNION () UNION (', $db->quote($query));

		$query->close();

		$this->assertSame($query, $query->union_open(NULL, TRUE), 'Chainable (NULL, TRUE)');
		$this->assertSame('(a) UNION () UNION () UNION () UNION ALL (', $db->quote($query));

		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->union_open(new Database_Query('b')), 'Chainable (query)');
		$this->assertSame('(a) UNION ((b)', $db->quote($query));

		$this->assertSame($query, $query->union_open(new Database_Query('c'), FALSE), 'Chainable (query, FALSE)');
		$this->assertSame('(a) UNION ((b) UNION ((c)', $db->quote($query));

		$this->assertSame($query, $query->union_open(new Database_Query('d'), TRUE), 'Chainable (query, TRUE)');
		$this->assertSame('(a) UNION ((b) UNION ((c) UNION ALL ((d)', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::__toString
	 * @covers  Database_Query_Set::order_by
	 */
	public function test_order_by()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->order_by('x'), 'Chainable (string)');
		$this->assertSame('(a) ORDER BY "x"', $db->quote($query));

		$this->assertSame($query, $query->order_by(new Database_Expression('other'), 'asc'), 'Chainable (expression, string)');
		$this->assertSame('(a) ORDER BY "x", other ASC', $db->quote($query));

		$this->assertSame($query, $query->order_by('y', new Database_Expression('USING something')), 'Chainable (string, expression)');
		$this->assertSame('(a) ORDER BY "x", other ASC, "y" USING something', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::__toString
	 * @covers  Database_Query_Set::limit
	 */
	public function test_limit()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->limit(5));
		$this->assertSame('(a) LIMIT 5', $db->quote($query));

		$this->assertSame($query, $query->limit(0));
		$this->assertSame('(a) LIMIT 0', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Set::__toString
	 * @covers  Database_Query_Set::offset
	 */
	public function test_offset()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->offset(5));
		$this->assertSame('(a) OFFSET 5', $db->quote($query));

		$this->assertSame($query, $query->offset(0));
		$this->assertSame('(a)', $db->quote($query));
	}
}
