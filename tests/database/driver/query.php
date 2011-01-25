<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_Driver_Query_Test extends PHPUnit_Framework_TestCase
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

	public function test_execute()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query('SELECT * FROM '.$db->quote_table($this->_table));

		$result = $query->execute($db);

		$this->assertType('Database_Result', $result);
		$this->assertType('array', $result->current());
	}

	public function test_as_assoc()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query('SELECT * FROM '.$db->quote_table($this->_table));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');
		$this->assertType('array', $query->execute($db)->current());
	}

	public function test_as_object()
	{
		$db = $this->sharedFixture;
		$query = new Database_Query('SELECT * FROM '.$db->quote_table($this->_table));

		$this->assertSame($query, $query->as_object(), 'Chainable (void)');
		$this->assertType('stdClass', $query->execute($db)->current());

		$this->assertSame($query, $query->as_object(TRUE), 'Chainable (TRUE)');
		$this->assertType('stdClass', $query->execute($db)->current());

		$this->assertSame($query, $query->as_object(FALSE), 'Chainable (FALSE)');
		$this->assertType('array', $query->execute($db)->current());

		$this->assertSame($query, $query->as_object('Database_Driver_Query_Test_Class'), 'Chainable (Database_Driver_Query_Test_Class)');
		$this->assertType('Database_Driver_Query_Test_Class', $query->execute($db)->current());
	}
}

class Database_Driver_Query_Test_Class {}
