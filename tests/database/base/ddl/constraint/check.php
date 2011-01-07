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
	/**
	 * @covers  Database_DDL_Constraint_Check::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame('CHECK (:conditions)', $db->quote(new Database_DDL_Constraint_Check));
		$this->assertSame('CHECK (1)', $db->quote(new Database_DDL_Constraint_Check(new Database_Conditions(1))));
	}

	/**
	 * @covers  Database_DDL_Constraint_Check::conditions
	 */
	public function test_conditions()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = new Database_DDL_Constraint_Check;

		$this->assertSame($constraint, $constraint->conditions(new Database_Conditions(1)), 'Chainable');
		$this->assertSame('CHECK (1)', $db->quote($constraint));
	}

	/**
	 * @covers  Database_DDL_Constraint_Check::__toString
	 */
	public function test_toString()
	{
		$constraint = new Database_DDL_Constraint_Check;
		$constraint
			->name('a')
			->conditions(new Database_Conditions(1));

		$this->assertSame('CONSTRAINT :name CHECK (:conditions)', (string) $constraint);
	}
}
