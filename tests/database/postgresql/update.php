<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Update_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();
		$table = $db->quote_table($this->_table);

		$db->execute_command(implode('; ', array(
			'CREATE TEMPORARY TABLE '.$table.' ("id" bigserial PRIMARY KEY, "value" integer)',
			'INSERT INTO '.$table.' ("value") VALUES (50)',
			'INSERT INTO '.$table.' ("value") VALUES (55)',
			'INSERT INTO '.$table.' ("value") VALUES (60)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
		)));
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @covers  Database_PostgreSQL_Update::from
	 */
	public function test_from_limit()
	{
		$db = $this->sharedFixture;
		$command = $db->update($this->_table)->limit(5);

		try
		{
			$command->from($this->_table);
			$this->setExpectedException('Kohana_Exception');
		}
		catch (Kohana_Exception $e) {}

		$this->assertSame($command, $command->from(NULL), 'Chainable (reset)');
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 */
	public function test_limit()
	{
		$db = $this->sharedFixture;
		$command = $db->update($this->_table, NULL, array('value' => 100))
			->where('value', 'between', array(42,62));

		$this->assertSame($command, $command->limit(2), 'Chainable (int)');
		$this->assertSame(2, $db->execute($command));

		$this->assertSame(0, $db->execute($command->limit(0)), 'Zero');

		$this->assertSame($command, $command->limit(NULL), 'Chainable (reset)');
		$this->assertSame(1, $db->execute($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 */
	public function test_limit_from()
	{
		$db = $this->sharedFixture;
		$command = $db->update($this->_table)->from($this->_table);

		try
		{
			$command->limit(5);
			$this->setExpectedException('Kohana_Exception');
		}
		catch (Kohana_Exception $e) {}

		$this->assertSame($command, $command->limit(NULL), 'Chainable (reset)');
	}

	/**
	 * @covers  Database_PostgreSQL_Update::returning
	 */
	public function test_returning()
	{
		$db = $this->sharedFixture;
		$query = $db->update($this->_table, NULL, array('value' => 100))
			->where('value', 'between', array(52,62));

		$this->assertSame($query, $query->returning(array('more' => 'id')), 'Chainable (column)');

		$result = $db->execute($query);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals(array(array('more' => 2), array('more' => 3)), $result->as_array(), 'Each aliased column');

		$query->where('value', '=', 100);

		$this->assertSame($query, $query->returning(new SQL_Expression('\'asdf\' AS "rawr"')), 'Chainable (expression)');

		$result = $db->execute($query);

		$this->assertEquals(array(array('rawr' => 'asdf'), array('rawr' => 'asdf')), $result->as_array(), 'Each expression');

		$this->assertSame($query, $query->returning(NULL), 'Chainable (reset)');
		$this->assertSame(2, $db->execute($query));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::as_assoc
	 */
	public function test_as_assoc()
	{
		$db = $this->sharedFixture;
		$query = $db->update($this->_table, NULL, array('value' => 100))
			->where('value', 'between', array(52,62))
			->returning(array('id'));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');

		$result = $db->execute($query);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals(array(array('id' => 2), array('id' => 3)), $result->as_array(), 'Each column');
	}

	/**
	 * @covers  Database_PostgreSQL_Update::as_object
	 */
	public function test_as_object()
	{
		$db = $this->sharedFixture;
		$query = $db->update($this->_table, NULL, array('value' => 100))
			->where('value', 'between', array(52,62))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object(), 'Chainable');

		$result = $db->execute($query);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals(array( (object) array('id' => 2), (object) array('id' => 3)), $result->as_array(), 'Each column');
	}

	/**
	 * @covers  Database_PostgreSQL_Update::__toString
	 */
	public function test_toString()
	{
		$command = new Database_PostgreSQL_Update;

		$this->assertSame('UPDATE :table SET :values', (string) $command);

		$command
			->where(new SQL_Conditions)
			->limit(1)
			->returning('a');

		$this->assertSame('UPDATE :table SET :values WHERE ctid IN (SELECT ctid FROM :table WHERE :where LIMIT :limit) RETURNING :returning', (string) $command);
	}
}
