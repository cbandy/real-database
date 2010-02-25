<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.expression
 */
class Database_Expression_Test extends PHPUnit_Framework_TestCase
{
	public function test_bind()
	{
		$db = new Database_Expression_Test_DB;

		$expr = new Database_Expression('?');
		$expr->bind(0, $var);

		$this->assertSame('NULL', $db->quote($expr));

		$var = 1;
		$this->assertSame('1', $db->quote($expr));

		$var = 'A';
		$this->assertSame("'A'", $db->quote($expr));
	}

	public function test_constructor()
	{
		$db = new Database_Expression_Test_DB;

		$this->assertSame("'A'", $db->quote(new Database_Expression('?', array('A'))));

		$this->assertSame("'A'", $db->quote(new Database_Expression(':x', array(':x' => 'A'))));

		$this->assertSame("'A' 8 'C'", $db->quote(new Database_Expression('? :x ?', array('A', 'C', ':x' => 8))));
	}

	public function test_parameters()
	{
		$db = new Database_Expression_Test_DB;

		$expr = new Database_Expression('?');

		$expr->parameters(array('A'));
		$this->assertSame("'A'", $db->quote($expr));

		$expr->parameters(array('B'));
		$this->assertSame("'B'", $db->quote($expr));

		$expr = new Database_Expression('? ?');

		$expr->parameters(array('A', 'B'));
		$this->assertSame("'A' 'B'", $db->quote($expr));

		$expr->parameters(array('C'));
		$this->assertSame("'C' 'B'", $db->quote($expr));
	}
}

class Database_Expression_Test_DB extends Database
{
	public function escape($value)
	{
		return "'$value'";
	}
}
