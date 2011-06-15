<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  PDO
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Transactions_Test extends Database_PDO_TestCase
{
	protected $_table = 'kohana_test_table';

	protected function getDataSet()
	{
		$dataset = new PHPUnit_Extensions_Database_DataSet_CsvDataSet;
		$dataset->addTable(
			Database::factory()->table_prefix().$this->_table,
			dirname(dirname(__FILE__)).'/datasets/values.csv'
		);

		return $dataset;
	}

	public function provider_begin()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				NULL,
				'kohana_txn_0',
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 60', array($table)
				),
				'kohana_savepoint',
				'kohana_savepoint',
			),
		);
	}

	/**
	 * @covers  Database_PDO::begin
	 *
	 * @dataProvider    provider_begin
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   string          $name       Transaction name
	 * @param   string          $expected   Expected transaction name
	 */
	public function test_begin($query, $command, $name, $expected)
	{
		$metadata = $this->getConnection()->getMetaData();

		if ($metadata instanceof Database_SQLServer_MetaData
			AND ! $metadata->is_read_committed_snapshot_on())
		{
			// This test hangs with the default isolation level, READ COMMITTED,
			// unless READ_COMMITTED_SNAPSHOT is ON.
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		// Start a transaction
		$this->assertSame($expected, $db->begin($name));
		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'No change');

		// Change the dataset
		$db->execute_command($command);
		$this->assertSame($initial, Database::factory()->execute_query($query)->as_array(), 'Other connection unaffected');
	}

	public function provider_rollback()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 60', array($table)
				),
			),
		);
	}

	/**
	 * @covers  Database_PDO::rollback
	 *
	 * @dataProvider    provider_rollback
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 */
	public function test_rollback($query, $command)
	{
		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$db->begin();

		// Change the dataset
		$db->execute_command($command);

		$this->assertNull($db->rollback());
		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'Changes reverted');
	}

	/**
	 * Trying to rollback when not in a transaction throws an exception.
	 *
	 * @covers  Database_PDO::rollback
	 */
	public function test_rollback_no_transaction()
	{
		$db = Database::factory();

		$this->setExpectedException(
			'Database_Exception', 'no active transaction'
		);

		$db->rollback();
	}

	public function provider_savepoint()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				NULL,
				'kohana_txn_1',
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 60', array($table)
				),
				'kohana_savepoint',
				'kohana_savepoint',
			),
		);
	}

	/**
	 * @covers  Database_PDO::rollback
	 * @covers  Database_PDO::savepoint
	 *
	 * @dataProvider    provider_savepoint
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   string          $name       Savepoint name
	 * @param   string          $expected   Expected savepoint name
	 */
	public function test_savepoint($query, $command, $name, $expected)
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$db->begin();

		// Change the dataset
		$db->execute_command($command);
		$before = $db->execute_query($query)->as_array();

		$this->assertSame($expected, $db->savepoint($name));
		$this->assertSame($before, $db->execute_query($query)->as_array(), 'No change');

		// Change the dataset
		$db->execute_command($command);

		$this->assertNull($db->rollback($expected));
		$this->assertSame($before, $db->execute_query($query)->as_array(), 'Reverted');
	}

	/**
	 * Setting a savepoint when not in a transaction may throw an exception.
	 *
	 * @covers  Database_PDO::savepoint
	 */
	public function test_savepoint_no_transaction()
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();

		if ($connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql')
		{
			$this->setExpectedException(
				'Database_Exception', 'only be used in transaction', '25P01'
			);
		}

		$db->savepoint('kohana_savepoint');
	}

	/**
	 * Reverting a non-existent savepoint throws an exception.
	 *
	 * @covers  Database_PDO::rollback
	 */
	public function test_rollback_invalid_savepoint()
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$db->begin();

		switch ($connection->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'mysql':
				$this->setExpectedException(
					'Database_Exception', 'does not exist', '42000'
				);
			break;
			case 'pgsql':
				$this->setExpectedException(
					'Database_Exception', 'no such savepoint', '3B001'
				);
			break;
			case 'sqlite':
				$this->setExpectedException(
					'Database_Exception', 'no such savepoint', 'HY000'
				);
			break;
			default:
				$this->setExpectedException('Database_Exception');
		}

		$db->rollback('kohana_savepoint');
	}

	public function provider_result()
	{
		return array(
			array(
				new SQL_Expression('SELECT value FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('INSERT INTO ? (value) VALUES (100)', array(new SQL_Table($this->_table))),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 60),
					array('value' => 60),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
					array('value' => 100),
				),
			),
			array(
				new SQL_Expression('SELECT value FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('DELETE FROM ? WHERE value = 60', array(new SQL_Table($this->_table))),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
				),
			),
		);
	}

	/**
	 * @covers  Database_PDO::commit
	 *
	 * @dataProvider    provider_result
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   array           $expected   Expected result of the query after command is executed and after commit
	 */
	public function test_commit($query, $command, $expected)
	{
		$metadata = $this->getConnection()->getMetaData();

		if ($metadata instanceof Database_SQLServer_MetaData
			AND ! $metadata->is_read_committed_snapshot_on())
		{
			// This test hangs with the default isolation level, READ COMMITTED,
			// unless READ_COMMITTED_SNAPSHOT is ON.
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$db->begin();

		// Change the dataset
		$db->execute_command($command);
		$this->assertSame($initial, $other->execute_query($query)->as_array(), 'Other connection unaffected');

		$this->assertNull($db->commit());
		$this->assertEquals($expected, $other->execute_query($query)->as_array(), 'Other connection affected');
	}

	/**
	 * Releasing a non-existent savepoint throws an exception.
	 *
	 * @covers  Database_PDO::commit
	 */
	public function test_commit_invalid_savepoint()
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$db->begin();

		switch ($connection->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'mysql':
				$this->setExpectedException(
					'Database_Exception', 'does not exist', '42000'
				);
			break;
			case 'pgsql':
				$this->setExpectedException(
					'Database_Exception', 'no such savepoint', '3B001'
				);
			break;
			case 'sqlite':
				$this->setExpectedException(
					'Database_Exception', 'no such savepoint', 'HY000'
				);
			break;
			default:
				$this->setExpectedException('Database_Exception');
		}

		$db->commit('kohana_savepoint');
	}

	/**
	 * A query that reads a dataset, two commands that alter the dataset and the
	 * dataset after both commands are executed.
	 */
	public function provider_nested_transaction_both()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 65', array($table)
				),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 60),
					array('value' => 60),
					array('value' => 100),
				),
			),
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value IN (60, 65)', array($table)
				),
				new SQL_Expression('UPDATE ? SET value = 10', array($table)),
				array(
					array('value' => 10),
					array('value' => 10),
				),
			),
		);
	}

	/**
	 * @covers  Database_PDO::begin
	 * @covers  Database_PDO::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed and after commit
	 */
	public function test_nested_transaction_commit_commit($query, $command1, $command2, $expected)
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit inner transaction
		$this->assertNull($db->commit($inner));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit outer transaction
		$this->assertNull($db->commit($outer));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection affected
		$this->assertEquals($expected, $other->execute_query($query)->as_array());
	}

	/**
	 * @covers  Database_PDO::begin
	 * @covers  Database_PDO::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed
	 */
	public function test_nested_transaction_commit_rollback($query, $command1, $command2, $expected)
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback inner transaction
		$this->assertNull($db->rollback($inner));
		$this->assertNotEquals($expected, $db->execute_query($query)->as_array());
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback outer transaction
		$this->assertNull($db->rollback($outer));
		$this->assertSame($initial, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());
	}

	/**
	 * A query that reads a dataset, two commands that alter the dataset and the
	 * dataset after the first command is executed.
	 */
	public function provider_nested_transaction_rollback_commit()
	{
		$table = new SQL_Table($this->_table);

		return array(
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'INSERT INTO ? (value) VALUES (100)', array($table)
				),
				new SQL_Expression(
					'DELETE FROM ? WHERE value = 65', array($table)
				),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 60),
					array('value' => 60),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
					array('value' => 100),
				),
			),
			array(
				new SQL_Expression('SELECT value FROM ?', array($table)),
				new SQL_Expression(
					'DELETE FROM ? WHERE value IN (60, 65)', array($table)
				),
				new SQL_Expression('UPDATE ? SET value = 10', array($table)),
				array(
					array('value' => 50),
					array('value' => 55),
				),
			),
		);
	}

	/**
	 * @covers  Database_PDO::begin
	 * @covers  Database_PDO::commit
	 *
	 * @dataProvider    provider_nested_transaction_rollback_commit
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after the first command is executed and after commit
	 */
	public function test_nested_transaction_rollback_commit($query, $command1, $command2, $expected)
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertNotEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback inner transaction
		$this->assertNull($db->rollback($inner));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit outer transaction
		$this->assertNull($db->commit($outer));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection affected
		$this->assertEquals($expected, $other->execute_query($query)->as_array());
	}

	/**
	 * Commit the named outer transaction when nested.
	 *
	 * @covers  Database_PDO::begin
	 * @covers  Database_PDO::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed and after commit
	 */
	public function test_nested_transaction_short_commit($query, $command1, $command2, $expected)
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Commit outer transaction
		$this->assertNull($db->commit($outer));
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection affected
		$this->assertEquals($expected, $other->execute_query($query)->as_array());
	}

	/**
	 * Rollback the named outer transaction when nested.
	 *
	 * @covers  Database_PDO::begin
	 * @covers  Database_PDO::commit
	 *
	 * @dataProvider    provider_nested_transaction_both
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command1   SQL command that alters the dataset, executed first
	 * @param   SQL_Expression  $command2   SQL command that alters the dataset, executed second
	 * @param   array           $expected   Expected result of the query after both commands are executed
	 */
	public function test_nested_transaction_short_rollback($query, $command1, $command2, $expected)
	{
		$connection = $this->getConnection()->getConnection();

		if (in_array($connection->getAttribute(PDO::ATTR_DRIVER_NAME), array(
			'sqlsrv',
		)))
		{
			// Savepoints have a different syntax in SQL Server
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$outer = $db->begin();

		// Change the dataset
		$db->execute_command($command1);
		$this->assertNotEquals($initial, $db->execute_query($query)->as_array());

		$inner = $db->begin();

		// Change the dataset again
		$db->execute_command($command2);
		$this->assertEquals($expected, $db->execute_query($query)->as_array());

		// Other connection unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());

		// Rollback outer transaction
		$this->assertNull($db->rollback($outer));
		$this->assertSame($initial, $db->execute_query($query)->as_array());

		// Other connection still unaffected
		$this->assertSame($initial, $other->execute_query($query)->as_array());
	}
}
