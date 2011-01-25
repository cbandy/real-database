<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_DDL_Column_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DDL_Column::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame('"a" b', $db->quote(new SQL_DDL_Column('a', 'b')));
	}

	/**
	 * @covers  SQL_DDL_Column::name
	 */
	public function test_name()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new SQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->name('c'));
		$this->assertSame('"c" b', $db->quote($column));
	}

	/**
	 * @covers  SQL_DDL_Column::type
	 */
	public function test_type()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new SQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->type('c'));
		$this->assertSame('"a" c', $db->quote($column));
	}

	/**
	 * @covers  SQL_DDL_Column::set_default
	 */
	public function test_set_default()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new SQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->set_default(1));
		$this->assertSame('"a" b DEFAULT 1', $db->quote($column));
	}

	/**
	 * @covers  SQL_DDL_Column::no_default
	 */
	public function test_no_default()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new SQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->no_default());
		$this->assertSame('"a" b', $db->quote($column));

		$column->set_default(1);
		$this->assertSame('"a" b DEFAULT 1', $db->quote($column));

		$column->no_default();
		$this->assertSame('"a" b', $db->quote($column));
	}

	/**
	 * @covers  SQL_DDL_Column::not_null
	 */
	public function test_not_null()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new SQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->not_null(), 'Chainable (void)');
		$this->assertSame('"a" b NOT NULL', $db->quote($column));

		$this->assertSame($column, $column->not_null(FALSE), 'Chainable (FALSE)');
		$this->assertSame('"a" b', $db->quote($column));

		$this->assertSame($column, $column->not_null(TRUE), 'Chainable (TRUE)');
		$this->assertSame('"a" b NOT NULL', $db->quote($column));
	}

	/**
	 * @covers  SQL_DDL_Column::constraint
	 */
	public function test_constraint()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new SQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->constraint(new SQL_DDL_Constraint_Unique), 'Chainable (unique)');
		$this->assertSame('"a" b UNIQUE', $db->quote($column));

		$this->assertSame($column, $column->constraint(new SQL_DDL_Constraint_Check(1)), 'Chainable (check)');
		$this->assertSame('"a" b UNIQUE CHECK (1)', $db->quote($column));

		$this->assertSame($column, $column->constraint(NULL), 'Chainable (NULL)');
		$this->assertSame('"a" b', $db->quote($column));
	}

	/**
	 * @covers  SQL_DDL_Column::__toString
	 */
	public function test_toString()
	{
		$column = new SQL_DDL_Column;
		$column
			->name('a')
			->type('b')
			->set_default('c')
			->not_null()
			->constraint(new SQL_DDL_Constraint_Unique);

		$this->assertSame(':name :type DEFAULT :default NOT NULL :constraints', (string) $column);
	}
}
