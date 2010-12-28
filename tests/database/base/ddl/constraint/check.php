<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_DDL_Constraint_Check_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('CHECK (1)', $db->quote(new Database_DDL_Constraint_Check(new Database_Conditions(1))));
	}

	public function test_conditions()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Check(new Database_Conditions(1));

		$this->assertSame($constraint, $constraint->conditions(new Database_Conditions(2)));
		$this->assertSame('CHECK (2)', $db->quote($constraint));
	}
}
