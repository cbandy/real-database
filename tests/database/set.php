<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.set
 */
class Database_Set_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = new Database_Set_Test_DB;

		$this->assertSame('', $db->quote(new Database_Query_Set));
		$this->assertSame('(asdf)', $db->quote(new Database_Query_Set(new Database_Query('asdf'))));
	}

	public function test_except()
	{
		$db = new Database_Set_Test_DB;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->except(new Database_Query('b')), 'Chainable (void)');
		$this->assertSame('(a) EXCEPT (b)', $db->quote($query));

		$this->assertSame($query, $query->except(new Database_Query('c'), FALSE), 'Chainable (FALSE)');
		$this->assertSame('(a) EXCEPT (b) EXCEPT (c)', $db->quote($query));

		$this->assertSame($query, $query->except(new Database_Query('d'), TRUE), 'Chainable (TRUE)');
		$this->assertSame('(a) EXCEPT (b) EXCEPT (c) EXCEPT ALL (d)', $db->quote($query));
	}

	public function test_intersect()
	{
		$db = new Database_Set_Test_DB;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->intersect(new Database_Query('b')), 'Chainable (void)');
		$this->assertSame('(a) INTERSECT (b)', $db->quote($query));

		$this->assertSame($query, $query->intersect(new Database_Query('c'), FALSE), 'Chainable (FALSE)');
		$this->assertSame('(a) INTERSECT (b) INTERSECT (c)', $db->quote($query));

		$this->assertSame($query, $query->intersect(new Database_Query('d'), TRUE), 'Chainable (TRUE)');
		$this->assertSame('(a) INTERSECT (b) INTERSECT (c) INTERSECT ALL (d)', $db->quote($query));
	}

	public function test_union()
	{
		$db = new Database_Set_Test_DB;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->union(new Database_Query('b')), 'Chainable (void)');
		$this->assertSame('(a) UNION (b)', $db->quote($query));

		$this->assertSame($query, $query->union(new Database_Query('c'), FALSE), 'Chainable (FALSE)');
		$this->assertSame('(a) UNION (b) UNION (c)', $db->quote($query));

		$this->assertSame($query, $query->union(new Database_Query('d'), TRUE), 'Chainable (TRUE)');
		$this->assertSame('(a) UNION (b) UNION (c) UNION ALL (d)', $db->quote($query));
	}

	public function test_parentheses()
	{
		$db = new Database_Set_Test_DB;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->open('union'), 'Chainable (1)');
		$this->assertSame('(a) UNION (', $db->quote($query));

		$query->add('except', new Database_Query('b'));
		$this->assertSame('(a) UNION ((b)', $db->quote($query));

		$query->add('intersect', new Database_Query('c'));
		$this->assertSame('(a) UNION ((b) INTERSECT (c)', $db->quote($query));

		$this->assertSame($query, $query->close(), 'Chainable (2)');
		$this->assertSame('(a) UNION ((b) INTERSECT (c))', $db->quote($query));
	}

	public function test_order_by()
	{
		$db = new Database_Set_Test_DB;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->order_by('x'));
		$this->assertSame('(a) ORDER BY "x"', $db->quote($query));

		$this->assertSame($query, $query->order_by(new Database_Expression('other'), 'asc'));
		$this->assertSame('(a) ORDER BY "x", other ASC', $db->quote($query));

		$this->assertSame($query, $query->order_by('y', new Database_Expression('USING something')));
		$this->assertSame('(a) ORDER BY "x", other ASC, "y" USING something', $db->quote($query));
	}

	public function test_limit()
	{
		$db = new Database_Set_Test_DB;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->limit(5));
		$this->assertSame('(a) LIMIT 5', $db->quote($query));

		$this->assertSame($query, $query->limit(0));
		$this->assertSame('(a) LIMIT 0', $db->quote($query));
	}

	public function test_offset()
	{
		$db = new Database_Set_Test_DB;
		$query = new Database_Query_Set(new Database_Query('a'));

		$this->assertSame($query, $query->offset(5));
		$this->assertSame('(a) OFFSET 5', $db->quote($query));

		$this->assertSame($query, $query->offset(0));
		$this->assertSame('(a)', $db->quote($query));
	}
}

class Database_Set_Test_DB extends Database
{
	public function __construct($name = NULL, $config = NULL) {}

	public function begin() {}

	public function commit() {}

	public function connect() {}

	public function disconnect() {}

	public function execute_command($statement) {}

	public function execute_query($statement, $as_object = FALSE) {}

	public function rollback() {}

	public function table_prefix()
	{
		return 'pre_';
	}
}
