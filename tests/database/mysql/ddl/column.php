<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DDL_Column_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_MySQL_DDL_Column::auto_increment
	 */
	public function test_auto_increment()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->auto_increment(), 'Chainable (void)');
		$this->assertSame('`a` b AUTO_INCREMENT', $db->quote($column));

		$this->assertSame($column, $column->auto_increment(FALSE), 'Chainable (FALSE)');
		$this->assertSame('`a` b', $db->quote($column));

		$this->assertSame($column, $column->auto_increment(TRUE), 'Chainable (TRUE)');
		$this->assertSame('`a` b AUTO_INCREMENT', $db->quote($column));
	}

	/**
	 * @covers  Database_MySQL_DDL_Column::comment
	 */
	public function test_comment()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->comment('c'));
		$this->assertSame("`a` b COMMENT 'c'", $db->quote($column));
	}

	/**
	 * @covers  Database_MySQL_DDL_Column::constraint
	 */
	public function test_constraint()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->constraint(new SQL_DDL_Constraint_Unique), 'Chainable (unique)');
		$this->assertSame('`a` b UNIQUE', $db->quote($column));

		$this->assertSame($column, $column->constraint(new SQL_DDL_Constraint_Check(1)), 'Chainable (check)');
		$this->assertSame('`a` b UNIQUE', $db->quote($column));

		$this->assertSame($column, $column->constraint(new SQL_DDL_Constraint_Foreign('c', array('d'))), 'Chainable (foreign)');
		$this->assertSame('`a` b UNIQUE REFERENCES '.$db->quote_table('c').' (`d`)', $db->quote($column));

		$this->assertSame($column, $column->constraint(NULL), 'Chainable (NULL)');
		$this->assertSame('`a` b', $db->quote($column));
	}

	/**
	 * @covers  Database_MySQL_DDL_Column::identity
	 */
	public function test_identity()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->identity());
		$this->assertSame('`a` b AUTO_INCREMENT PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_MySQL_DDL_Column::__toString
	 */
	public function test_toString()
	{
		$column = new Database_MySQL_DDL_Column;
		$column
			->name('a')
			->type('b')
			->not_null()
			->set_default(NULL)
			->auto_increment()
			->constraint(new SQL_DDL_Constraint_Unique)
			->comment('c')
			->constraint(new SQL_DDL_Constraint_Foreign);

		$this->assertSame(':name :type NOT NULL DEFAULT :default AUTO_INCREMENT :unique COMMENT :comment :foreign', (string) $column);
	}
}
