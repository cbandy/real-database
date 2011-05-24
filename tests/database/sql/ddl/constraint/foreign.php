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
	public function provider_constructor()
	{
		return array(
			array(array(), 'REFERENCES "pre_"'),
			array(array('a'), 'REFERENCES "pre_a"'),
			array(array('a', array('b')), 'REFERENCES "pre_a" ("b")'),
			array(array('a', array('b', 'c')), 'REFERENCES "pre_a" ("b", "c")'),
		);
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$class = new ReflectionClass('SQL_DDL_Constraint_Foreign');
		$constraint = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($constraint));
	}

	public function provider_columns()
	{
		return array(
			array(NULL, 'REFERENCES ""'),

			array(
				array('a'),
				'REFERENCES "" ("a")',
			),
			array(
				array('a', 'b'),
				'REFERENCES "" ("a", "b")',
			),

			array(
				array(new SQL_Column('a')),
				'REFERENCES "" ("a")',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'REFERENCES "" ("a", "b")',
			),

			array(
				array(new SQL_Expression('a')),
				'REFERENCES "" (a)',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'REFERENCES "" (a, b)',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_columns($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = new SQL_DDL_Constraint_Foreign;

		$this->assertSame($constraint, $constraint->columns($value), 'Chainable');
		$this->assertSame($expected, $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_columns_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = new SQL_DDL_Constraint_Foreign;
		$constraint->columns($value);

		$constraint->columns(NULL);

		$this->assertSame('REFERENCES ""', $db->quote($constraint));
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

	public function provider_referencing()
	{
		return array(
			array(NULL, 'REFERENCES ""'),

			array(
				array('a'),
				'FOREIGN KEY ("a") REFERENCES ""',
			),
			array(
				array('a', 'b'),
				'FOREIGN KEY ("a", "b") REFERENCES ""',
			),

			array(
				array(new SQL_Column('a')),
				'FOREIGN KEY ("a") REFERENCES ""',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'FOREIGN KEY ("a", "b") REFERENCES ""',
			),

			array(
				array(new SQL_Expression('a')),
				'FOREIGN KEY (a) REFERENCES ""',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'FOREIGN KEY (a, b) REFERENCES ""',
			),
		);
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::referencing
	 *
	 * @dataProvider    provider_referencing
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_referencing($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = new SQL_DDL_Constraint_Foreign;

		$this->assertSame($constraint, $constraint->referencing($value), 'Chainable');
		$this->assertSame($expected, $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Foreign::referencing
	 *
	 * @dataProvider    provider_referencing
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_referencing_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = new SQL_DDL_Constraint_Foreign;
		$constraint->referencing($value);

		$constraint->referencing(NULL);

		$this->assertSame('REFERENCES ""', $db->quote($constraint));
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
