<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Database_Test extends PHPUnit_Framework_TestCase
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

	public function test_copy_from()
	{
		$db = $this->sharedFixture;
		$db->copy_from($this->_table, array("8\t\\N", "9\t75"));

		$this->assertEquals(array(
			array('id' => 1, 'value' => 50),
			array('id' => 2, 'value' => 55),
			array('id' => 3, 'value' => 60),
			array('id' => 4, 'value' => 65),
			array('id' => 5, 'value' => 65),
			array('id' => 8, 'value' => NULL),
			array('id' => 9, 'value' => 75),
		), $db->execute_query('SELECT * FROM '.$db->quote_table($this->_table).' ORDER BY "id"')->as_array());
	}

	public function test_copy_to()
	{
		$db = $this->sharedFixture;
		$db->execute_command('INSERT INTO '.$db->quote_table($this->_table).' ("value") VALUES (NULL)');

		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t65\n", "5\t65\n", "6\t\\N\n"), $db->copy_to($this->_table));
	}

	public function provider_datatype()
	{
		return array
		(
			array('money', 'exact', TRUE),
			array('bytea', NULL, array('type' => 'binary')),
		);
	}

	/**
	 * @dataProvider provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	public function test_execute_command_query()
	{
		$db = $this->sharedFixture;

		$this->assertSame(5, $db->execute_command('SELECT * FROM '.$db->quote_table($this->_table)), 'Number of returned rows');
	}

	public function test_execute_compound_command()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertSame(2, $db->execute_command('DELETE FROM '.$table.' WHERE "id" = 3; DELETE FROM '.$table.' WHERE "id" = 5'), 'Total number of rows');

		try
		{
			// Connection should have no pending results
			$db->execute_query('SELECT * FROM '.$table);
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	public function test_execute_copy()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$this->assertSame(0, $db->execute_command('COPY '.$table.' TO STDOUT'));

		$this->assertNull($db->execute_query('COPY '.$table.' TO STDOUT'));
	}

	public function test_execute_prepared_command()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'UPDATE '.$table.' SET "value" = 20 WHERE "value" = 65');

		$this->assertSame(2, $db->execute_prepared_command($name));

		$name = $db->prepare(NULL, 'UPDATE '.$table.' SET "value" = $1 WHERE "value" = $2');

		$this->assertSame(1, $db->execute_prepared_command($name, array(20, 50)));
		$this->assertSame(3, $db->execute_prepared_command($name, array(30, 20)));

		try
		{
			$db->execute_prepared_command($name);
			$this->fail('Executing without the required parameters should raise a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	public function test_execute_prepared_query()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table.' WHERE "value" = $1');

		$result = $db->execute_prepared_query($name, array(60));

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Parameters (1)');
		$this->assertSame(1, $result->count(), 'Parameters (1)');
		$this->assertEquals(60, $result->get('value'));

		$result = $db->execute_prepared_query($name, array(50));

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Parameters (2)');
		$this->assertSame(1, $result->count(), 'Parameters (2)');
		$this->assertEquals(50, $result->get('value'));

		try
		{
			$db->execute_prepared_query($name);
			$this->fail('Executing without the required parameters should raise a Database_Exception');
		}
		catch (Database_Exception $e) {}

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table);

		$result = $db->execute_prepared_query($name);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'No parameters');
		$this->assertType('array', $result->current(), 'No parameters');

		$result = $db->execute_prepared_query($name, array(), FALSE);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Result type (FALSE)');
		$this->assertType('array', $result->current(), 'Result type (FALSE)');

		$result = $db->execute_prepared_query($name, array(), TRUE);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Result type (TRUE)');
		$this->assertType('stdClass', $result->current(), 'Result type (TRUE)');

		$result = $db->execute_prepared_query($name, array(), 'Database_PostgreSQL_Database_Test_Class');

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Result type (Database_PostgreSQL_Database_Test_Class)');
		$this->assertType('Database_PostgreSQL_Database_Test_Class', $result->current(), 'Result type (Database_PostgreSQL_Database_Test_Class)');
	}

	public function test_prepare()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table);

		$this->assertNotEquals('', $name, 'Returns a generated name');

		$result = $db->execute_query("SELECT * FROM pg_prepared_statements WHERE name = '$name'");
		$this->assertSame(1, $result->count(), 'Created successfully');
		$this->assertSame('f', $result->get('from_sql'), 'Definitely programmatic');

		$this->assertSame('asdf', $db->prepare('asdf', 'SELECT * FROM '.$table));
	}

	public function test_prepare_command()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$query = $db->prepare_command('DELETE FROM '.$table);

		$this->assertTrue($query instanceof Database_PostgreSQL_Command, 'No parameters');
		$this->assertSame('DELETE FROM '.$table, (string) $query, 'No parameters');
		$this->assertSame(array(), $query->parameters, 'No parameters');

		$query = $db->prepare_command('DELETE FROM ? WHERE :cond', array(new Database_Table($this->_table), ':cond' => new Database_Conditions(new Database_Column('value'), '=', 60)));

		$this->assertTrue($query instanceof Database_PostgreSQL_Command, 'Parameters');
		$this->assertSame('DELETE FROM '.$table.' WHERE "value" = $1', (string) $query, 'Parameters');
		$this->assertSame(array(60), $query->parameters, 'Parameters');
	}

	public function test_prepare_query()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$query = $db->prepare_query('SELECT * FROM '.$table);

		$this->assertTrue($query instanceof Database_PostgreSQL_Query, 'No parameters');
		$this->assertSame('SELECT * FROM '.$table, (string) $query, 'No parameters');
		$this->assertSame(array(), $query->parameters, 'No parameters');

		$query = $db->prepare_query('SELECT * FROM ? WHERE :cond', array(new Database_Table($this->_table), ':cond' => new Database_Conditions(new Database_Column('value'), '=', 60)));

		$this->assertTrue($query instanceof Database_PostgreSQL_Query, 'Parameters');
		$this->assertSame('SELECT * FROM '.$table.' WHERE "value" = $1', (string) $query, 'Parameters');
		$this->assertSame(array(60), $query->parameters, 'Parameters');
	}

	public function test_prepared_command_deallocate()
	{
		$db = $this->sharedFixture;
		$query = $db->prepare_command('DELETE FROM '.$db->quote_table($this->_table));

		$this->assertNull($query->deallocate());

		try
		{
			$query->deallocate();
			$this->fail('Calling deallocate() twice should fail with a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	public function test_prepared_query_deallocate()
	{
		$db = $this->sharedFixture;
		$query = $db->prepare_query('SELECT * FROM '.$db->quote_table($this->_table));

		$this->assertNull($query->deallocate());

		try
		{
			$query->deallocate();
			$this->fail('Calling deallocate() twice should fail with a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	public function test_quote_expression()
	{
		$db = $this->sharedFixture;
		$expression = new Database_Expression("SELECT :value::interval, 'yes':::type", array(':value' => '1 week', ':type' => new Database_Expression('boolean')));

		$this->assertSame("SELECT '1 week'::interval, 'yes'::boolean", $db->quote_expression($expression));
	}

	public function test_savepoint_transactions()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$delete = 'DELETE FROM '.$table;
		$select = 'SELECT * FROM '.$table;

		$this->assertSame(5, $db->execute_query($select)->count(), 'Initial');

		$db->begin();
		$db->execute_command($delete.' WHERE "value" = 65');

		$this->assertSame(3, $db->execute_query($select)->count(), 'Deleted 65');

		$this->assertNull($db->savepoint('test_savepoint'));

		$db->execute_command($delete.' WHERE "value" = 55');

		$this->assertSame(2, $db->execute_query($select)->count(), 'Deleted 55');

		$this->assertNull($db->rollback('test_savepoint'));

		$this->assertSame(3, $db->execute_query($select)->count(), 'Rollback 55');

		$this->assertNull($db->rollback());

		$this->assertSame(5, $db->execute_query($select)->count(), 'Rollback 65');
	}

	public function test_select()
	{
		$db = $this->sharedFixture;
		$query = $db->select(array('value'));

		$this->assertTrue($query instanceof Database_PostgreSQL_Select);

		$query->from(new Database_From($this->_table));

		$this->assertSame($query, $query->distinct(), 'Chainable (void)');
		$this->assertSame(4, $query->execute($db)->count(), 'Distinct (void)');

		$this->assertSame($query, $query->distinct(TRUE), 'Chainable (TRUE)');
		$this->assertSame(4, $query->execute($db)->count(), 'Distinct (TRUE)');

		$this->assertSame($query, $query->distinct(FALSE), 'Chainable (FALSE)');
		$this->assertSame(5, $query->execute($db)->count(), 'Not distinct');

		$this->assertSame($query, $query->distinct(array('value')), 'Chainable (column)');
		$this->assertSame(4, $query->execute($db)->count(), 'Distinct on column');

		$this->assertSame($query, $query->distinct(new Database_Expression('"value" % 10 = 0')), 'Chainable (expression)');
		$this->assertSame(2, $query->execute($db)->count(), 'Distinct on expression');
	}

	public function test_table_columns_no_table()
	{
		$db = $this->sharedFixture;
		$this->assertSame(array(), $db->table_columns('table-does-not-exist'));
	}
}

class Database_PostgreSQL_Database_Test_Class {}
