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
class Database_PDO_Execution_Test extends Database_PDO_TestCase
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

	/**
	 * 1-indexed parameters should not be passed to PDOStatement::execute(),
	 * though some drivers allow it.
	 *
	 * @covers  PDOStatement::execute
	 *
	 * @link    http://bugs.php.net/37290
	 */
	public function test_execute_parameters_one_indexed()
	{
		$pdo = $this->getConnection()->getConnection();

		// PostgreSQL: Addition coerces the value to integer
		$statement = 'SELECT ? + 0';

		// 0-indexed array parameters succeed
		$this->assertTrue($pdo->prepare($statement)->execute(array(1)));

		$result = $pdo->prepare($statement);

		try
		{
			$result = $result->execute(array(1 => 1));
		}
		catch (PDOException $e)
		{
			// The exception message and code vary between drivers
			switch ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'mysql':
					$this->setExpectedException(
						'PDOException', 'parameter number', 'HY093'
					);
				break;
				case 'sqlite':
					$this->setExpectedException(
						'PDOException', 'index out of range', 'HY000'
					);
				break;
				case 'sqlsrv':
					$this->setExpectedException(
						'PDOException', 'field incorrect', '07002'
					);
				break;
			}

			throw $e;
		}

		// Some drivers allow 1-indexed array parameters
		switch ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$this->assertTrue($result);
			break;
			default:
				$this->assertFalse($result);
		}
	}

	/**
	 * Named parameters should not be used more than once, though most drivers
	 * allow it.
	 *
	 * @covers  PDOStatement::execute
	 *
	 * @link    http://bugs.php.net/33886
	 * @link    http://www.php.net/manual/pdo.prepare.php
	 */
	public function test_execute_parameters_multiple_named()
	{
		$pdo = $this->getConnection()->getConnection();

		// PostgreSQL: Addition coerces the values to integer
		$statement = 'SELECT :a + 0, :a + 0';

		$result = $pdo->prepare($statement);

		try
		{
			$this->assertTrue($result->execute(array(':a' => 1)));
		}
		catch (PDOException $e)
		{
			switch ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'sqlsrv':
					$this->setExpectedException(
						'PDOException', 'field incorrect', '07002'
					);
				break;
			}

			throw $e;
		}

		$result = $result->fetch(PDO::FETCH_NUM);

		// The returned data types vary between drivers
		switch ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$this->assertSame(array(1,1), $result);
			break;
			default:
				$this->assertSame(array('1','1'), $result);
		}
	}
}
