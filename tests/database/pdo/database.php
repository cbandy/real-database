<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Database_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';
	protected $_column = 'value';

	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' ('.$column.' integer)');
		$db->execute_command('INSERT INTO '.$this->_table.' ('.$column.') VALUES (50)');
		$db->execute_command('INSERT INTO '.$this->_table.' ('.$column.') VALUES (55)');
		$db->execute_command('INSERT INTO '.$this->_table.' ('.$column.') VALUES (60)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_prepare()
	{
		$db = $this->sharedFixture;
		$statement = $db->prepare('SELECT * FROM '.$db->quote_table($this->_table));

		$this->assertType('PDOStatement', $statement);
	}

	public function test_prepare_command()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$command = $db->prepare_command('DELETE FROM '.$table);

		$this->assertType('Database_PDO_Command', $command, 'No parameters');
		$this->assertSame('DELETE FROM '.$table, (string) $command, 'No parameters');
		$this->assertSame(array(), $command->parameters, 'No parameters');

		$command = $db->prepare_command('DELETE FROM ? WHERE :cond', array(new Database_Table($this->_table), ':cond' => new Database_Conditions(new Database_Column($this->_column), '=', 60)));

		$this->assertType('Database_PDO_Command', $command, 'Parameters');
		$this->assertSame('DELETE FROM '.$table.' WHERE '.$column.' = ?', (string) $command, 'Parameters');
		$this->assertSame(array(1 => 60), $command->parameters, 'Parameters');
	}

	public function test_prepare_query()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$query = $db->prepare_query('SELECT * FROM '.$table);

		$this->assertType('Database_PDO_Query', $query, 'No parameters');
		$this->assertSame('SELECT * FROM '.$table, (string) $query, 'No parameters');
		$this->assertSame(array(), $query->parameters, 'No parameters');

		$query = $db->prepare_query('SELECT * FROM ? WHERE :cond', array(new Database_Table($this->_table), ':cond' => new Database_Conditions(new Database_Column($this->_column), '=', 60)));

		$this->assertType('Database_PDO_Query', $query, 'Parameters');
		$this->assertSame('SELECT * FROM '.$table.' WHERE '.$column.' = ?', (string) $query, 'Parameters');
		$this->assertSame(array(1 => 60), $query->parameters, 'Parameters');
	}
}
