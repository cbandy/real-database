<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_Insert_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_SQLite_Insert::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$table = $db->quote_table('a');

		$this->assertSame('INSERT INTO '.$table.' DEFAULT VALUES', $db->quote(new Database_SQLite_Insert('a')));
		$this->assertSame('INSERT INTO '.$table.' ("b") DEFAULT VALUES', $db->quote(new Database_SQLite_Insert('a', array('b'))));
	}

	/**
	 * @covers  Database_SQLite_Insert::values
	 */
	public function test_values()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$command = new Database_SQLite_Insert('a');
		$table = $db->quote_table('a');

		$this->assertSame($command, $command->values(array('b')), 'Chainable (one array)');
		$this->assertSame('INSERT INTO '.$table." VALUES ('b');", $db->quote($command));

		$this->assertSame($command, $command->values(new SQL_Expression('c')), 'Chainable (expression)');
		$this->assertSame('INSERT INTO '.$table.' c', $db->quote($command));

		$this->assertSame($command, $command->values(array('c'), array('d')), 'Chainable (two arrays)');
		$this->assertSame('INSERT INTO '.$table." VALUES ('b');INSERT INTO ".$table." VALUES ('c');INSERT INTO ".$table." VALUES ('d');", $db->quote($command));

		$this->assertSame($command, $command->values(NULL), 'Chainable (NULL)');
		$this->assertSame('INSERT INTO '.$table.' DEFAULT VALUES', $db->quote($command));

		$this->assertSame($command, $command->values(array('e'), array('f'), array('g')), 'Chainable (three arrays)');
		$this->assertSame('INSERT INTO '.$table." VALUES ('e');INSERT INTO ".$table." VALUES ('f');INSERT INTO ".$table." VALUES ('g');", $db->quote($command));
	}

	/**
	 * @covers  Database_SQLite_Insert::__toString
	 */
	public function test_toString()
	{
		$command = new Database_SQLite_Insert;
		$command
			->into('a')
			->columns(array('b'));

		$this->assertSame('INSERT INTO :table (:columns) DEFAULT VALUES', (string) $command);

		$command->values(array('c'));

		$this->assertSame('INSERT INTO :table (:columns) VALUES ?;', (string) $command);
	}
}
