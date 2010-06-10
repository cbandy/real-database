<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'PostgreSQL')
			$this->markTestSkipped('Database not configured for PostgreSQL');

		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' ("id" bigserial PRIMARY KEY, "value" integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' ("value") VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' ("value") VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' ("value") VALUES (60)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' ("value") VALUES (65)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' ("value") VALUES (65)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_copy_from()
	{
		$this->_db->copy_from('temp_test_table', array("8\t\\N", "9\t75"));

		$this->assertEquals(array(
			array('id' => 1, 'value' => 50),
			array('id' => 2, 'value' => 55),
			array('id' => 3, 'value' => 60),
			array('id' => 4, 'value' => 65),
			array('id' => 5, 'value' => 65),
			array('id' => 8, 'value' => NULL),
			array('id' => 9, 'value' => 75),
		), $this->_db->execute_query('SELECT * FROM '.$this->_table.' ORDER BY "id"')->as_array());
	}

	public function test_copy_to()
	{
		$this->_db->execute_command('INSERT INTO '.$this->_table.' ("value") VALUES (NULL)');

		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t65\n", "5\t65\n", "6\t\\N\n"), $this->_db->copy_to('temp_test_table'));
	}

	public function test_execute_command_query()
	{
		$this->assertSame(5, $this->_db->execute_command('SELECT * FROM '.$this->_table), 'Number of returned rows');
	}

	public function test_execute_copy()
	{
		$this->assertSame(0, $this->_db->execute_command('COPY '.$this->_table.' TO STDOUT'));

		$this->assertNull($this->_db->execute_query('COPY '.$this->_table.' TO STDOUT'));
	}

	public function test_execute_prepared_command()
	{
		$name = $this->_db->prepare(NULL, 'UPDATE '.$this->_table.' SET "value" = 20 WHERE "value" = 65');

		$this->assertSame(2, $this->_db->execute_prepared_command($name));

		$name = $this->_db->prepare(NULL, 'UPDATE '.$this->_table.' SET "value" = $1 WHERE "value" = $2');

		$this->assertSame(1, $this->_db->execute_prepared_command($name, array(20, 50)));
		$this->assertSame(3, $this->_db->execute_prepared_command($name, array(30, 20)));

		try
		{
			$this->_db->execute_prepared_command($name);
			$this->fail('Executing without the required parameters should raise a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	public function test_execute_prepared_query()
	{
		$name = $this->_db->prepare(NULL, 'SELECT * FROM '.$this->_table.' WHERE "value" = $1');

		$result = $this->_db->execute_prepared_query($name, array(60));

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Parameters (1)');
		$this->assertSame(1, $result->count(), 'Parameters (1)');
		$this->assertEquals(60, $result->get('value'));

		$result = $this->_db->execute_prepared_query($name, array(50));

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Parameters (2)');
		$this->assertSame(1, $result->count(), 'Parameters (2)');
		$this->assertEquals(50, $result->get('value'));

		try
		{
			$this->_db->execute_prepared_query($name);
			$this->fail('Executing without the required parameters should raise a Database_Exception');
		}
		catch (Database_Exception $e) {}

		$name = $this->_db->prepare(NULL, 'SELECT * FROM '.$this->_table);

		$result = $this->_db->execute_prepared_query($name);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'No parameters');
		$this->assertType('array', $result->current(), 'No parameters');

		$result = $this->_db->execute_prepared_query($name, array(), FALSE);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Result type (FALSE)');
		$this->assertType('array', $result->current(), 'Result type (FALSE)');

		$result = $this->_db->execute_prepared_query($name, array(), TRUE);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Result type (TRUE)');
		$this->assertType('stdClass', $result->current(), 'Result type (TRUE)');

		$result = $this->_db->execute_prepared_query($name, array(), 'Database_PostgreSQL_Test_Class');

		$this->assertTrue($result instanceof Database_PostgreSQL_Result, 'Result type (Database_PostgreSQL_Test_Class)');
		$this->assertType('Database_PostgreSQL_Test_Class', $result->current(), 'Result type (Database_PostgreSQL_Test_Class)');
	}

	public function test_prepare()
	{
		$name = $this->_db->prepare(NULL, 'SELECT * FROM '.$this->_table);

		$this->assertNotEquals('', $name, 'Returns a generated name');

		$result = $this->_db->execute_query("SELECT * FROM pg_prepared_statements WHERE name = '$name'");
		$this->assertSame(1, $result->count(), 'Created successfully');
		$this->assertSame('f', $result->get('from_sql'), 'Definitely programmatic');

		$this->assertSame('asdf', $this->_db->prepare('asdf', 'SELECT * FROM '.$this->_table));
	}

	public function test_prepare_command()
	{
		$query = $this->_db->prepare_command('DELETE FROM '.$this->_table);

		$this->assertTrue($query instanceof Database_PostgreSQL_Command, 'No parameters');
		$this->assertSame('DELETE FROM '.$this->_table, (string) $query, 'No parameters');
		$this->assertSame(array(), $query->parameters, 'No parameters');

		$query = $this->_db->prepare_command('DELETE FROM ? WHERE :cond', array(new Database_Table('temp_test_table'), ':cond' => new Database_Conditions(new Database_Column('value'), '=', 60)));

		$this->assertTrue($query instanceof Database_PostgreSQL_Command, 'Parameters');
		$this->assertSame('DELETE FROM '.$this->_table.' WHERE "value" = $1', (string) $query, 'Parameters');
		$this->assertSame(array(60), $query->parameters, 'Parameters');
	}

	public function test_prepare_query()
	{
		$query = $this->_db->prepare_query('SELECT * FROM '.$this->_table);

		$this->assertTrue($query instanceof Database_PostgreSQL_Query, 'No parameters');
		$this->assertSame('SELECT * FROM '.$this->_table, (string) $query, 'No parameters');
		$this->assertSame(array(), $query->parameters, 'No parameters');

		$query = $this->_db->prepare_query('SELECT * FROM ? WHERE :cond', array(new Database_Table('temp_test_table'), ':cond' => new Database_Conditions(new Database_Column('value'), '=', 60)));

		$this->assertTrue($query instanceof Database_PostgreSQL_Query, 'Parameters');
		$this->assertSame('SELECT * FROM '.$this->_table.' WHERE "value" = $1', (string) $query, 'Parameters');
		$this->assertSame(array(60), $query->parameters, 'Parameters');
	}

	public function test_prepared_command_deallocate()
	{
		$query = $this->_db->prepare_command('DELETE FROM '.$this->_table);

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
		$query = $this->_db->prepare_query('SELECT * FROM '.$this->_table);

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
		$expression = new Database_Expression("SELECT :value::interval, 'yes':::type", array(':value' => '1 week', ':type' => new Database_Expression('boolean')));

		$this->assertSame("SELECT '1 week'::interval, 'yes'::boolean", $this->_db->quote_expression($expression));
	}

	public function test_savepoint_transactions()
	{
		$select = 'SELECT * FROM '.$this->_table;

		$this->assertSame(5, $this->_db->execute_query($select)->count(), 'Initial');

		$this->_db->begin();
		$this->_db->execute_command('DELETE FROM '.$this->_table.' WHERE "value" = 65');

		$this->assertSame(3, $this->_db->execute_query($select)->count(), 'Deleted 65');

		$this->assertNull($this->_db->savepoint('test_savepoint'));

		$this->_db->execute_command('DELETE FROM '.$this->_table.' WHERE "value" = 55');

		$this->assertSame(2, $this->_db->execute_query($select)->count(), 'Deleted 55');

		$this->assertNull($this->_db->rollback('test_savepoint'));

		$this->assertSame(3, $this->_db->execute_query($select)->count(), 'Rollback 55');

		$this->assertNull($this->_db->rollback());

		$this->assertSame(5, $this->_db->execute_query($select)->count(), 'Rollback 65');
	}

	public function test_select()
	{
		$query = $this->_db->select(array('value'));

		$this->assertTrue($query instanceof Database_PostgreSQL_Select);

		$query->from(new Database_From('temp_test_table'));

		$this->assertSame($query, $query->distinct(), 'Chainable (void)');
		$this->assertSame(4, $query->execute($this->_db)->count(), 'Distinct (void)');

		$this->assertSame($query, $query->distinct(TRUE), 'Chainable (TRUE)');
		$this->assertSame(4, $query->execute($this->_db)->count(), 'Distinct (TRUE)');

		$this->assertSame($query, $query->distinct(FALSE), 'Chainable (FALSE)');
		$this->assertSame(5, $query->execute($this->_db)->count(), 'Not distinct');

		$this->assertSame($query, $query->distinct(array('value')), 'Chainable (column)');
		$this->assertSame(4, $query->execute($this->_db)->count(), 'Distinct on column');

		$this->assertSame($query, $query->distinct(new Database_Expression('"value" % 10 = 0')), 'Chainable (expression)');
		$this->assertSame(2, $query->execute($this->_db)->count(), 'Distinct on expression');
	}
}

class Database_PostgreSQL_Test_Class {}
