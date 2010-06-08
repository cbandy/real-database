<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 * @group   database.postgresql.insert
 */
class Database_PostgreSQL_Insert_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'PostgreSQL')
			$this->markTestSkipped('Database not configured for PostgreSQL');

		$this->_db = Database::instance('testing');
		$this->_db->execute_command('CREATE TEMPORARY TABLE "temp_test_table" ("id" bigserial PRIMARY KEY, "value" integer)');
		$this->_db->execute_command('INSERT INTO "temp_test_table" ("value") VALUES (50)');
		$this->_db->execute_command('INSERT INTO "temp_test_table" ("value") VALUES (55)');
		$this->_db->execute_command('INSERT INTO "temp_test_table" ("value") VALUES (60)');
		$this->_db->execute_command('INSERT INTO "temp_test_table" ("value") VALUES (65)');
		$this->_db->execute_command('INSERT INTO "temp_test_table" ("value") VALUES (65)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_factory()
	{
		$this->assertType('Database_PostgreSQL_Insert', Database_PostgreSQL::insert());
		$this->assertTrue($this->_db->insert() instanceof Database_PostgreSQL_Insert);
	}

	public function test_identity()
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

	public function test_returning()
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

	public function test_as_assoc()
	{
		$query = $this->_db->insert('temp_test_table', array('value'))
			->values(array(75), array(80))
			->returning(array('id'));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('id' => 6), array('id' => 7)), $result->as_array(), 'Each column');
	}

	public function test_as_object()
	{
		$query = $this->_db->insert('temp_test_table', array('value'))
			->values(array(75), array(80))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array( (object) array('id' => 6), (object) array('id' => 7)), $result->as_array(), 'Each column');
	}
}
