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
		$db->execute_command('INSERT INTO '.$table.' ('.$column.') VALUES (50)');
		$db->execute_command('INSERT INTO '.$table.' ('.$column.') VALUES (55)');
		$db->execute_command('INSERT INTO '.$table.' ('.$column.') VALUES (60)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	public function test_execute_query_command()
	{
		$db = $this->sharedFixture;

		$this->assertNull($db->execute_query('DELETE FROM '.$db->quote_table($this->_table)));
	}

	/**
	 * @covers  Database_PDO::last_insert_id
	 */
	public function test_last_insert_id()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$db->execute_command('INSERT INTO '.$table.' ('.$column.') VALUES (100)');

		$this->assertEquals(4, $db->last_insert_id(), 'Once');
		$this->assertEquals(4, $db->last_insert_id(), 'Twice');
	}

	public function test_prepare()
	{
		$db = $this->sharedFixture;
		$statement = $db->prepare('SELECT * FROM '.$db->quote_table($this->_table));

		$this->assertType('PDOStatement', $statement);
	}

	/**
	 * @dataProvider    provider_prepare_command
	 */
	public function test_prepare_command($input_sql, $input_params, $expected_sql, $expected_params)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$input_sql = strtr($input_sql, array('$table' => $table, '$column' => $column));
		$expected_sql = strtr($expected_sql, array('$table' => $table, '$column' => $column));

		$command = $db->prepare_command($input_sql, $input_params);

		$this->assertType('Database_PDO_Command', $command);
		$this->assertSame($expected_sql, (string) $command);
		$this->assertSame($expected_params, $command->parameters);
	}

	public function provider_prepare_command()
	{
		return array
		(
			array(
				'DELETE FROM $table', array(),
				'DELETE FROM $table', array(),
			),
			array(
				'DELETE FROM ?', array(new SQL_Table($this->_table)),
				'DELETE FROM $table', array(),
			),
			array(
				'DELETE FROM :table', array(':table' => new SQL_Table($this->_table)),
				'DELETE FROM $table', array(),
			),
			array(
				'DELETE FROM $table WHERE ?', array(new SQL_Conditions(new SQL_Column($this->_column), '=', 60)),
				'DELETE FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'DELETE FROM $table WHERE :condition', array(':condition' => new SQL_Conditions(new SQL_Column($this->_column), '=', 60)),
				'DELETE FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'DELETE FROM $table WHERE :condition AND :condition', array(':condition' => new SQL_Conditions(new SQL_Column($this->_column), '=', 60)),
				'DELETE FROM $table WHERE $column = ? AND $column = ?', array(1 => 60, 60),
			),
			array(
				'DELETE FROM $table WHERE $column = ?', array(60),
				'DELETE FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'DELETE FROM $table WHERE $column = :value', array(':value' => 60),
				'DELETE FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'DELETE FROM $table WHERE $column = :value AND $column = :value', array(':value' => 60),
				'DELETE FROM $table WHERE $column = ? AND $column = ?', array(1 => 60, 60),
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(60, 70, 80)),
				'DELETE FROM $table WHERE $column IN (?, ?, ?)', array(1 => 60, 70, 80),
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(60, 70, array(80))),
				'DELETE FROM $table WHERE $column IN (?, ?, ?)', array(1 => 60, 70, 80),
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(60, new SQL_Expression(':name', array(':name' => 70)), 80)),
				'DELETE FROM $table WHERE $column IN (?, ?, ?)', array(1 => 60, 70, 80),
			),
			array(
				'DELETE FROM $table WHERE $column IN (?)', array(array(new SQL_Identifier($this->_column), 70, 80)),
				'DELETE FROM $table WHERE $column IN ($column, ?, ?)', array(1 => 70, 80),
			),
		);
	}

	/**
	 * @dataProvider    provider_prepare_query
	 */
	public function test_prepare_query($input_sql, $input_params, $expected_sql, $expected_params)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$input_sql = strtr($input_sql, array('$table' => $table, '$column' => $column));
		$expected_sql = strtr($expected_sql, array('$table' => $table, '$column' => $column));

		$query = $db->prepare_query($input_sql, $input_params);

		$this->assertType('Database_PDO_Query', $query);
		$this->assertSame($expected_sql, (string) $query);
		$this->assertSame($expected_params, $query->parameters);
	}

	public function provider_prepare_query()
	{
		return array
		(
			array(
				'SELECT * FROM $table', array(),
				'SELECT * FROM $table', array(),
			),
			array(
				'SELECT * FROM ?', array(new SQL_Table($this->_table)),
				'SELECT * FROM $table', array(),
			),
			array(
				'SELECT * FROM :table', array(':table' => new SQL_Table($this->_table)),
				'SELECT * FROM $table', array(),
			),
			array(
				'SELECT * FROM $table WHERE ?', array(new SQL_Conditions(new SQL_Column($this->_column), '=', 60)),
				'SELECT * FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'SELECT * FROM $table WHERE :condition', array(':condition' => new SQL_Conditions(new SQL_Column($this->_column), '=', 60)),
				'SELECT * FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'SELECT * FROM $table WHERE :condition AND :condition', array(':condition' => new SQL_Conditions(new SQL_Column($this->_column), '=', 60)),
				'SELECT * FROM $table WHERE $column = ? AND $column = ?', array(1 => 60, 60),
			),
			array(
				'SELECT * FROM $table WHERE $column = ?', array(60),
				'SELECT * FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'SELECT * FROM $table WHERE $column = :value', array(':value' => 60),
				'SELECT * FROM $table WHERE $column = ?', array(1 => 60),
			),
			array(
				'SELECT * FROM $table WHERE $column = :value AND $column = :value', array(':value' => 60),
				'SELECT * FROM $table WHERE $column = ? AND $column = ?', array(1 => 60, 60),
			),
			array(
				'SELECT * FROM $table WHERE $column IN (?)', array(array(60, 70, 80)),
				'SELECT * FROM $table WHERE $column IN (?, ?, ?)', array(1 => 60, 70, 80),
			),
			array(
				'SELECT * FROM $table WHERE $column IN (?)', array(array(60, 70, array(80))),
				'SELECT * FROM $table WHERE $column IN (?, ?, ?)', array(1 => 60, 70, 80),
			),
			array(
				'SELECT * FROM $table WHERE $column IN (?)', array(array(60, new SQL_Expression(':name', array(':name' => 70)), 80)),
				'SELECT * FROM $table WHERE $column IN (?, ?, ?)', array(1 => 60, 70, 80),
			),
			array(
				'SELECT * FROM $table WHERE $column IN (?)', array(array(new SQL_Identifier($this->_column), 70, 80)),
				'SELECT * FROM $table WHERE $column IN ($column, ?, ?)', array(1 => 70, 80),
			),
		);
	}
}
