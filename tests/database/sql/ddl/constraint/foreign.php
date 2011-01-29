<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Constraint_Foreign_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DDL_Constraint_Foreign::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('REFERENCES "pre_a"', $db->quote(new SQL_DDL_Constraint_Foreign('a')));
		$this->assertSame('REFERENCES "pre_a" ("b")', $db->quote(new SQL_DDL_Constraint_Foreign('a', array('b'))));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::columns
	 */
	public function test_columns()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$constraint = new SQL_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->columns(array('b')), 'Chainable');
		$this->assertSame('REFERENCES "pre_a" ("b")', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::deferrable
	 */
	public function test_deferrable()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$constraint = new SQL_DDL_Constraint_Foreign('a');

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

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::match
	 */
	public function test_match()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$constraint = new SQL_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->match('simple'));
		$this->assertSame('REFERENCES "pre_a" MATCH SIMPLE', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::on
	 */
	public function test_on()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$constraint = new SQL_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->on('delete', 'cascade'), 'Chainable (delete, cascade)');
		$this->assertSame('REFERENCES "pre_a" ON DELETE CASCADE', $db->quote($constraint));

		$this->assertSame($constraint, $constraint->on('update', 'set default'), 'Chainable (update, set default)');
		$this->assertSame('REFERENCES "pre_a" ON DELETE CASCADE ON UPDATE SET DEFAULT', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::referencing
	 */
	public function test_referencing()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$constraint = new SQL_DDL_Constraint_Foreign('a');

		$this->assertSame($constraint, $constraint->referencing(array('b')));
		$this->assertSame('FOREIGN KEY ("b") REFERENCES "pre_a"', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::table
	 */
	public function test_table()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$constraint = new SQL_DDL_Constraint_Foreign;

		$this->assertSame($constraint, $constraint->table('a'), 'Chainable');
		$this->assertSame('REFERENCES "pre_a"', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::__toString
	 */
	public function test_toString()
	{
		$constraint = new SQL_DDL_Constraint_Foreign;
		$constraint
			->name('a')
			->referencing(array('b'))
			->table('c')
			->columns(array('d'))
			->match('e')
			->on('delete', 'f')
			->on('update', 'g');

		$this->assertSame('CONSTRAINT :name FOREIGN KEY (:referencing) REFERENCES :table (:columns) MATCH E ON DELETE F ON UPDATE G', (string) $constraint);

		$constraint->deferrable(FALSE);

		$this->assertSame('CONSTRAINT :name FOREIGN KEY (:referencing) REFERENCES :table (:columns) MATCH E ON DELETE F ON UPDATE G NOT DEFERRABLE', (string) $constraint);

		$constraint->deferrable('h');

		$this->assertSame('CONSTRAINT :name FOREIGN KEY (:referencing) REFERENCES :table (:columns) MATCH E ON DELETE F ON UPDATE G DEFERRABLE INITIALLY H', (string) $constraint);
	}
}
