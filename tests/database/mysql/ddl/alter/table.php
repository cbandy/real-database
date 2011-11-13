<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DDL_Alter_Table_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_MySQL_DDL_Alter_Table::_position
	 * @covers  Database_MySQL_DDL_Alter_Table::add_column
	 */
	public function test_add_column()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->add_column(new SQL_DDL_Column('b', 'c')), 'Chainable (column)');
		$this->assertSame("ALTER TABLE `a` ADD `b` c", $db->quote($command));

		$this->assertSame($command, $command->add_column(new SQL_DDL_Column('e', 'f'), TRUE), 'Chainable (column, TRUE)');
		$this->assertSame("ALTER TABLE `a` ADD `b` c, ADD `e` f FIRST", $db->quote($command));

		$this->assertSame($command, $command->add_column(new SQL_DDL_Column('g', 'h'), 'i'), 'Chainable (column, string)');
		$this->assertSame("ALTER TABLE `a` ADD `b` c, ADD `e` f FIRST, ADD `g` h AFTER `i`", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_DDL_Alter_Table::_position
	 * @covers  Database_MySQL_DDL_Alter_Table::change_column
	 */
	public function test_change_column()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->change_column('b', new SQL_DDL_Column('c', 'd')), 'Chainable (string, column)');
		$this->assertSame("ALTER TABLE `a` CHANGE `b` `c` d", $db->quote($command));

		$this->assertSame($command, $command->change_column('e', new SQL_DDL_Column('f', 'g'), TRUE), 'Chainable (string, column, TRUE)');
		$this->assertSame("ALTER TABLE `a` CHANGE `b` `c` d, CHANGE `e` `f` g FIRST", $db->quote($command));

		$this->assertSame($command, $command->change_column('h', new SQL_DDL_Column('i', 'j'), 'k'), 'Chainable (string, column, string)');
		$this->assertSame("ALTER TABLE `a` CHANGE `b` `c` d, CHANGE `e` `f` g FIRST, CHANGE `h` `i` j AFTER `k`", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_DDL_Alter_Table::drop_constraint
	 */
	public function test_drop_constraint()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->drop_constraint('primary', 'b'), 'Chainable (primary)');
		$this->assertSame("ALTER TABLE `a` DROP PRIMARY KEY", $db->quote($command));

		$this->assertSame($command, $command->drop_constraint('foreign', 'c'), 'Chainable (foreign)');
		$this->assertSame("ALTER TABLE `a` DROP PRIMARY KEY, DROP FOREIGN KEY `c`", $db->quote($command));

		$this->assertSame($command, $command->drop_constraint('unique', 'd'), 'Chainable (unique)');
		$this->assertSame("ALTER TABLE `a` DROP PRIMARY KEY, DROP FOREIGN KEY `c`, DROP INDEX `d`", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_DDL_Alter_Table::option
	 */
	public function test_option()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Alter_Table('a');

		$this->assertSame($command, $command->option('ENGINE', 'InnoDB'));
		$this->assertSame("ALTER TABLE `a` ENGINE 'InnoDB'", $db->quote($command));
	}
}
