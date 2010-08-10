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
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'MySQL')
			$this->markTestSkipped('Database not configured for MySQL');

		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (id bigint unsigned AUTO_INCREMENT PRIMARY KEY, value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50), (55), (60)');
	}

	public function tearDown()
	{
		if ( ! $this->_db)
			return;

		$this->_db->disconnect();
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
		$this->assertSame($expected, $this->_db->datatype($type, $attribute));
	}

	public function test_execute_command_query()
	{
		$this->assertSame(3, $this->_db->execute_command('SELECT * FROM '.$this->_table), 'Number of returned rows');
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_command()
	{
		$this->_db->execute_command('DELETE FROM '.$this->_table.'; DELETE FROM '.$this->_table);
	}

	/**
	 * @expectedException Database_Exception
	 */
	public function test_execute_compound_query()
	{
		$this->_db->execute_query('SELECT * FROM '.$this->_table.'; SELECT * FROM '.$this->_table);
	}

	public function test_execute_insert()
	{
		$this->assertSame(array(0,1), $this->_db->execute_insert(''), 'First identity from prior INSERT');
		$this->assertSame(array(1,4), $this->_db->execute_insert('INSERT INTO '.$this->_table.' (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$query = $this->_db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_Command_Insert_Identity);

		$query->identity('id')->values(array(65), array(70));

		$this->assertEquals(array(2,4), $query->execute($this->_db), 'AUTO_INCREMENT of the first row');

		$query->values(NULL)->values(array(75));

		$this->assertEquals(array(1,6), $query->execute($this->_db));

		$query->identity(NULL);

		$this->assertSame(1, $query->execute($this->_db));
	}

	public function test_table_columns_no_table()
	{
		$this->assertSame(array(), $this->_db->table_columns('table-does-not-exist'));
	}
}
