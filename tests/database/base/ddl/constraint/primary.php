<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_DDL_Constraint_Primary_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('PRIMARY KEY', $db->quote(new Database_DDL_Constraint_Primary));
		$this->assertSame('PRIMARY KEY', $db->quote(new Database_DDL_Constraint_Primary(array())));
		$this->assertSame('PRIMARY KEY ("a")', $db->quote(new Database_DDL_Constraint_Primary(array('a'))));
	}

	public function test_name()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Primary;

		$this->assertSame($constraint, $constraint->name('a'));
		$this->assertSame('CONSTRAINT "a" PRIMARY KEY', $db->quote($constraint));
	}

	public function test_columns()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Primary;

		$this->assertSame($constraint, $constraint->columns(array('a')));
		$this->assertSame('PRIMARY KEY ("a")', $db->quote($constraint));
	}
}
