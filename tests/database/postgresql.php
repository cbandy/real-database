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

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'PostgreSQL')
			$this->markTestSkipped('Database not configured for PostgreSQL');

		$this->_db = Database::instance('testing');
		$this->_db->execute_command('CREATE TEMPORARY TABLE "temp_test_table" ("id" bigserial PRIMARY KEY, "value" integer)');
		$this->_db->execute_command('INSERT INTO "temp_test_table" ("value") VALUES (50), (55), (60), (65), (65)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_delete()
	{
		$query = $this->_db->delete('temp_test_table');

		$this->assertTrue($query instanceof Database_PostgreSQL_Delete);

		$query->where(Database::conditions()->column(NULL, 'value', 'between', array(52,62)));

		$this->assertSame($query, $query->returning(array('more' => 'id')), 'Chainable (column)');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('more' => 2), array('more' => 3)), $result->as_array(), 'Each aliased column');

		$query->where(Database::conditions()->column(NULL, 'id', '=', 4));

		$this->assertSame($query, $query->returning(new Database_Expression('\'asdf\' AS "rawr"')), 'Chainable (expression)');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('rawr' => 'asdf')), $result->as_array());

		$query->where(NULL);

		$this->assertSame($query, $query->returning(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($this->_db));
	}

	public function test_delete_assoc()
	{
		$query = $this->_db->delete('temp_test_table')
			->where(Database::conditions()->column(NULL, 'value', 'between', array(52,62)))
			->returning(array('id'));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('id' => 2), array('id' => 3)), $result->as_array(), 'Each column');
	}

	public function test_delete_object()
	{
		$query = $this->_db->delete('temp_test_table')
			->where(Database::conditions()->column(NULL, 'value', 'between', array(52,62)))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object());

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array( (object) array('id' => 2), (object) array('id' => 3)), $result->as_array(), 'Each column');
	}

	public function test_execute_command_query()
	{
		$this->assertSame(5, $this->_db->execute_command('SELECT * FROM "temp_test_table"'), 'Number of returned rows');
	}

	public function test_execute_copy()
	{
		$this->assertSame(0, $this->_db->execute_command('COPY "temp_test_table" TO STDOUT'));

		$this->assertNull($this->_db->execute_query('COPY "temp_test_table" TO STDOUT'));
	}

	public function test_execute_prepared_command()
	{
		$name = $this->_db->prepare(NULL, 'UPDATE "temp_test_table" SET "value" = 20 WHERE "value" = 65');

		$this->assertSame(2, $this->_db->execute_prepared_command($name));

		$name = $this->_db->prepare(NULL, 'UPDATE "temp_test_table" SET "value" = $1 WHERE "value" = $2');

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
		$name = $this->_db->prepare(NULL, 'SELECT * FROM "temp_test_table" WHERE "value" = $1');

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

		$name = $this->_db->prepare(NULL, 'SELECT * FROM "temp_test_table"');

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

	public function test_insert()
	{
		$query = $this->_db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_PostgreSQL_Insert);
	}

	public function test_insert_identity()
	{
		$query = $this->_db->insert('temp_test_table', array('value'))
			->values(array(75), array(80));

		$this->assertSame($query, $query->identity('id'), 'Chainable (column)');
		$this->assertEquals(array(2,6), $query->execute($this->_db), 'Identity of the _first_ row');

		$this->assertSame($query, $query->identity(new Database_Expression("'asdf'")), 'Chainable (expression)');
		$this->assertEquals(array(2,'asdf'), $query->execute($this->_db), 'Expression result');

		$this->assertSame($query, $query->identity(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($this->_db), 'No identity');
	}

	public function test_insert_returning()
	{
		$query = $this->_db->insert('temp_test_table', array('value'))
			->values(array(75), array(80));

		$this->assertSame($query, $query->returning(array('more' => 'id')), 'Chainable (column)');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('more' => 6), array('more' => 7)), $result->as_array(), 'Each aliased column');

		$this->assertSame($query, $query->returning(new Database_Expression('\'asdf\' AS "rawr"')), 'Chainable (expression)');

		$result = $query->execute($this->_db);

		$this->assertEquals(array(array('rawr' => 'asdf'), array('rawr' => 'asdf')), $result->as_array(), 'Each expression');

		$this->assertSame($query, $query->returning(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($this->_db));
	}

	public function test_insert_assoc()
	{
		$query = $this->_db->insert('temp_test_table', array('value'))
			->values(array(75), array(80))
			->returning(array('id'));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('id' => 6), array('id' => 7)), $result->as_array(), 'Each column');
	}

	public function test_insert_object()
	{
		$query = $this->_db->insert('temp_test_table', array('value'))
			->values(array(75), array(80))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array( (object) array('id' => 6), (object) array('id' => 7)), $result->as_array(), 'Each column');
	}

	public function test_prepare()
	{
		$name = $this->_db->prepare(NULL, 'SELECT * FROM "temp_test_table"');

		$this->assertNotEquals('', $name, 'Returns a generated name');

		$result = $this->_db->execute_query("SELECT * FROM pg_prepared_statements WHERE name = '$name'");
		$this->assertSame(1, $result->count(), 'Created successfully');
		$this->assertSame('f', $result->get('from_sql'), 'Definitely programmatic');

		$this->assertSame('asdf', $this->_db->prepare('asdf', 'SELECT * FROM "temp_test_table"'));
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

	public function test_update()
	{
		$query = $this->_db->update('temp_test_table', NULL, array('value' => 100));

		$this->assertTrue($query instanceof Database_PostgreSQL_Update);

		$query->where(Database::conditions()->column(NULL, 'value', 'between', array(52,62)));

		$this->assertSame($query, $query->returning(array('more' => 'id')), 'Chainable (column)');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('more' => 2), array('more' => 3)), $result->as_array(), 'Each aliased column');

		$query->where(Database::conditions()->column(NULL, 'value', '=', 100));

		$this->assertSame($query, $query->returning(new Database_Expression('\'asdf\' AS "rawr"')), 'Chainable (expression)');

		$result = $query->execute($this->_db);

		$this->assertEquals(array(array('rawr' => 'asdf'), array('rawr' => 'asdf')), $result->as_array(), 'Each expression');

		$this->assertSame($query, $query->returning(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($this->_db));
	}

	public function test_update_assoc()
	{
		$query = $this->_db->update('temp_test_table', NULL, array('value' => 100))
			->where(Database::conditions()->column(NULL, 'value', 'between', array(52,62)))
			->returning(array('id'));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('id' => 2), array('id' => 3)), $result->as_array(), 'Each column');
	}

	public function test_update_object()
	{
		$query = $this->_db->update('temp_test_table', NULL, array('value' => 100))
			->where(Database::conditions()->column(NULL, 'value', 'between', array(52,62)))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array( (object) array('id' => 2), (object) array('id' => 3)), $result->as_array(), 'Each column');
	}
}

class Database_PostgreSQL_Test_Class {}
