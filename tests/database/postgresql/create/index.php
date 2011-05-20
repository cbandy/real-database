<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Create_Index_Test extends PHPUnit_Framework_TestCase
{
	public function provider_column()
	{
		return array(
			array(array(NULL), 'CREATE INDEX "" ON "" ()'),
			array(array(NULL, 'any'), 'CREATE INDEX "" ON "" ()'),
			array(array(NULL, 'any', 'any'), 'CREATE INDEX "" ON "" ()'),

			array(
				array('a'),
				'CREATE INDEX "" ON "" ("a")',
			),
			array(
				array('a', 'b'),
				'CREATE INDEX "" ON "" ("a" B)',
			),
			array(
				array('a', 'b', 'c'),
				'CREATE INDEX "" ON "" ("a" B NULLS C)',
			),

			array(
				array(new SQL_Column('d')),
				'CREATE INDEX "" ON "" ("d")',
			),
			array(
				array(new SQL_Column('d'), 'e'),
				'CREATE INDEX "" ON "" ("d" E)',
			),
			array(
				array(new SQL_Column('d'), 'e', 'f'),
				'CREATE INDEX "" ON "" ("d" E NULLS F)',
			),

			array(
				array(new SQL_Expression('expr')),
				'CREATE INDEX "" ON "" ((expr))'
			),
			array(
				array(new SQL_Expression('expr'), 'f'),
				'CREATE INDEX "" ON "" ((expr) F)'
			),
			array(
				array(new SQL_Expression('expr'), 'f', 'g'),
				'CREATE INDEX "" ON "" ((expr) F NULLS G)'
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_column($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_PostgreSQL_Create_Index;

		$result = call_user_func_array(array($statement, 'column'), $arguments);

		$this->assertSame($statement, $result, 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_column_reset($arguments)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new Database_PostgreSQL_Create_Index;

		call_user_func_array(array($statement, 'column'), $arguments);

		$statement->column(NULL);

		$this->assertSame('CREATE INDEX "" ON "" ()', $db->quote($statement));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::tablespace
	 */
	public function test_tablespace()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->tablespace('c'));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () TABLESPACE "c"', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::using
	 */
	public function test_using()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->using('btree'));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' USING btree ()', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::where
	 */
	public function test_where()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->where(new SQL_Conditions(1)));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () WHERE 1', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::with
	 */
	public function test_with()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_PostgreSQL_Create_Index('a', 'b');
		$table = $db->quote_table('b');

		$this->assertSame($command, $command->with(array('FILLFACTOR' => 50)));
		$this->assertSame('CREATE INDEX "a" ON '.$table.' () WITH (FILLFACTOR = 50)', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Create_Index::__toString
	 */
	public function test_toString()
	{
		$command = new Database_PostgreSQL_Create_Index;
		$command
			->unique()
			->using('a')
			->with(array('b' => 'c'))
			->tablespace('d')
			->where(new SQL_Conditions);

		$this->assertSame('CREATE :type INDEX :name ON :table USING a (:columns) WITH (:with) TABLESPACE :tablespace WHERE :where', (string) $command);
	}
}
