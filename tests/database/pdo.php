<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if (strncmp($config['type'], 'PDO', 3))
			$this->markTestSkipped('Database not configured for PDO');

		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');
		$this->_column = $this->_db->quote_column('value');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (55)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (60)');
	}

	public function tearDown()
	{
		if ( ! $this->_db)
			return;

		$this->_db->disconnect();
	}

	public function test_prepare()
	{
		$statement = $this->_db->prepare('SELECT * FROM '.$this->_table);

		$this->assertTrue($statement instanceof PDOStatement);
	}

	public function test_prepare_command()
	{
		$query = $this->_db->prepare_command('DELETE FROM '.$this->_table);

		$this->assertTrue($query instanceof Database_PDO_Command, 'No parameters');
		$this->assertSame('DELETE FROM '.$this->_table, (string) $query, 'No parameters');
		$this->assertSame(array(), $query->parameters, 'No parameters');

		$query = $this->_db->prepare_command('DELETE FROM ? WHERE :cond', array(new Database_Table('temp_test_table'), ':cond' => new Database_Conditions(new Database_Column('value'), '=', 60)));

		$this->assertTrue($query instanceof Database_PDO_Command, 'Parameters');
		$this->assertSame('DELETE FROM '.$this->_table.' WHERE '.$this->_column.' = ?', (string) $query, 'Parameters');
		$this->assertSame(array(1 => 60), $query->parameters, 'Parameters');
	}

	public function test_prepare_query()
	{
		$query = $this->_db->prepare_query('SELECT * FROM "temp_test_table"');

		$this->assertTrue($query instanceof Database_PDO_Query, 'No parameters');
		$this->assertSame('SELECT * FROM '.$this->_table, (string) $query, 'No parameters');
		$this->assertSame(array(), $query->parameters, 'No parameters');

		$query = $this->_db->prepare_query('SELECT * FROM ? WHERE :cond', array(new Database_Table('temp_test_table'), ':cond' => new Database_Conditions(new Database_Column('value'), '=', 60)));

		$this->assertTrue($query instanceof Database_PDO_Query, 'Parameters');
		$this->assertSame('SELECT * FROM '.$this->_table.' WHERE '.$this->_column.' = ?', (string) $query, 'Parameters');
		$this->assertSame(array(1 => 60), $query->parameters, 'Parameters');
	}
}
