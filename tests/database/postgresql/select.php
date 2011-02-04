<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Select_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$db->execute_command(implode('; ', array(
			'CREATE TEMPORARY TABLE '.$table.' ("id" bigserial PRIMARY KEY, "value" integer)',
			'INSERT INTO '.$table.' ("value") VALUES (50)',
			'INSERT INTO '.$table.' ("value") VALUES (55)',
			'INSERT INTO '.$table.' ("value") VALUES (60)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
		)));
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @covers  Database_PostgreSQL_Select::distinct
	 */
	public function test_distinct()
	{
		$db = $this->sharedFixture;
		$query = $db->select(array('value'));

		$this->assertType('Database_PostgreSQL_Select', $query);

		$query->from(new SQL_Table_Reference($this->_table));

		$this->assertSame($query, $query->distinct(), 'Chainable (void)');
		$this->assertSame(4, $query->execute($db)->count(), 'Distinct (void)');

		$this->assertSame($query, $query->distinct(TRUE), 'Chainable (TRUE)');
		$this->assertSame(4, $query->execute($db)->count(), 'Distinct (TRUE)');

		$this->assertSame($query, $query->distinct(FALSE), 'Chainable (FALSE)');
		$this->assertSame(5, $query->execute($db)->count(), 'Not distinct');

		$this->assertSame($query, $query->distinct(array('value')), 'Chainable (column)');
		$this->assertSame(4, $query->execute($db)->count(), 'Distinct on column');

		$this->assertSame($query, $query->distinct(new SQL_Expression('"value" % 10 = 0')), 'Chainable (expression)');
		$this->assertSame(2, $query->execute($db)->count(), 'Distinct on expression');
	}
}
