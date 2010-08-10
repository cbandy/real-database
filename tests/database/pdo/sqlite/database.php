<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo_sqlite
 */
class Database_PDO_SQLite_Database_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'PDO_SQLite')
			$this->markTestSkipped('Database not configured for SQLite using PDO');

		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (id INTEGER PRIMARY KEY, value INTEGER)');
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

	public function test_execute_command_query()
	{
		$this->assertSame(1, $this->_db->execute_command('SELECT * FROM '.$this->_table), 'Always one');
		$this->assertSame(1, $this->_db->execute_command('DELETE FROM '.$this->_table.' WHERE "id" = 1; SELECT * FROM '.$this->_table), 'Compound, always one');
	}

	public function test_execute_compound_command()
	{
		// All statements executed
		$this->assertSame(2, $this->_db->execute_command('DELETE FROM '.$this->_table.' WHERE "id" = 1; DELETE FROM '.$this->_table), 'Count of last statement');
	}

	public function test_execute_compound_command_mixed()
	{
		$this->assertSame(3, $this->_db->execute_command('SELECT * FROM '.$this->_table.' WHERE value < 60; DELETE FROM '.$this->_table), 'Count of last statement');
	}

	public function test_execute_compound_query()
	{
		$result = $this->_db->execute_query('SELECT * FROM '.$this->_table.' WHERE value < 60; SELECT * FROM '.$this->_table.' WHERE value < 70');

		$this->assertType('Database_Result', $result);
		$this->assertSame(2, count($result), 'First result');
		$this->assertEquals(array(50, 55), $result->as_array(NULL, 'value'), 'First result');

		$this->assertType('Database_Result', $this->_db->execute_query('SELECT * FROM '.$this->_table.' WHERE value < 60; DELETE FROM '.$this->_table));
		$this->assertEquals(3, $this->_db->execute_query('SELECT COUNT(*) FROM '.$this->_table)->get(), 'Second statement is not executed');

		$this->assertNull($this->_db->execute_query('DELETE FROM '.$this->_table.' WHERE value = 50; DELETE FROM '.$this->_table.' WHERE value = 55; SELECT * FROM '.$this->_table));
		$this->assertEquals(2, $this->_db->execute_query('SELECT COUNT(*) FROM '.$this->_table)->get(), 'Only the first statement is executed');
	}

	public function test_execute_compound_query_mixed()
	{
		$this->assertType('Database_Result', $this->_db->execute_query('SELECT * FROM '.$this->_table.' WHERE value < 60; DELETE FROM '.$this->_table));

		$this->assertEquals(3, $this->_db->execute_query('SELECT COUNT(*) FROM '.$this->_table)->get(), 'Second statement is not executed');
	}

	public function test_execute_insert()
	{
		$this->assertEquals(array(0,3), $this->_db->execute_insert(''), 'Prior identity');
		$this->assertEquals(array(1,4), $this->_db->execute_insert('INSERT INTO '.$this->_table.' (value) VALUES (65)'));
	}

	public function test_insert()
	{
		$query = $this->_db->insert('temp_test_table', array('value'));

		$this->assertTrue($query instanceof Database_Command_Insert_Multiple);

		$query->identity('id')->values(array('65'), array('70'));

		$this->assertEquals(array(1,5), $query->execute($this->_db), 'Count is always one. Identity is INTEGER PRIMARY KEY of the last row');

		$query->values(NULL)->values(array('75'));

		$this->assertEquals(array(1,6), $query->execute($this->_db));
	}

	public function provider_table_columns()
	{
		return array
		(
			array('integer', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'integer',
			)),
			array('numeric', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'numeric',
			)),
			array('numeric(10)', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'numeric',
				'numeric_precision' => 10,
			)),
			array('numeric(10,5)', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'numeric',
				'numeric_precision' => 10,
				'numeric_scale' => 5,
			)),
			array('real', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'real',
			)),
			array('text', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'text',
			)),
			array('varchar(50)', array(
				'column_default' => NULL,
				'is_nullable' => 'YES',
				'data_type' => 'varchar',
				'character_maximum_length' => 50,
			)),

			array('int DEFAULT 5', array(
				'column_default' => 5,
				'is_nullable' => 'YES',
				'data_type' => 'int',
			)),
			array('int DEFAULT 5 NOT NULL', array(
				'column_default' => 5,
				'is_nullable' => 'NO',
				'data_type' => 'int',
			)),
		);
	}

	/**
	 * @dataProvider provider_table_columns
	 */
	public function test_table_columns($column, $expected)
	{
		$expected = array_merge(array(
			'column_name'       => 'field',
			'ordinal_position'  => 1,
			'column_default'    => NULL,
			'is_nullable'       => NULL,
			'data_type'         => NULL,
			'character_maximum_length'  => NULL,
			'numeric_precision' => NULL,
			'numeric_scale'     => NULL,
		), $expected);

		$this->_db->execute_command('DROP TABLE '.$this->_table);
		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table."( field $column )");

		$result = $this->_db->table_columns('temp_test_table');

		$this->assertEquals($expected, $result['field']);
	}

	public function test_table_columns_no_table()
	{
		$this->assertSame(array(), $this->_db->table_columns('table-does-not-exist'));
	}
}
