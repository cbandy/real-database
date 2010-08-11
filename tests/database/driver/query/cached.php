<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Driver_Query_Cached_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' (value integer)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (50)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_delete()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query('SELECT * FROM '.$db->quote_table($this->_table));
		$class = get_class($query->execute($db));

		$cached = new Database_Query_Cached(5, $db, $query);

		// Cache the result
		$cached->execute();

		// Clear the cache
		$cached->delete();
		$this->assertType($class, $cached->execute(), 'Not cached');
	}

	public function test_execute()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query('SELECT * FROM '.$db->quote_table($this->_table));
		$class = get_class($query->execute($db));

		$cached = new Database_Query_Cached(5, $db, $query);

		// Clear the cache
		$cached->delete();

		$this->assertType($class, $cached->execute(), 'First execution not cached');
		$this->assertType('Database_Result_Array', $cached->execute(), 'Second execution cached');
	}

	public function test_as_assoc()
	{
		$db = $this->sharedFixture;
		$cached = new Database_Query_Cached(5, $db, new Database_Query('SELECT * FROM '.$db->quote_table($this->_table)));

		// Clear the cache
		$cached->delete();

		$this->assertSame($cached, $cached->as_assoc(), 'Chainable');
		$this->assertType('array', $cached->execute()->current(), 'Array result');
		$this->assertType('array', $cached->execute()->current(), 'Array result, cached');
	}

	public function test_as_object()
	{
		$db = $this->sharedFixture;
		$cached = new Database_Query_Cached(5, $db, new Database_Query('SELECT * FROM '.$db->quote_table($this->_table)));

		// Clear the cache
		$cached->delete();

		$this->assertSame($cached, $cached->as_object(), 'Chainable (void)');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result, cached');

		// Clear the cache
		$cached->delete();

		$this->assertSame($cached, $cached->as_object(TRUE), 'Chainable (TRUE)');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result, cached');

		// Clear the cache
		$cached->delete();

		$this->assertSame($cached, $cached->as_object(FALSE), 'Chainable (FALSE)');
		$this->assertType('array', $cached->execute()->current(), 'Array result');
		$this->assertType('array', $cached->execute()->current(), 'Array result, cached');

		// Clear the cache
		$cached->delete();

		$this->assertSame($cached, $cached->as_object('Database_Driver_Query_Cached_Test_Class'), 'Chainable (Database_Driver_Query_Cached_Test_Class)');
		$this->assertType('Database_Driver_Query_Cached_Test_Class', $cached->execute()->current(), 'Class result');
		$this->assertType('Database_Driver_Query_Cached_Test_Class', $cached->execute()->current(), 'Class result, cached');
	}

	public function test_prepared()
	{
		$db = $this->sharedFixture;
		$query = $db->prepare_query('SELECT * FROM '.$db->quote_table($this->_table));
		$class = get_class($query->execute());

		$cached = new Database_Query_Cached(5, $db, $query);

		// Clear the cache
		$cached->delete();

		$this->assertType($class, $cached->execute(), 'First execution not cached');
		$this->assertType('Database_Result_Array', $cached->execute(), 'Second execution cached');
	}
}

class Database_Driver_Query_Cached_Test_Class {}
