<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Base_Database_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database::__construct
	 * @covers  Database::__toString
	 */
	public function test_constructor_name()
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame('name', (string) $mock);
	}

	public function provider_constructor_quote_character()
	{
		return array(
			array('$', '$$'),
			array(array('a', 'b'), 'ab'),
		);
	}

	/**
	 * @covers  Database::__construct
	 *
	 * @dataProvider    provider_constructor_quote_character
	 *
	 * @param   string|array    $value      Argument
	 * @param   string          $expected
	 */
	public function test_constructor_quote_character($value, $expected)
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array(
			'quote_character' => $value,
		)));

		$this->assertSame($expected, $mock->quote_identifier(''));
	}

	public function provider_constructor_table_prefix()
	{
		return array(
			array('', ''),
			array('a', 'a'),
			array('pre_', 'pre_'),
		);
	}

	/**
	 * @covers  Database::__construct
	 *
	 * @dataProvider    provider_constructor_table_prefix
	 *
	 * @param   string  $value      Argument
	 * @param   string  $expected
	 */
	public function test_constructor_table_prefix($value, $expected)
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array(
			'table_prefix' => $value,
		)));

		$this->assertSame($expected, $mock->table_prefix());
	}

	/**
	 * @covers  Database::__destruct
	 */
	public function test_destructor()
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('disconnect');

		$mock->__destruct();
	}

	/**
	 * An exception is thrown when the configuration lacks a driver type.
	 *
	 * @covers  Database::factory
	 */
	public function test_factory_incomplete_config()
	{
		$this->setExpectedException('Kohana_Exception');

		Database::factory('any', array());
	}

	/**
	 * @covers  Database::factory
	 */
	public function test_factory_load_config()
	{
		$config = Kohana::config('database');

		// Find an unused config group
		for ($i = 0; $i < 10; ++$i)
		{
			$name = sha1(mt_rand());

			if ( ! isset($config[$name]))
				break;
		}

		if (isset($config[$name]))
			$this->markTestSkipped('Unable to find unused config group');

		$class = 'Database_Mock_'.$name;
		$driver = 'Mock_'.$name;

		// Generate a mock class
		$this->getMockForAbstractClass('Database', array('name', array()), $class);

		// Set the config group
		$config[$name] = array('type' => $driver);

		$result = Database::factory($name);

		$this->assertType($class, $result);
		$this->assertSame($name, (string) $result);
	}

	/**
	 * @covers  Database::instance
	 */
	public function test_instance()
	{
		$name = sha1(mt_rand());
		$class = 'Database_Mock_'.$name;
		$driver = 'Mock_'.$name;

		// Generate a mock class
		$this->getMockForAbstractClass('Database', array('name', array()), $class);

		$result = Database::instance($name, array('type' => $driver));

		$this->assertType($class, $result);
		$this->assertSame($name, (string) $result);

		$this->assertSame($result, Database::instance($name));
	}

	public function provider_binary()
	{
		return array(
			array(array('a'), new Database_Binary('a')),
		);
	}

	/**
	 * @covers  Database::binary
	 *
	 * @dataProvider    provider_binary
	 *
	 * @param   array           $arguments
	 * @param   Database_Binary $expected
	 */
	public function test_binary($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::binary', $arguments)
		);
	}

	public function provider_datetime()
	{
		return array(
			array(array(1258461296), new Database_DateTime(1258461296)),
			array(array(1258461296, 'UTC'), new Database_DateTime(1258461296, 'UTC')),
			array(array(1258461296, 'UTC', 'Y-m-d'), new Database_DateTime(1258461296, 'UTC', 'Y-m-d')),
		);
	}

	/**
	 * @covers  Database::datetime
	 *
	 * @dataProvider    provider_datetime
	 *
	 * @param   array               $arguments
	 * @param   Database_DateTime   $expected
	 */
	public function test_datetime($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::datetime', $arguments)
		);
	}

	public function provider_delete()
	{
		return array(
			array(array(), new Database_Delete),
			array(array('a'), new Database_Delete('a')),
			array(array('a', 'b'), new Database_Delete('a', 'b')),
		);
	}

	/**
	 * @covers  Database::delete
	 *
	 * @dataProvider    provider_delete
	 *
	 * @param   array           $arguments
	 * @param   Database_Delete $expected
	 */
	public function test_delete($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::delete', $arguments)
		);
	}

	/**
	 * @covers  Database::execute
	 */
	public function test_execute_insert()
	{
		$statement = new Database_Insert;

		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('execute_command')
			->with($this->equalTo($statement));

		$mock->execute($statement);
	}

	/**
	 * @covers  Database::execute
	 */
	public function test_execute_insert_identity()
	{
		$statement = new Database_Insert;
		$statement->identity('a');

		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('execute_insert')
			->with(
				$this->equalTo($statement),
				$this->equalTo($statement->identity)
			);

		$mock->execute($statement);
	}

	/**
	 * @covers  Database::execute
	 */
	public function test_execute_query()
	{
		$statement = new Database_Select;

		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('execute_query')
			->with(
				$this->equalTo($statement),
				$this->equalTo($statement->as_object)
			);

		$mock->execute($statement);
	}

	/**
	 * @covers  Database::execute
	 */
	public function test_execute_returning()
	{
		$statement = new Database_Delete;

		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('execute_command')
			->with($this->equalTo($statement));

		$mock->execute($statement);
	}

	/**
	 * @covers  Database::execute
	 */
	public function test_execute_returning_returning()
	{
		$statement = new Database_Delete;
		$statement->returning(array('a'));

		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('execute_query')
			->with(
				$this->equalTo($statement),
				$this->equalTo($statement->as_object)
			);

		$mock->execute($statement);
	}

	/**
	 * @covers  Database::execute
	 */
	public function test_execute_string()
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('execute_command')
			->with($this->identicalTo('SELECT 1'));

		$mock->execute('SELECT 1');
	}

	public function provider_insert()
	{
		return array(
			array(array(), new Database_Insert),
			array(array('a'), new Database_Insert('a')),
			array(array('a', array('b')), new Database_Insert('a', array('b'))),
		);
	}

	/**
	 * @covers  Database::insert
	 *
	 * @dataProvider    provider_insert
	 *
	 * @param   array           $arguments
	 * @param   Database_Insert $expected
	 */
	public function test_insert($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::insert', $arguments)
		);
	}

	public function provider_parse_statement()
	{
		$result = array(
			array(new SQL_Expression(''), new Database_Statement('')),

			// data set #1
			array(
				new SQL_Expression('?', array('a')),
				new Database_Statement('?', array('a'))
			),
			array(
				new SQL_Expression('?', array(new SQL_Expression('a'))),
				new Database_Statement('a')
			),
			array(
				new SQL_Expression('?', array(new SQL_Identifier('a'))),
				new Database_Statement('"a"')
			),
			array(
				new SQL_Expression('?', array(new SQL_Table('a'))),
				new Database_Statement('"pre_a"')
			),

			// data set #5
			array(
				new SQL_Expression(':a', array(':a' => 'b')),
				new Database_Statement('?', array('b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => new SQL_Expression('b'))),
				new Database_Statement('b')
			),
			array(
				new SQL_Expression(':a', array(':a' => new SQL_Identifier('b'))),
				new Database_Statement('"b"')
			),
			array(
				new SQL_Expression(':a', array(':a' => new SQL_Table('b'))),
				new Database_Statement('"pre_b"')
			),

			// data set #9
			array(
				new SQL_Expression('?', array(array())),
				new Database_Statement('')
			),
			array(
				new SQL_Expression('?', array(array('a', 'b'))),
				new Database_Statement('?, ?', array('a', 'b'))
			),

			// data set #11
			array(
				new SQL_Expression('?', array(array(new SQL_Expression('a'), 'b'))),
				new Database_Statement('a, ?', array('b'))
			),
			array(
				new SQL_Expression('?', array(array(new SQL_Identifier('a'), 'b'))),
				new Database_Statement('"a", ?', array('b'))
			),
			array(
				new SQL_Expression('?', array(array(new SQL_Table('a'), 'b'))),
				new Database_Statement('"pre_a", ?', array('b'))
			),

			// data set #14
			array(
				new SQL_Expression(':a', array(':a' => array('b', new SQL_Expression('c')))),
				new Database_Statement('?, c', array('b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => array('b', new SQL_Identifier('c')))),
				new Database_Statement('?, "c"', array('b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => array('b', new SQL_Table('c')))),
				new Database_Statement('?, "pre_c"', array('b'))
			),
		);

		return $result;
	}

	/**
	 * @covers  Database::_parse
	 * @covers  Database::_parse_value
	 * @covers  Database::parse_statement
	 *
	 * @dataProvider    provider_parse_statement
	 *
	 * @param   SQL_Expression      $argument   Argument to the method
	 * @param   Database_Statement  $expected   Expected result
	 */
	public function test_parse_statement($argument, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('', array(
			'table_prefix' => 'pre_',
		)));

		$this->assertEquals($expected, $db->parse_statement($argument));
	}

	/**
	 * @covers  Database::_parse_value
	 */
	public function test_parse_statement_bound()
	{
		$db = $this->getMockForAbstractClass('Database', array('', array()));

		$expression = new SQL_Expression('? :a');
		$expression->bind(0, $var);
		$expression->bind(':a', $var);

		$statement = $db->parse_statement($expression);

		$this->assertSame(array(0 => NULL, 1 => NULL), $statement->parameters());

		$var = 1;
		$this->assertSame(array(0 => 1, 1 => 1), $statement->parameters());
	}

	public function provider_query()
	{
		return array(
			array(array('a'), new Database_Query('a')),
			array(array('a', array()), new Database_Query('a', array())),
			array(array('a', array('b')), new Database_Query('a', array('b'))),
		);
	}

	/**
	 * @covers  Database::query
	 *
	 * @dataProvider    provider_query
	 *
	 * @param   array           $arguments
	 * @param   Database_Query  $expected
	 */
	public function test_query($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::query', $arguments)
		);
	}

	public function provider_query_set()
	{
		return array(
			array(array(), new Database_Query_Set),
			array(array(new SQL_Expression('a')), new Database_Query_Set(new SQL_Expression('a'))),
		);
	}

	/**
	 * @covers  Database::query_set
	 *
	 * @dataProvider    provider_query_set
	 *
	 * @param   array               $arguments
	 * @param   Database_Query_Set  $expected
	 */
	public function test_query_set($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::query_set', $arguments)
		);
	}

	public function provider_select()
	{
		return array(
			array(array(), new Database_Select),
			array(array(array('a' => 'b')), new Database_Select(array('a' => 'b'))),
		);
	}

	/**
	 * @covers  Database::select
	 *
	 * @dataProvider    provider_select
	 *
	 * @param   array           $arguments
	 * @param   Database_Select $expected
	 */
	public function test_select($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::select', $arguments)
		);
	}

	public function provider_update()
	{
		return array(
			array(array(), new Database_Update),
			array(array('a'), new Database_Update('a')),
			array(array('a', 'b'), new Database_Update('a', 'b')),
			array(array('a', 'b', array('c' => 'd')), new Database_Update('a', 'b', array('c' => 'd'))),
		);
	}

	/**
	 * @covers  Database::update
	 *
	 * @dataProvider    provider_update
	 *
	 * @param   array           $arguments
	 * @param   Database_Update $expected
	 */
	public function test_update($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database::update', $arguments)
		);
	}
}
