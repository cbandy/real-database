<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_DDL_Create_Table_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_MySQL_DDL_Create_Table::if_not_exists
	 */
	public function test_if_not_exists()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Create_Table('a');

		$this->assertSame($command, $command->if_not_exists(), 'Chainable (void)');
		$this->assertSame("CREATE TABLE IF NOT EXISTS `a`", $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(FALSE), 'Chainable (FALSE)');
		$this->assertSame("CREATE TABLE `a`", $db->quote($command));

		$this->assertSame($command, $command->if_not_exists(TRUE), 'Chainable (TRUE)');
		$this->assertSame("CREATE TABLE IF NOT EXISTS `a`", $db->quote($command));
	}

	/**
	 * @covers  Database_MySQL_DDL_Create_Table::like
	 */
	public function test_like()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$command = new Database_MySQL_DDL_Create_Table('a');

		$this->assertSame($command, $command->like('b'));
		$this->assertSame("CREATE TABLE `a` LIKE `b`", $db->quote($command));
	}

	public function provider_options()
	{
		return array(
			array(NULL, 'CREATE TABLE ``'),

			array(
				array('a' => 'b'),
				"CREATE TABLE `` a 'b'",
			),
			array(
				array('ENGINE' => 'InnoDB'),
				"CREATE TABLE `` ENGINE 'InnoDB'",
			),
			array(
				array('AUTO_INCREMENT' => 5),
				'CREATE TABLE `` AUTO_INCREMENT 5',
			),
		);
	}

	/**
	 * @covers  Database_MySQL_DDL_Create_Table::options
	 *
	 * @dataProvider    provider_options
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_options($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$statement = new Database_MySQL_DDL_Create_Table;

		$this->assertSame($statement, $statement->options($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  Database_MySQL_DDL_Create_Table::options
	 *
	 * @dataProvider    provider_options
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_options_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => '`',
		)));
		$statement = new Database_MySQL_DDL_Create_Table;
		$statement->options($value);

		$statement->options(NULL);

		$this->assertSame('CREATE TABLE ``', $db->quote($statement));
	}

	/**
	 * @covers  Database_MySQL_DDL_Create_Table::__toString
	 */
	public function test_toString()
	{
		$command = new Database_MySQL_DDL_Create_Table;
		$command
			->temporary()
			->if_not_exists()
			->column(new SQL_DDL_Column('a', 'b'))
			->constraint(new SQL_DDL_Constraint_Primary(array('c')))
			->options(array('d' => 'e'))
			->query(new SQL_Expression('f'));

		$this->assertSame('CREATE TEMPORARY TABLE IF NOT EXISTS :name (:columns, :constraints) :options AS :query', (string) $command);

		$command->like('g');

		$this->assertSame('CREATE TEMPORARY TABLE IF NOT EXISTS :name LIKE :like', (string) $command);
	}
}
