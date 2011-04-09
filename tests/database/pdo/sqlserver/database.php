<?php
/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlserver
 */
class Database_PDO_SQLServer_Database_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo_sqlsrv'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO SQL Server extension not installed');

		if ( ! Database::factory() instanceof Database_PDO_SQLServer)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for SQL Server using PDO');
	}

	protected $_table = 'kohana_test_table';

	public function provider_charset()
	{
		if ( ! extension_loaded('pdo_sqlsrv'))
			return;

		return array
		(
			array(PDO::SQLSRV_ENCODING_DEFAULT),
			array(PDO::SQLSRV_ENCODING_SYSTEM),
			array(PDO::SQLSRV_ENCODING_UTF8),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::charset
	 *
	 * @dataProvider    provider_charset
	 *
	 * @param   integer $encoding
	 */
	public function test_charset($encoding)
	{
		$db = Database::factory();

		$this->assertNull($db->charset($encoding));
	}

	/**
	 * @covers  Database_PDO_SQLServer::connect
	 */
	public function test_connect_error()
	{
		$db = new Database_PDO_SQLServer(
			'name',
			array('connection' => array(
				'dsn' => 'sqlsrv:Server=(local)\\kohana-invalid-instance;LoginTimeout=1',
				'username' => NULL,
				'password' => NULL
			))
		);

		$this->setExpectedException(
			'Database_Exception',
			'Error Locating Server'
		);

		$db->connect();
	}

	public function provider_datatype()
	{
		return array
		(
			array('money', 'exact', TRUE),
			array('rowversion', NULL, array('type' => 'integer')),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::datatype
	 *
	 * @dataProvider    provider_datatype
	 *
	 * @param   string      $type
	 * @param   string      $attribute
	 * @param   array|mixed $expected
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	public function provider_ddl_column()
	{
		return array
		(
			array(array(), new Database_SQLServer_DDL_Column),
			array(array('a'), new Database_SQLServer_DDL_Column('a')),
			array(array('a', 'b'), new Database_SQLServer_DDL_Column('a', 'b')),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::ddl_column
	 *
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array                           $arguments
	 * @param   Database_SQLServer_DDL_Column   $expected
	 */
	public function test_ddl_column($arguments, $expected)
	{
		$this->assertEquals(
			$expected,
			call_user_func_array('Database_PDO_SQLServer::ddl_column', $arguments)
		);
	}

	public function provider_delete()
	{
		return array
		(
			array(array(), new Database_SQLServer_Delete),
			array(array('a'), new Database_SQLServer_Delete('a')),
			array(array('a', 'b'), new Database_SQLServer_Delete('a', 'b')),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::delete
	 *
	 * @dataProvider    provider_delete
	 *
	 * @param   array                       $arguments
	 * @param   Database_SQLServer_Delete   $expected
	 */
	public function test_delete($arguments, $expected)
	{
		$this->assertEquals(
			$expected,
			call_user_func_array('Database_PDO_SQLServer::delete', $arguments)
		);
	}

	/**
	 * @covers  Database_PDO::execute_command
	 */
	public function test_execute_command_query()
	{
		$db = Database::factory();

		$this->assertSame(0, $db->execute_command('SELECT 1'));
	}

	public function provider_insert()
	{
		return array
		(
			array(array(), new Database_SQLServer_Insert),
			array(array('a'), new Database_SQLServer_Insert('a')),
			array(array('a', array('b')), new Database_SQLServer_Insert('a', array('b'))),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::insert
	 *
	 * @dataProvider    provider_insert
	 *
	 * @param   array   $arguments
	 * @param   Database_SQLServer_Select   $expected
	 */
	public function test_insert($arguments, $expected)
	{
		$this->assertEquals(
			$expected,
			call_user_func_array('Database_PDO_SQLServer::insert', $arguments)
		);
	}

	public function provider_select()
	{
		return array
		(
			array(array(), new Database_SQLServer_Select),
			array(array(array('a' => 'b')), new Database_SQLServer_Select(array('a' => 'b'))),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::prepare
	 */
	public function test_prepare()
	{
		$db = Database::factory();
		$statement = $db->prepare('SELECT 1');

		$this->assertFalse(
			$statement->getAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY)
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::select
	 *
	 * @dataProvider    provider_select
	 *
	 * @param   array                       $arguments
	 * @param   Database_SQLServer_Select   $expected
	 */
	public function test_select($arguments, $expected)
	{
		$this->assertEquals(
			$expected,
			call_user_func_array('Database_PDO_SQLServer::select', $arguments)
		);
	}

	public function provider_update()
	{
		return array
		(
			array(array(), new Database_SQLServer_Update),
			array(array('a'), new Database_SQLServer_Update('a')),
			array(array('a', 'b'), new Database_SQLServer_Update('a', 'b')),
			array(array('a', 'b', array('c' => 'd')), new Database_SQLServer_Update('a', 'b', array('c' => 'd'))),
		);
	}

	/**
	 * @covers  Database_PDO_SQLServer::update
	 *
	 * @dataProvider    provider_update
	 *
	 * @param   array                       $arguments
	 * @param   Database_SQLServer_Update   $expected
	 */
	public function test_update($arguments, $expected)
	{
		$this->assertEquals(
			$expected,
			call_user_func_array('Database_PDO_SQLServer::update', $arguments)
		);
	}
}
