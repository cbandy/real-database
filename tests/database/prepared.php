<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.prepared
 */
class Database_Prepared_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_command_execute()
	{
		$result = Database::command('DELETE FROM '.$this->_table.' WHERE 1 = 1')->prepare($this->_db)->execute();

		$this->assertSame(1, $result);
	}

	public function test_query_execute()
	{
		$result = Database::query('SELECT * FROM '.$this->_table)->prepare($this->_db)->execute();

		$this->assertTrue($result instanceof Database_Result);
		$this->assertType('array', $result->current());
	}

	public function test_query_as_assoc()
	{
		$prepared = Database::query('SELECT * FROM '.$this->_table)->prepare($this->_db);

		$this->assertSame($prepared, $prepared->as_assoc(), 'Chainable');
		$this->assertType('array', $prepared->execute()->current());
	}

	public function test_query_as_object()
	{
		$prepared = Database::query('SELECT * FROM '.$this->_table)->prepare($this->_db);

		$this->assertSame($prepared, $prepared->as_object(), 'Chainable (void)');
		$this->assertType('stdClass', $prepared->execute()->current());

		$this->assertSame($prepared, $prepared->as_object(TRUE), 'Chainable (TRUE)');
		$this->assertType('stdClass', $prepared->execute()->current());

		$this->assertSame($prepared, $prepared->as_object(FALSE), 'Chainable (FALSE)');
		$this->assertType('array', $prepared->execute()->current());

		$this->assertSame($prepared, $prepared->as_object('Database_Prepared_Test_Class'), 'Chainable (Database_Prepared_Test_Class)');
		$this->assertType('Database_Prepared_Test_Class', $prepared->execute()->current());
	}
}

class Database_Prepared_Test_Class {}
