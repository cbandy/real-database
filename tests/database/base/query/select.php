<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Base_Query_Select_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Query_Select::__construct
	 */
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('SELECT ',            $db->quote(new Database_Query_Select));
		$this->assertSame('SELECT :columns',    $db->quote(new Database_Query_Select(array())));
		$this->assertSame('SELECT "b" AS "a"',  $db->quote(new Database_Query_Select(array('a' => 'b'))));
	}

	/**
	 * @covers  Database_Query_Select::select
	 */
	public function test_select()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select;

		$this->assertSame($query, $query->select(array('x')));
		$this->assertSame('SELECT "x"', $db->quote($query));

		$this->assertSame($query, $query->select(array('y' => new Database_Expression('a'))));
		$this->assertSame('SELECT "x", a AS "y"', $db->quote($query));

		$this->assertSame($query, $query->select(new Database_Expression('b')));
		$this->assertSame('SELECT b', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::distinct
	 */
	public function test_distinct()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select;

		$this->assertSame($query, $query->distinct(), 'Chainable (void)');
		$this->assertSame('SELECT DISTINCT ', $db->quote($query), 'Distinct (void)');

		$this->assertSame($query, $query->distinct(FALSE), 'Chainable (FALSE)');
		$this->assertSame('SELECT ', $db->quote($query), 'Distinct (FALSE)');

		$this->assertSame($query, $query->distinct(TRUE), 'Chainable (TRUE)');
		$this->assertSame('SELECT DISTINCT ', $db->quote($query), 'Distinct (TRUE)');
	}

	/**
	 * @covers  Database_Query_Select::column
	 */
	public function test_column()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select;

		$this->assertSame($query, $query->column('one.x', 'a'));
		$this->assertSame('SELECT "pre_one"."x" AS "a"', $db->quote($query));

		$this->assertSame($query, $query->column('y'));
		$this->assertSame('SELECT "pre_one"."x" AS "a", "y"', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::from
	 */
	public function test_from()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select(array('one.x'));

		$this->assertSame($query, $query->from('one', 'a'), 'Chainable (table)');
		$this->assertSame('SELECT "pre_one"."x" FROM "pre_one" AS "a"', $db->quote($query));

		$from = new Database_From('one');
		$from->add('two')->join('three');

		$this->assertSame($query, $query->from($from), 'Chainable (from)');
		$this->assertSame('SELECT "pre_one"."x" FROM "pre_one", "pre_two" JOIN "pre_three"', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::where
	 */
	public function test_where()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select(new Database_Expression(1));

		$this->assertSame($query, $query->where(new Database_Conditions(new Database_Column('y'), '=', 1)), 'Chainable (conditions)');
		$this->assertSame('SELECT 1 WHERE "y" = 1', $db->quote($query));

		$this->assertSame($query, $query->where('y', '=', 0), 'Chainable (operands)');
		$this->assertSame('SELECT 1 WHERE "y" = 0', $db->quote($query));

		$conditions = new Database_Conditions;
		$conditions->open(NULL)->add(NULL, new Database_Column('y'), '=', 0)->close();

		$this->assertSame($query, $query->where($conditions, '=', TRUE), 'Chainable (conditions as operand)');
		$this->assertSame('SELECT 1 WHERE ("y" = 0) = \'1\'', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::group_by
	 */
	public function test_group_by()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select(array('x'));

		$this->assertSame($query, $query->group_by(array('y', 'one.z', new Database_Expression('expr'))));

		$this->assertSame('SELECT "x" GROUP BY "y", "pre_one"."z", expr', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::having
	 */
	public function test_having()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select(array('x'));

		$this->assertSame($query, $query->having(new Database_Conditions(new Database_Column('x'), '=', 1)), 'Chainable (conditions)');
		$this->assertSame('SELECT "x" HAVING "x" = 1', $db->quote($query));

		$this->assertSame($query, $query->having('x', '=', 0), 'Chainable (operands)');
		$this->assertSame('SELECT "x" HAVING "x" = 0', $db->quote($query));

		$conditions = new Database_Conditions;
		$conditions->open(NULL)->add(NULL, new Database_Column('x'), '=', 0)->close();

		$this->assertSame($query, $query->having($conditions, '=', TRUE), 'Chainable (conditions as operand)');
		$this->assertSame('SELECT "x" HAVING ("x" = 0) = \'1\'', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::order_by
	 */
	public function test_order_by()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select(array('x', 'y'));

		$this->assertSame($query, $query->order_by('x'));
		$this->assertSame('SELECT "x", "y" ORDER BY "x"', $db->quote($query));

		$this->assertSame($query, $query->order_by(new Database_Expression('other'), 'asc'));
		$this->assertSame('SELECT "x", "y" ORDER BY "x", other ASC', $db->quote($query));

		$this->assertSame($query, $query->order_by('y', new Database_Expression('USING something')));
		$this->assertSame('SELECT "x", "y" ORDER BY "x", other ASC, "y" USING something', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::limit
	 */
	public function test_limit()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select(array('x'));

		$this->assertSame($query, $query->limit(5));
		$this->assertSame('SELECT "x" LIMIT 5', $db->quote($query));

		$this->assertSame($query, $query->limit(0));
		$this->assertSame('SELECT "x" LIMIT 0', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::offset
	 */
	public function test_offset()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select(array('x'));

		$this->assertSame($query, $query->offset(5));
		$this->assertSame('SELECT "x" OFFSET 5', $db->quote($query));

		$this->assertSame($query, $query->offset(0));
		$this->assertSame('SELECT "x"', $db->quote($query));
	}

	/**
	 * @covers  Database_Query_Select::__toString
	 */
	public function test_toString()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query_Select;
		$query
			->distinct()
			->select(array('a'))
			->from('b')
			->where('c', '=', 'd')
			->group_by(array('e'))
			->having('f', '=', 'g')
			->order_by('h')
			->limit(1)
			->offset(1);

		$this->assertSame('SELECT :distinct :columns FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset', (string) $query);
	}
}
