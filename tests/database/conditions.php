<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.conditions
 */
class Database_Conditions_Test extends PHPUnit_Framework_TestCase
{
	public function test_between()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Query_Conditions('2009-11-19', 'between', array('2009-11-1', '2009-12-1'));

		$this->assertSame("'2009-11-19' BETWEEN '2009-11-1' AND '2009-12-1'", $db->quote($conditions));
	}

	public function test_in()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Query_Conditions(new Database_Identifier('a'), 'in', array('x', 5, new Database_Identifier('z')));

		$this->assertSame('"a" IN (\'x\', 5, "z")', $db->quote($conditions));
	}

	public function test_mixed()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Query_Conditions(new Database_Identifier('a'), 'is', NULL);

		$conditions
			->add('and', new Database_Identifier('b'), '=', 'literal')
			->add('or', new Database_Expression('c'), 'operator', new Database_Expression('d'));

		$this->assertSame('"a" IS NULL AND "b" = \'literal\' OR c OPERATOR d', $db->quote($conditions));
	}

	public function test_parentheses()
	{
		$db = new Database_Conditions_Test_DB;
		$conditions = new Database_Query_Conditions;

		$conditions->add('and', 0, '<>', 1);
		$this->assertSame('0 <> 1', $db->quote($conditions));

		$conditions->open('and');
		$this->assertSame('0 <> 1 AND (', $db->quote($conditions));

		$conditions->add('or', 2, '=', 2);
		$this->assertSame('0 <> 1 AND (2 = 2', $db->quote($conditions));

		$conditions->add('or', 2, '=', 2);
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2', $db->quote($conditions));

		$conditions->close();
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2)', $db->quote($conditions));

		$conditions->open('or');
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (', $db->quote($conditions));

		$conditions->add('and', 3, '<>', 4);
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4', $db->quote($conditions));

		$conditions->close();
		$this->assertSame('0 <> 1 AND (2 = 2 OR 2 = 2) OR (3 <> 4)', $db->quote($conditions));
	}
}

class Database_Conditions_Test_DB extends Database
{
	public function escape($value)
	{
		return "'$value'";
	}
}
