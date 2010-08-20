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
	public function test_bind()
	{
		$db = $this->sharedFixture;
		$expr = new Database_Expression('?');

		$this->assertSame($expr, $expr->bind(0, $var));

		$this->assertSame('NULL', $db->quote($expr));

		$var = 1;
		$this->assertSame('1', $db->quote($expr));

		$var = 'A';
		$this->assertSame("'A'", $db->quote($expr));
	}

	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame("'A'", $db->quote(new Database_Expression('?', array('A'))));

		$this->assertSame("'A'", $db->quote(new Database_Expression(':x', array(':x' => 'A'))));

		$this->assertSame("'A' 8 'C'", $db->quote(new Database_Expression('? :x ?', array('A', 'C', ':x' => 8))));
	}

	public function test_param()
	{
		$db = $this->sharedFixture;
		$expr = new Database_Expression('?');

		$this->assertSame($expr, $expr->param(0, NULL));
		$this->assertSame('NULL', $db->quote($expr));

		$this->assertSame($expr, $expr->param(0, 1));
		$this->assertSame('1', $db->quote($expr));

		$this->assertSame($expr, $expr->param(0, 'A'));
		$this->assertSame("'A'", $db->quote($expr));
	}

	public function test_parameters()
	{
		$db = $this->sharedFixture;
		$expr = new Database_Expression('?');

		$this->assertSame($expr, $expr->parameters(array('A')));
		$this->assertSame("'A'", $db->quote($expr));

		$this->assertSame($expr, $expr->parameters(array('B')));
		$this->assertSame("'B'", $db->quote($expr));

		$expr = new Database_Expression('? ?');

		$this->assertSame($expr, $expr->parameters(array('A', 'B')));
		$this->assertSame("'A' 'B'", $db->quote($expr));

		$this->assertSame($expr, $expr->parameters(array('C')));
		$this->assertSame("'C' 'B'", $db->quote($expr));
	}
}
