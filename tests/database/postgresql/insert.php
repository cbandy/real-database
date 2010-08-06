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

		$table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$table.' ("id" bigserial PRIMARY KEY, "value" integer)');
		$this->_db->execute_command('INSERT INTO '.$table.' ("value") VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$table.' ("value") VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$table.' ("value") VALUES (60)');
		$this->_db->execute_command('INSERT INTO '.$table.' ("value") VALUES (65)');
		$this->_db->execute_command('INSERT INTO '.$table.' ("value") VALUES (65)');
	}

	public function tearDown()
	{
		if ( ! $this->_db)
			return;

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

		if ($this->_db->version() < '8.2')
		{
			try
			{
				$query->execute($this->_db);

				$this->setExpectedException('Database_Exception');
			}
			catch (Database_Exception $e) {}
		}
		else
		{
			$this->assertEquals(array(2,'asdf'), $query->execute($this->_db), 'Expression result');
		}

		$this->assertSame($query, $query->identity(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($this->_db), 'No identity');
	}

	public function test_identity_assigned()
	{
		$query = $this->_db->insert('temp_test_table', array('id', 'value'))
			->identity('id');

		$query->values(array(20, 75), array(21, 80));
		$this->assertEquals(array(2,20), $query->execute($this->_db), 'Identity of the first row (literal)');
		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t65\n", "5\t65\n", "20\t75\n", "21\t80\n"), $this->_db->copy_to('temp_test_table'));

		$query->values(NULL)->values(array(new Database_Expression('DEFAULT'), 85), array(30, 90));
		$this->assertEquals(array(2,6), $query->execute($this->_db), 'Identity of the first row (default)');
		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t65\n", "5\t65\n", "20\t75\n", "21\t80\n", "6\t85\n", "30\t90\n"), $this->_db->copy_to('temp_test_table'));
	}

	public function test_identity_query()
	{
		$query = $this->_db->insert('temp_test_table', array('value'))
			->values($this->_db->query('SELECT 75 as "value" UNION SELECT 80'))
			->identity('id');

		if ($this->_db->version() < '8.2')
		{
			$this->assertEquals(array(2,7), $query->execute($this->_db), 'Identity of the _last_ row');
		}
		else
		{
			$this->assertEquals(array(2,6), $query->execute($this->_db), 'Identity of the _first_ row');
		}
	}

	public function test_identity_table_expression()
	{
		if ($this->_db->version() < '8.2')
		{
			$this->setExpectedException('Database_Exception');
		}

		$result = $this->_db->insert(new Database_Expression($this->_db->quote_table('temp_test_table')))
			->columns(array('id', 'value'))
			->values(array(20, 75), array(21, 80))
			->identity('id')
			->execute($this->_db);

		$this->assertEquals(array(2,20), $result, 'Identity of the first row');
	}

	public function test_identity_without_columns()
	{
		if ($this->_db->version() < '8.2')
		{
			$this->setExpectedException('Database_Exception');
		}

		$result = $this->_db->insert('temp_test_table')
			->values(array(20, 75), array(21, 80))
			->identity('id')
			->execute($this->_db);

		$this->assertEquals(array(2,20), $result, 'Identity of the first row');
	}

	public function test_returning()
	{
		if ($this->_db->version() < '8.2')
			$this->markTestSkipped('Not supported');

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
		if ($this->_db->version() < '8.2')
			$this->markTestSkipped('Not supported');

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
		if ($this->_db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$query = $this->_db->insert('temp_test_table', array('value'))
			->values(array(75), array(80))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object(), 'Chainable');

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_PostgreSQL_Result);
		$this->assertEquals(array( (object) array('id' => 6), (object) array('id' => 7)), $result->as_array(), 'Each column');
	}
}
