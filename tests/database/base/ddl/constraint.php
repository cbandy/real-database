<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_DDL_Constraint_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_DDL_Constraint::name
	 */
	public function test_name()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = $this->getMockForAbstractClass('Database_DDL_Constraint', array(''));

		$this->assertSame($constraint, $constraint->name('a'), 'Chainable');
		$this->assertSame('CONSTRAINT "a" ', $db->quote($constraint));
	}

	/**
	 * @covers  Database_DDL_Constraint::__toString
	 */
	public function test_toString()
	{
		$constraint = $this->getMockForAbstractClass('Database_DDL_Constraint', array(''));

		$this->assertSame('', (string) $constraint);

		$constraint->name('a');

		$this->assertSame('CONSTRAINT :name ', (string) $constraint);

	}
}
