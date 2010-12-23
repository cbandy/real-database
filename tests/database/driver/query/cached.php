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
