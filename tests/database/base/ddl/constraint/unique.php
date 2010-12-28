<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_DDL_Constraint_Unique_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('UNIQUE', $db->quote(new Database_DDL_Constraint_Unique));
		$this->assertSame('UNIQUE', $db->quote(new Database_DDL_Constraint_Unique(array())));
		$this->assertSame('UNIQUE ("a")', $db->quote(new Database_DDL_Constraint_Unique(array('a'))));
	}

	public function test_columns()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Unique;

		$this->assertSame($constraint, $constraint->columns(array('a')));
		$this->assertSame('UNIQUE ("a")', $db->quote($constraint));
	}
}
