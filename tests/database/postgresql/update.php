<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 * @group   database.postgresql.update
 */
class Database_PostgreSQL_Update_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table('temp_test_table');

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' ("id" bigserial PRIMARY KEY, "value" integer)');
		$db->execute_command('INSERT INTO '.$table.' ("value") VALUES (50)');
		$db->execute_command('INSERT INTO '.$table.' ("value") VALUES (55)');
		$db->execute_command('INSERT INTO '.$table.' ("value") VALUES (60)');
		$db->execute_command('INSERT INTO '.$table.' ("value") VALUES (65)');
		$db->execute_command('INSERT INTO '.$table.' ("value") VALUES (65)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_factory()
	{
		$db = $this->sharedFixture;

		$this->assertType('Database_PostgreSQL_Update', Database_PostgreSQL::update());
		$this->assertTrue($db->update() instanceof Database_PostgreSQL_Update);
	}

	public function test_returning()
	{
		$db = $this->sharedFixture;
		$query = $db->update('temp_test_table', NULL, array('value' => 100))
			->where('value', 'between', array(52,62));

		$this->assertSame($query, $query->returning(array('more' => 'id')), 'Chainable (column)');

		$result = $query->execute($db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('more' => 2), array('more' => 3)), $result->as_array(), 'Each aliased column');

		$query->where('value', '=', 100);

		$this->assertSame($query, $query->returning(new Database_Expression('\'asdf\' AS "rawr"')), 'Chainable (expression)');

		$result = $query->execute($db);

		$this->assertEquals(array(array('rawr' => 'asdf'), array('rawr' => 'asdf')), $result->as_array(), 'Each expression');

		$this->assertSame($query, $query->returning(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($db));
	}

	public function test_as_assoc()
	{
		$db = $this->sharedFixture;
		$query = $db->update('temp_test_table', NULL, array('value' => 100))
			->where('value', 'between', array(52,62))
			->returning(array('id'));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');

		$result = $query->execute($db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array(array('id' => 2), array('id' => 3)), $result->as_array(), 'Each column');
	}

	public function test_as_object()
	{
		$db = $this->sharedFixture;
		$query = $db->update('temp_test_table', NULL, array('value' => 100))
			->where('value', 'between', array(52,62))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object(), 'Chainable');

		$result = $query->execute($db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array( (object) array('id' => 2), (object) array('id' => 3)), $result->as_array(), 'Each column');
	}
}
