<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.commands
 */
class Database_Base_Command_Insert_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Command_Insert::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame('INSERT INTO "pre_" DEFAULT VALUES',          $db->quote(new Database_Command_Insert));
		$this->assertSame('INSERT INTO "pre_a" DEFAULT VALUES',         $db->quote(new Database_Command_Insert('a')));
		$this->assertSame('INSERT INTO "pre_a" ("b") DEFAULT VALUES',   $db->quote(new Database_Command_Insert('a', array('b'))));
	}

	/**
	 * @covers  Database_Command_Insert::into
	 */
	public function test_into()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Insert;

		$this->assertSame($command, $command->into('a'), 'Chainable (string)');
		$this->assertSame('INSERT INTO "pre_a" DEFAULT VALUES', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Insert::columns
	 */
	public function test_columns()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Insert('a');

		$this->assertSame($command, $command->columns(array('b', new Database_Expression('c'), new Database_Column('d'))), 'Chainable (array)');
		$this->assertSame('INSERT INTO "pre_a" ("b", c, "d") DEFAULT VALUES', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Insert::values
	 */
	public function test_values()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Insert('a', array('b','c'));

		$this->assertSame($command, $command->values(array(0,1)), 'Chainable (array)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") VALUES (0, 1)', $db->quote($command));

		$this->assertSame($command, $command->values(new Database_Expression('d')), 'Chainable (Database_Expression)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") d', $db->quote($command));

		$this->assertSame($command, $command->values(NULL), 'Chainable (NULL)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") DEFAULT VALUES', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Insert::values
	 */
	public function test_values_arrays()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$command = new Database_Command_Insert('a', array('b','c'));

		$command->values(array(0,1));

		$this->assertSame($command, $command->values(array(2,3), array(4,5)), 'Chainable (array, array)');
		$this->assertSame('INSERT INTO "pre_a" ("b", "c") VALUES (0, 1), (2, 3), (4, 5)', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Insert::__toString
	 */
	public function test_toString()
	{
		$command = new Database_Command_Insert;
		$command
			->into('a')
			->columns(array('b'));

		$this->assertSame('INSERT INTO :table (:columns) DEFAULT VALUES', (string) $command);

		$command->values(array('c'));

		$this->assertSame('INSERT INTO :table (:columns) VALUES :values', (string) $command);

		$command->values(new Database_Expression('d'));

		$this->assertSame('INSERT INTO :table (:columns) :values', (string) $command);
	}
}
