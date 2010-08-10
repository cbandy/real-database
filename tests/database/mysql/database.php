<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.mysql
 */
class Database_MySQL_Database_Test extends PHPUnit_Framework_TestCase
{
	protected $_table;

	public function setUp()
	{
		$db = $this->sharedFixture;
		$this->_table = $db->quote_table('temp_test_table');

		$db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (id bigint unsigned AUTO_INCREMENT PRIMARY KEY, value integer)');
		$db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50), (55), (60)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function provider_datatype()
	{
		return array
		(
			array('tinyint unsigned zerofill', NULL, array('type' => 'integer', 'min' => '0', 'max' => '255')),
			array('point', NULL, array('type' => 'binary')),
		);
	}

	/**
	 * @dataProvider provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	public function test_execute_command_query()
	{
		$db = $this->sharedFixture;

		$this->assertSame(3, $db->execute_command('SELECT * FROM '.$this->_table), 'Number of returned rows');
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_command()
	{
		$db = $this->sharedFixture;

		$db->execute_command('DELETE FROM '.$this->_table.'; DELETE FROM '.$this->_table);
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_query()
	{
		$db = $this->sharedFixture;

		$db->execute_query('SELECT * FROM '.$this->_table.'; SELECT * FROM '.$this->_table);
	}

	public function test_execute_insert()
	{
		$db = $this->sharedFixture;

		$this->assertSame(array(0,1), $db->execute_insert(''), 'First identity from prior INSERT');
		$this->assertSame(array(1,4), $db->execute_insert('INSERT INTO '.$this->_table.' (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$db = $this->sharedFixture;
		$query = $db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_Command_Insert_Identity);

		$query->identity('id')->values(array(65), array(70));

		$this->assertEquals(array(2,4), $query->execute($db), 'AUTO_INCREMENT of the first row');

		$query->values(NULL)->values(array(75));

		$this->assertEquals(array(1,6), $query->execute($db));

		$query->identity(NULL);

		$this->assertSame(1, $query->execute($db));
	}

	public function test_table_columns_no_table()
	{
		$db = $this->sharedFixture;

		$this->assertSame(array(), $db->table_columns('table-does-not-exist'));
	}
}
