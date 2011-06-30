<?php

/**
 * @package     RealDatabase
 * @subpackage  PDO
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Statement_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pdo'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');

		if ( ! Database::factory() instanceof Database_PDO)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PDO');
	}

	public function provider_constructor_sql()
	{
		return array
		(
			array('SELECT 1'),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::__construct
	 * @covers  Database_PDO_Statement::__toString
	 *
	 * @dataProvider    provider_constructor_sql
	 *
	 * @param   string  $sql
	 */
	public function test_constructor_sql($sql)
	{
		$db = Database::factory();

		$statement = new Database_PDO_Statement($db, $db->prepare($sql));

		$this->assertSame($sql, (string) $statement);
	}

	public function provider_constructor_parameters()
	{
		return array(
			// 0-indexed
			array(array(NULL), array(1 => NULL)),
			array(array('a'), array(1 => 'a')),
			array(array(5), array(1 => 5)),

			// 1-indexed
			array(array(1 => NULL), array(1 => NULL)),
			array(array(1 => 'a'), array(1 => 'a')),
			array(array(1 => 5), array(1 => 5)),

			// Named
			array(array(':a' => NULL), array(':a' => NULL)),
			array(array(':a' => 'b'), array(':a' => 'b')),
			array(array(':a' => 5), array(':a' => 5)),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::__construct
	 * @covers  Database_PDO_Statement::parameters
	 *
	 * @dataProvider    provider_constructor_parameters
	 *
	 * @param   array   $parameters Argument to the constructor
	 * @param   array   $expected   Expected parameters
	 */
	public function test_constructor_parameters($parameters, $expected)
	{
		$db = Database::factory();

		$statement = new Database_PDO_Statement(
			$db, $db->prepare('SELECT 1'), $parameters
		);

		$this->assertSame($expected, $statement->parameters());
	}

	/**
	 * @covers  Database_PDO_Statement::__construct
	 */
	public function test_constructor_parameters_bound()
	{
		$db = Database::factory();

		$var;
		$parameters[1] =& $var;

		// PostgreSQL: addition operator implies integer type
		$statement = new Database_PDO_Statement(
			$db, $db->prepare('SELECT ? + 0'), $parameters
		);

		$var = 1;
		$this->assertSame($parameters, $statement->parameters());
		$this->assertEquals($var, $statement->execute_query()->get());
	}

	public function provider_bind()
	{
		return array(
			array(
				1, 'a', PDO::PARAM_STR, array(1 => 'a'),
				'b', array(1 => 'b')
			),
			array(
				2, 3, PDO::PARAM_INT, array(2 => 3),
				4, array(2 => 4)
			),
			array(
				5, TRUE, PDO::PARAM_BOOL, array(5 => TRUE),
				FALSE, array(5 => FALSE)
			),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::bind
	 *
	 * @dataProvider    provider_bind
	 *
	 * @param   integer $param          First argument to the method
	 * @param   mixed   $var            Second argument to the method
	 * @para    integer $type           Expected parameter type
	 * @param   array   $params_before  Expected parameters
	 * @param   mixed   $after          Value to assign to bound variable
	 * @param   array   $params_after   Expected paramters after assigning to bound variable
	 */
	public function test_bind($param, $var, $type, $params_before, $next, $params_after)
	{
		$db = new Database_PDO('name', array());
		$statement = $this->getMock('PDOStatement', array('bindParam'));

		$statement
			->expects($this->once())
			->method('bindParam')
			->with(
				$this->identicalTo($param),
				$this->identicalTo($var),
				$this->identicalTo($type)
			);

		$statement = new Database_PDO_Statement($db, $statement);

		$this->assertSame($statement, $statement->bind($param, $var), 'Chainable');
		$this->assertSame($params_before, $statement->parameters());

		$var = $next;
		$this->assertSame($params_after, $statement->parameters());
	}

	/**
	 * @covers  Database_PDO_Statement::bind
	 */
	public function test_bind_object()
	{
		$db = new Database_PDO('name', array());
		$statement = $this->getMock('PDOStatement', array('bindParam'));
		$var = new stdClass;

		$statement
			->expects($this->once())
			->method('bindParam')
			->with(
				$this->identicalTo(1),
				$this->EqualTo($var)
			);

		$statement = new Database_PDO_Statement($db, $statement);

		$this->assertSame($statement, $statement->bind(1, $var), 'Chainable');
		$this->assertSame(array(1 => $var), $statement->parameters());

		$var = new stdClass;
		$this->assertSame(array(1 => $var), $statement->parameters());
	}

	public function provider_bind_type()
	{
		return array(
			array(
				1, 2, PDO::PARAM_INT, array(1 => 2),
				3, array(1 => 3)
			),
			array(
				4, 5, PDO::PARAM_STR, array(4 => 5),
				6, array(4 => 6)
			),
			array(
				7, 8, PDO::PARAM_BOOL, array(7 => 8),
				9, array(7 => 9)
			),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::bind
	 *
	 * @dataProvider    provider_bind_type
	 *
	 * @param   integer $param          First argument to the method
	 * @param   mixed   $var            Second argument to the method
	 * @para    integer $type           Third argument to the method
	 * @param   array   $params_before  Expected parameters
	 * @param   mixed   $after          Value to assign to bound variable
	 * @param   array   $params_after   Expected paramters after assigning to bound variable
	 */
	public function test_bind_type($param, $var, $type, $params_before, $next, $params_after)
	{
		$db = new Database_PDO('name', array());
		$statement = $this->getMock('PDOStatement', array('bindParam'));

		$statement
			->expects($this->once())
			->method('bindParam')
			->with(
				$this->identicalTo($param),
				$this->identicalTo($var),
				$this->identicalTo($type)
			);

		$statement = new Database_PDO_Statement($db, $statement);

		$this->assertSame($statement, $statement->bind($param, $var, $type), 'Chainable');
		$this->assertSame($params_before, $statement->parameters());

		$var = $next;
		$this->assertSame($params_after, $statement->parameters());
	}

	public function provider_execute_query()
	{
		return array
		(
			array('SELECT 1 AS value', array(), array(
				array('value' => 1),
			)),

			// PostgreSQL: addition operator implies integer type
			array('SELECT ? + 0 AS value', array(1 => 2), array(
				array('value' => 2),
			)),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::_execute
	 * @covers  Database_PDO_Statement::execute_query
	 *
	 * @dataProvider    provider_execute_query
	 *
	 * @param   string  $sql        SQL statement
	 * @param   array   $parameters Statement parameters
	 * @param   array   $expected   Expected result
	 */
	public function test_execute_query($sql, $parameters, $expected)
	{
		$db = Database::factory();
		$statement = new Database_PDO_Statement(
			$db, $db->prepare($sql), $parameters
		);

		$result = $statement->execute_query();

		$this->assertType('Database_PDO_Result', $result);
		$this->assertEquals($expected, $result->as_array());
	}

	public function provider_param()
	{
		return array(
			array(1, 'a', PDO::PARAM_STR, array(1 => 'a')),
			array(2, 3, PDO::PARAM_INT, array(2 => 3)),
			array(4, TRUE, PDO::PARAM_BOOL, array(4 => TRUE)),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::param
	 *
	 * @dataProvider    provider_param
	 *
	 * @param   integer $param      First argument to the method
	 * @param   mixed   $value      Second argument to the method
	 * @para    integer $type       Expected parameter type
	 * @param   array   $parameters Expected parameters
	 */
	public function test_param($param, $value, $type, $parameters)
	{
		$db = new Database_PDO('name', array());
		$statement = $this->getMock('PDOStatement', array('bindValue'));

		$statement
			->expects($this->once())
			->method('bindValue')
			->with(
				$this->identicalTo($param),
				$this->identicalTo($value),
				$this->identicalTo($type)
			);

		$statement = new Database_PDO_Statement($db, $statement);

		$this->assertSame($statement, $statement->param($param, $value), 'Chainable');
		$this->assertSame($parameters, $statement->parameters());
	}

	/**
	 * @covers  Database_PDO_Statement::param
	 */
	public function test_param_object()
	{
		$db = new Database_PDO('name', array());
		$statement = $this->getMock('PDOStatement', array('bindValue'));
		$value = new stdClass;

		$statement
			->expects($this->once())
			->method('bindValue')
			->with(
				$this->identicalTo(1),
				$this->EqualTo($value)
			);

		$statement = new Database_PDO_Statement($db, $statement);

		$this->assertSame($statement, $statement->param(1, $value), 'Chainable');
		$this->assertSame(array(1 => $value), $statement->parameters());
	}

	public function provider_param_type()
	{
		return array(
			array(1, 2, PDO::PARAM_INT, array(1 => 2)),
			array(3, 4, PDO::PARAM_STR, array(3 => 4)),
			array(5, 6, PDO::PARAM_BOOL, array(5 => 6)),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::param
	 *
	 * @dataProvider    provider_param_type
	 *
	 * @param   integer $param      First argument to the method
	 * @param   mixed   $value      Second argument to the method
	 * @para    integer $type       Third argument to the method
	 * @param   array   $parameters Expected parameters
	 */
	public function test_param_type($param, $value, $type, $parameters)
	{
		$db = new Database_PDO('name', array());
		$statement = $this->getMock('PDOStatement', array('bindValue'));

		$statement
			->expects($this->once())
			->method('bindValue')
			->with(
				$this->identicalTo($param),
				$this->identicalTo($value),
				$this->identicalTo($type)
			);

		$statement = new Database_PDO_Statement($db, $statement);

		$this->assertSame($statement, $statement->param($param, $value, $type), 'Chainable');
		$this->assertSame($parameters, $statement->parameters());
	}

	public function provider_parameters()
	{
		return array(
			array(array(1 => 'a')),
			array(array(1 => 'a', 'b')),
		);
	}

	/**
	 * @covers  Database_PDO_Statement::parameters
	 *
	 * @dataProvider    provider_parameters
	 *
	 * @param   array   $argument
	 */
	public function test_parameters($argument)
	{
		$db = new Database_PDO('name', array());
		$statement = $this->getMock('PDOStatement', array('bindValue'));

		$statement
			->expects($this->exactly(count($argument)))
			->method('bindValue');

		$statement = new Database_PDO_Statement($db, $statement);

		$this->assertSame($statement, $statement->parameters($argument), 'Chainable');
		$this->assertSame($argument, $statement->parameters());
	}
}
