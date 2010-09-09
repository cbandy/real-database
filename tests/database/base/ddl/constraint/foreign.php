<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_DDL_Constraint_Foreign_Test extends PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('REFERENCES "pre_a"', $db->quote(new Database_DDL_Constraint_Foreign('a')));
		$this->assertSame('REFERENCES "pre_a" ("b")', $db->quote(new Database_DDL_Constraint_Foreign('a', array('b'))));
	}

	public function test_name()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->name('b'));
		$this->assertSame('CONSTRAINT "b" REFERENCES "pre_a"', $db->quote($constraint));
	}

	public function test_referencing()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->referencing(array('b')));
		$this->assertSame('FOREIGN KEY ("b") REFERENCES "pre_a"', $db->quote($constraint));
	}

	public function test_match()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->match('simple'));
		$this->assertSame('REFERENCES "pre_a" MATCH SIMPLE', $db->quote($constraint));
	}

	public function test_on()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->on('delete', 'cascade'), 'Chainable (delete, cascade)');
		$this->assertSame('REFERENCES "pre_a" ON DELETE CASCADE', $db->quote($constraint));

		$this->assertSame($constraint, $constraint->on('update', 'set default'), 'Chainable (update, set default)');
		$this->assertSame('REFERENCES "pre_a" ON DELETE CASCADE ON UPDATE SET DEFAULT', $db->quote($constraint));
	}

	public function test_deferrable()
	{
		$db = $this->sharedFixture;
		$constraint = new Database_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->deferrable(TRUE), 'Chainable (TRUE)');
		$this->assertSame('REFERENCES "pre_a" DEFERRABLE', $db->quote($constraint));

		$this->assertSame($constraint, $constraint->deferrable(FALSE), 'Chainable (FALSE)');
		$this->assertSame('REFERENCES "pre_a" NOT DEFERRABLE', $db->quote($constraint));

		$this->assertSame($constraint, $constraint->deferrable('deferred'), 'Chainable (deferred)');
		$this->assertSame('REFERENCES "pre_a" DEFERRABLE INITIALLY DEFERRED', $db->quote($constraint));

		$this->assertSame($constraint, $constraint->deferrable('immediate'), 'Chainable (immediate)');
		$this->assertSame('REFERENCES "pre_a" DEFERRABLE INITIALLY IMMEDIATE', $db->quote($constraint));

		$this->assertSame($constraint, $constraint->deferrable(NULL), 'Chainable (NULL)');
		$this->assertSame('REFERENCES "pre_a"', $db->quote($constraint));
	}
}
