<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Constraint_Check_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DDL_Constraint_Check::__construct
	 */
	public function test_constructor()
	{
		$db = new SQL;

		$this->assertSame('CHECK (:conditions)', $db->quote(new SQL_DDL_Constraint_Check));
		$this->assertSame('CHECK (1)', $db->quote(new SQL_DDL_Constraint_Check(new SQL_Conditions(1))));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Check::conditions
	 */
	public function test_conditions()
	{
		$db = new SQL;
		$constraint = new SQL_DDL_Constraint_Check;

		$this->assertSame($constraint, $constraint->conditions(new SQL_Conditions(1)), 'Chainable');
		$this->assertSame('CHECK (1)', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Check::__toString
	 */
	public function test_toString()
	{
		$constraint = new SQL_DDL_Constraint_Check;
		$constraint
			->name('a')
			->conditions(new SQL_Conditions(1));

		$this->assertSame('CONSTRAINT :name CHECK (:conditions)', (string) $constraint);
	}
}
