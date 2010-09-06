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
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('`a` b', $db->quote(new Database_MySQL_DDL_Column('a', 'b')));
	}

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

	public function test_comment()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->comment('c'));
		$this->assertSame("`a` b COMMENT 'c'", $db->quote($column));
	}

	public function test_set_default()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->set_default(1));
		$this->assertSame('`a` b DEFAULT 1', $db->quote($column));
	}

	public function test_no_default()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->no_default());
		$this->assertSame('`a` b', $db->quote($column));

		$column->set_default(1);
		$this->assertSame('`a` b DEFAULT 1', $db->quote($column));

		$column->no_default();
		$this->assertSame('`a` b', $db->quote($column));
	}

	public function test_not_null()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->not_null(), 'Chainable (void)');
		$this->assertSame('`a` b NOT NULL', $db->quote($column));

		$this->assertSame($column, $column->not_null(FALSE), 'Chainable (FALSE)');
		$this->assertSame('`a` b', $db->quote($column));

		$this->assertSame($column, $column->not_null(TRUE), 'Chainable (TRUE)');
		$this->assertSame('`a` b NOT NULL', $db->quote($column));
	}

	public function test_constraint()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->constraint(new Database_DDL_Constraint_Unique), 'Chainable (unique)');
		$this->assertSame('`a` b UNIQUE', $db->quote($column));

		$this->assertSame($column, $column->constraint(new Database_DDL_Constraint_Check(1)), 'Chainable (check)');
		$this->assertSame('`a` b UNIQUE', $db->quote($column));

		$this->assertSame($column, $column->constraint(new Database_DDL_Constraint_Foreign('c', array('d'))), 'Chainable (foreign)');
		$this->assertSame('`a` b UNIQUE REFERENCES `c` (`d`)', $db->quote($column));

		$this->assertSame($column, $column->constraint(NULL), 'Chainable (NULL)');
		$this->assertSame('`a` b', $db->quote($column));
	}

	public function test_identity()
	{
		$db = $this->sharedFixture;
		$column = new Database_MySQL_DDL_Column('a', 'b');

		$this->assertSame($column, $column->identity());
		$this->assertSame('`a` b AUTO_INCREMENT PRIMARY KEY', $db->quote($column));
	}
}
