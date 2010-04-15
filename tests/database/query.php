<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.query
 */
class Database_Query_Test extends PHPUnit_Framework_TestCase
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

	public function test_execute()
	{
		$query = new Database_Query('SELECT * FROM '.$this->_table);

		$result = $query->execute($this->_db);

		$this->assertTrue($result instanceof Database_Result);
		$this->assertType('array', $result->current());
	}

	public function test_prepare()
	{
		$query = new Database_Query('SELECT * FROM '.$this->_table);

		$prepared = $query->prepare($this->_db);

		$this->assertTrue($prepared instanceof Database_Prepared_Query);
	}

	public function test_as_assoc()
	{
		$query = new Database_Query('SELECT * FROM '.$this->_table);

		$this->assertSame($query, $query->as_assoc(), 'Chainable');
		$this->assertType('array', $query->execute($this->_db)->current());
	}

	public function test_as_object()
	{
		$query = new Database_Query('SELECT * FROM '.$this->_table);

		$this->assertSame($query, $query->as_object(), 'Chainable (void)');
		$this->assertType('stdClass', $query->execute($this->_db)->current());

		$this->assertSame($query, $query->as_object(TRUE), 'Chainable (TRUE)');
		$this->assertType('stdClass', $query->execute($this->_db)->current());

		$this->assertSame($query, $query->as_object(FALSE), 'Chainable (FALSE)');
		$this->assertType('array', $query->execute($this->_db)->current());

		$this->assertSame($query, $query->as_object('Database_Query_Test_Class'), 'Chainable (Database_Query_Test_Class)');
		$this->assertType('Database_Query_Test_Class', $query->execute($this->_db)->current());
	}
}

class Database_Query_Test_Class {}
