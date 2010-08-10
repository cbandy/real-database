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
	public function test_add()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_From('one');

		$this->assertSame($from, $from->add('two', 'b'));
		$this->assertSame('"pre_one", "pre_two" AS "b"', $db->quote($from));
	}

	public function test_constructor()
	{
		$db = new Database_From_Test_DB;

		$this->assertSame('', $db->quote(new Database_From));
		$this->assertSame('"pre_one"', $db->quote(new Database_From('one')));
		$this->assertSame('"pre_one" AS "a"', $db->quote(new Database_From('one', 'a')));
	}

	public function test_join()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_From('one');

		$this->assertSame($from, $from->join('two', 'b'));
		$this->assertSame('"pre_one" JOIN "pre_two" AS "b"', $db->quote($from));

		$this->assertSame($from, $from->join('three', NULL, 'left'));
		$this->assertSame('"pre_one" JOIN "pre_two" AS "b" LEFT JOIN "pre_three"', $db->quote($from));
	}

	/**
	 * @dataProvider provider_join_helpers
	 */
	public function test_join_helpers($method, $expected)
	{
		$db = new Database_From_Test_DB;
		$from = new Database_From('one');

		$this->assertSame($from, $from->$method('two'));
		$this->assertSame('"pre_one" '.$expected.' JOIN "pre_two"', $db->quote($from));

		$this->assertSame($from, $from->$method('three', 'a'));
		$this->assertSame('"pre_one" '.$expected.' JOIN "pre_two" '.$expected.' JOIN "pre_three" AS "a"', $db->quote($from));
	}

	public function provider_join_helpers()
	{
		return array
		(
			array('cross_join', 'CROSS'),
			array('full_join',  'FULL'),
			array('inner_join', 'INNER'),
			array('left_join',  'LEFT'),
			array('right_join', 'RIGHT'),

			array('natural_full_join',  'NATURAL FULL'),
			array('natural_inner_join', 'NATURAL INNER'),
			array('natural_left_join',  'NATURAL LEFT'),
			array('natural_right_join', 'NATURAL RIGHT'),
		);
	}

	public function test_on()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_From('one');
		$from->join('two');

		$conditions = new Database_Conditions(new Database_Column('one.x'), '=', new Database_Column('two.x'));

		$this->assertSame($from, $from->on($conditions), 'Chainable (conditions)');
		$this->assertSame('"pre_one" JOIN "pre_two" ON ("pre_one"."x" = "pre_two"."x")', $db->quote($from));

		$from = new Database_From('one');
		$from->join('two');

		$this->assertSame($from, $from->on('one.y', '=', 'two.y'), 'Chainable (operands)');
		$this->assertSame('"pre_one" JOIN "pre_two" ON ("pre_one"."y" = "pre_two"."y")', $db->quote($from));
	}

	public function test_parentheses()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_From;

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

	public function test_using()
	{
		$db = new Database_From_Test_DB;
		$from = new Database_From('one');
		$from->join('two');

		$this->assertSame($from, $from->using(array('x', 'y')));
		$this->assertSame('"pre_one" JOIN "pre_two" USING ("x", "y")', $db->quote($from));
	}
}

class Database_From_Test_DB extends Database
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
