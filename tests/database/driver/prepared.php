<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Driver_Prepared_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture;

		$db = Database::instance('testing');
		$table = $db->quote_table($this->_table);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' (value integer)');
		$db->execute_command('INSERT INTO '.$table.' (value) VALUES (50)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_command_execute()
	{
		$db = $this->sharedFixture;
		$result = Database::command('DELETE FROM '.$db->quote_table($this->_table).' WHERE 1 = 1')->prepare($db)->execute();

		$this->assertSame(1, $result);
	}

	public function test_query_execute()
	{
		$db = $this->sharedFixture;
		$result = Database::query('SELECT * FROM '.$db->quote_table($this->_table))->prepare($db)->execute();

		$this->assertType('Database_Result', $result);
		$this->assertType('array', $result->current());
	}

	public function test_query_as_assoc()
	{
		$db = $this->sharedFixture;
		$prepared = Database::query('SELECT * FROM '.$db->quote_table($this->_table))->prepare($db);

		$this->assertSame($prepared, $prepared->as_assoc(), 'Chainable');
		$this->assertType('array', $prepared->execute()->current());
	}

	public function test_query_as_object()
	{
		$db = $this->sharedFixture;
		$prepared = Database::query('SELECT * FROM '.$db->quote_table($this->_table))->prepare($db);

		$this->assertSame($prepared, $prepared->as_object(), 'Chainable (void)');
		$this->assertType('stdClass', $prepared->execute()->current());

		$this->assertSame($prepared, $prepared->as_object(TRUE), 'Chainable (TRUE)');
		$this->assertType('stdClass', $prepared->execute()->current());

		$this->assertSame($prepared, $prepared->as_object(FALSE), 'Chainable (FALSE)');
		$this->assertType('array', $prepared->execute()->current());

		$this->assertSame($prepared, $prepared->as_object('Database_Driver_Prepared_Test_Class'), 'Chainable (Database_Driver_Prepared_Test_Class)');
		$this->assertType('Database_Driver_Prepared_Test_Class', $prepared->execute()->current());
	}
}

class Database_Driver_Prepared_Test_Class {}
