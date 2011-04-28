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
	public function test_constructor()
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame('name', (string) $mock);
	}

	public function provider_constructor_quote()
	{
		return array
		(
			array('$', '$$'),
			array(array('a', 'b'), 'ab'),
		);
	}

	/**
	 * @covers  Database::__construct
	 *
	 * @dataProvider    provider_constructor_quote
	 *
	 * @param   string|array    $quote      Argument
	 * @param   string          $expected
	 */
	public function test_constructor_quote($quote, $expected)
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array(), $quote));

		$this->assertSame($expected, $mock->quote_identifier(''));
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
	 * @covers  Database::factory
	 * @expectedException   Kohana_Exception
	 */
	public function test_factory_incomplete_config()
	{
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

	public function provider_alter_table()
	{
		return array(
			array(array(), new SQL_DDL_Alter_Table()),
			array(array('a'), new SQL_DDL_Alter_Table('a')),
		);
	}

	/**
	 * @covers  Database::alter_table
	 *
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array               $arguments
	 * @param   SQL_DDL_Alter_Table $expected
	 */
	public function test_alter_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database::alter_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_create_index()
	{
		return array(
			array(array(), new SQL_DDL_Create_Index),
			array(array('a'), new SQL_DDL_Create_Index('a')),
			array(array('a', 'b'), new SQL_DDL_Create_Index('a', 'b')),
			array(array('a', 'b', array('c')), new SQL_DDL_Create_Index('a', 'b', array('c'))),
		);
	}

	/**
	 * @covers  Database::create_index
	 *
	 * @dataProvider    provider_create_index
	 *
	 * @param   array                   $arguments
	 * @param   SQL_DDL_Create_Index    $expected
	 */
	public function test_create_index($arguments, $expected)
	{
		$statement = call_user_func_array('Database::create_index', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_create_table()
	{
		return array(
			array(array(), new SQL_DDL_Create_Table),
			array(array('a'), new SQL_DDL_Create_Table('a')),
		);
	}

	/**
	 * @covers  Database::create_table
	 *
	 * @dataProvider    provider_create_table
	 *
	 * @param   array                   $arguments
	 * @param   SQL_DDL_Create_Table    $expected
	 */
	public function test_create_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database::create_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_create_view()
	{
		return array(
			array(array(), new SQL_DDL_Create_View),
			array(array('a'), new SQL_DDL_Create_View('a')),
			array(array('a', new SQL_Expression('b')), new SQL_DDL_Create_View('a', new SQL_Expression('b'))),
		);
	}

	/**
	 * @covers  Database::create_view
	 *
	 * @dataProvider    provider_create_view
	 *
	 * @param   array               $arguments
	 * @param   SQL_DDL_Create_View $expected
	 */
	public function test_create_view($arguments, $expected)
	{
		$statement = call_user_func_array('Database::create_view', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_datatype()
	{
		return array
		(
			array('blob', 'type', 'binary'),
			array('float', 'type', 'float'),
			array('integer', 'type', 'integer'),
			array('varchar', 'type', 'string'),

			array('varchar', NULL, array('type' => 'string')),

			array('not-a-type', 'type', NULL),
			array('not-a-type', NULL, array()),
		);
	}

	/**
	 * @covers  Database::datatype
	 * @dataProvider    provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame($expected, $db->datatype($type, $attribute));
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

	/**
	 * @covers  Database::binary
	 * @covers  Database::column
	 * @covers  Database::conditions
	 * @covers  Database::datetime
	 * @covers  Database::ddl_column
	 * @covers  Database::ddl_constraint
	 * @covers  Database::delete
	 * @covers  Database::drop
	 * @covers  Database::expression
	 * @covers  Database::identifier
	 * @covers  Database::insert
	 * @covers  Database::query
	 * @covers  Database::query_set
	 * @covers  Database::reference
	 * @covers  Database::select
	 * @covers  Database::table
	 * @covers  Database::update
	 * @dataProvider    provider_factories
	 */
	public function test_factories($method, $arguments, $expected)
	{
		$result = call_user_func_array("Database::$method", $arguments);
		$this->assertEquals($expected, $result);
	}

	public function provider_factories()
	{
		$result = array
		(
			array('binary', array('a'), new Database_Binary('a')),

			array('column', array('a'), new SQL_Column('a')),

			array('conditions', array(), new SQL_Conditions),
			array('conditions', array('a'), new SQL_Conditions('a')),
			array('conditions', array('a', '='), new SQL_Conditions('a', '=')),
			array('conditions', array('a', '=', 'b'), new SQL_Conditions('a', '=', 'b')),

			array('datetime', array(1258461296), new Database_DateTime(1258461296)),
			array('datetime', array(1258461296, 'UTC'), new Database_DateTime(1258461296, 'UTC')),
			array('datetime', array(1258461296, 'UTC', 'Y-m-d'), new Database_DateTime(1258461296, 'UTC', 'Y-m-d')),

			array('ddl_column', array(), new SQL_DDL_Column),
			array('ddl_column', array('a'), new SQL_DDL_Column('a')),
			array('ddl_column', array('a', 'b'), new SQL_DDL_Column('a', 'b')),

			array('ddl_constraint', array('check'), new SQL_DDL_Constraint_Check),
			array('ddl_constraint', array('foreign'), new SQL_DDL_Constraint_Foreign),
			array('ddl_constraint', array('primary'), new SQL_DDL_Constraint_Primary),
			array('ddl_constraint', array('unique'), new SQL_DDL_Constraint_Unique),

			array('delete', array(), new Database_Delete),
			array('delete', array('a'), new Database_Delete('a')),
			array('delete', array('a', 'b'), new Database_Delete('a', 'b')),

			array('drop', array('index'), new SQL_DDL_Drop('index')),
			array('drop', array('index', 'a'), new SQL_DDL_Drop('index', 'a')),

			array('drop', array('table'), new SQL_DDL_Drop_Table),
			array('drop', array('table', 'a'), new SQL_DDL_Drop_Table('a')),

			array('expression', array('a'), new SQL_Expression('a')),
			array('expression', array('a', array('b')), new SQL_Expression('a', array('b'))),

			array('identifier', array('a'), new SQL_Identifier('a')),

			array('insert', array(), new Database_Insert),
			array('insert', array('a'), new Database_Insert('a')),
			array('insert', array('a', array('b')), new Database_Insert('a', array('b'))),

			array('query', array('a'), new Database_Query('a')),
			array('query', array('a', array('b')), new Database_Query('a', array('b'))),

			array('query_set', array(), new Database_Query_Set),
			array('query_set', array(new Database_Query('a')), new Database_Query_Set(new Database_Query('a'))),

			array('reference', array(), new SQL_Table_Reference),
			array('reference', array('a'), new SQL_Table_Reference('a')),
			array('reference', array('a', 'b'), new SQL_Table_Reference('a', 'b')),

			array('select', array(), new Database_Select),
			array('select', array(array('a' => 'b')), new Database_Select(array('a' => 'b'))),

			array('table', array('a'), new SQL_Table('a')),

			array('update', array(), new Database_Update),
			array('update', array('a'), new Database_Update('a')),
			array('update', array('a', 'b'), new Database_Update('a', 'b')),
			array('update', array('a', 'b', array('c' => 'd')), new Database_Update('a', 'b', array('c' => 'd'))),
		);

		$constraint = new SQL_DDL_Constraint_Check;
		$constraint->name('a');
		$result[] = array('ddl_constraint', array('check', 'a'), $constraint);

		$constraint = new SQL_DDL_Constraint_Foreign;
		$constraint->name('a');
		$result[] = array('ddl_constraint', array('foreign', 'a'), $constraint);

		$constraint = new SQL_DDL_Constraint_Primary;
		$constraint->name('a');
		$result[] = array('ddl_constraint', array('primary', 'a'), $constraint);

		$constraint = new SQL_DDL_Constraint_Unique;
		$constraint->name('a');
		$result[] = array('ddl_constraint', array('unique', 'a'), $constraint);

		return $result;
	}

	public function provider_quote_literal()
	{
		return array
		(
			array(NULL, 'NULL'),
			array(FALSE, "'0'"),
			array(TRUE, "'1'"),

			array(0, '0'),
			array(-1, '-1'),
			array(51678, '51678'),
			array(12.345, '12.345000'),

			array('string', "'string'"),
			array("multiline\nstring", "'multiline\nstring'"),

			array(array(NULL), '(NULL)'),
			array(array(FALSE), "('0')"),
			array(array(TRUE), "('1')"),

			array(array(51678), '(51678)'),
			array(array(12.345), '(12.345000)'),

			array(array('string'), "('string')"),
			array(array("multiline\nstring"), "('multiline\nstring')"),
		);
	}

	/**
	 * @covers  Database::quote_literal
	 * @dataProvider    provider_quote_literal
	 */
	public function test_quote_literal($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame($expected, $db->quote_literal($value));
	}

	/**
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  Database::quote_literal
	 */
	public function test_quote_literal_object()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$object = $this->getMock('stdClass', array('__toString'));
		$object->expects($this->exactly(2))
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$this->assertSame("'object__toString'", $db->quote_literal($object));
		$this->assertSame("('object__toString')", $db->quote_literal(array($object)));
	}

	public function provider_quote_identifier()
	{
		$one = new SQL_Identifier('one');

		$two_array = new SQL_Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new SQL_Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new SQL_Identifier('two');
		$two_string->namespace = 'one';

		$three_array = new SQL_Identifier('three');
		$three_array->namespace = array('one','two');

		$three_identifier = new SQL_Identifier('three');
		$three_identifier->namespace = $two_identifier;

		$three_string = new SQL_Identifier('three');
		$three_string->namespace = 'one.two';

		return array
		(
			// Strings
			array('one',                '<one>'),
			array('one.two',            '<one>.<two>'),
			array('one.two.three',      '<one>.<two>.<three>'),
			array('one.two.three.four', '<one>.<two>.<three>.<four>'),

			// Arrays of strings
			array(array('one'),                      '<one>'),
			array(array('one','two'),                '<one>.<two>'),
			array(array('one','two','three'),        '<one>.<two>.<three>'),
			array(array('one','two','three','four'), '<one>.<two>.<three>.<four>'),

			// Identifier, no namespace
			array($one, '<one>'),

			// Identifier, one namespace
			array($two_array,      '<one>.<two>'),
			array($two_identifier, '<one>.<two>'),
			array($two_string,     '<one>.<two>'),

			// Identifier, two namespaces
			array($three_array,      '<one>.<two>.<three>'),
			array($three_identifier, '<one>.<two>.<three>'),
			array($three_string,     '<one>.<two>.<three>'),
		);
	}

	/**
	 * @covers  Database::quote_identifier
	 * @dataProvider    provider_quote_identifier
	 */
	public function test_quote_identifier($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(), array('<','>')));

		$this->assertSame($expected, $db->quote_identifier($value));
	}

	public function provider_quote_table()
	{
		$one = new SQL_Identifier('one');

		$two_array = new SQL_Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new SQL_Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new SQL_Identifier('two');
		$two_string->namespace = 'one';

		return array
		(
			// Strings
			array('one',     '<pre_one>'),
			array('one.two', '<one>.<pre_two>'),

			// Array of strings
			array(array('one'),       '<pre_one>'),
			array(array('one','two'), '<one>.<pre_two>'),

			// Identifier, no namespace
			array($one, '<pre_one>'),

			// Identifier, one namespace
			array($two_array,      '<one>.<pre_two>'),
			array($two_identifier, '<one>.<pre_two>'),
			array($two_string,     '<one>.<pre_two>'),
		);
	}

	/**
	 * @covers  Database::quote_table
	 * @dataProvider    provider_quote_table
	 */
	public function test_quote_table($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(), array('<','>')));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame($expected, $db->quote_table($value));
	}

	public function provider_quote_column()
	{
		$one = new SQL_Identifier('one');

		$two_array = new SQL_Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new SQL_Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new SQL_Identifier('two');
		$two_string->namespace = 'one';

		$two_table = new SQL_Identifier('two');
		$two_table->namespace = new SQL_Table('one');

		$three_array = new SQL_Identifier('three');
		$three_array->namespace = array('one','two');

		$three_identifier = new SQL_Identifier('three');
		$three_identifier->namespace = $two_identifier;

		$three_string = new SQL_Identifier('three');
		$three_string->namespace = 'one.two';

		$three_table = new SQL_Identifier('three');
		$three_table->namespace = new SQL_Table('one.two');

		$one_star = new SQL_Identifier('*');
		$two_star = new SQL_Identifier('one.*');
		$three_star = new SQL_Identifier('one.two.*');

		return array
		(
			// Strings
			array('one',            '<one>'),
			array('one.two',        '<pre_one>.<two>'),
			array('one.two.three',  '<one>.<pre_two>.<three>'),

			// Array of strings
			array(array('one'),                 '<one>'),
			array(array('one','two'),           '<pre_one>.<two>'),
			array(array('one','two','three'),   '<one>.<pre_two>.<three>'),

			// Identifiers, no namespace
			array($one, '<one>'),

			// Identifiers, one namespace
			array($two_array,       '<pre_one>.<two>'),
			array($two_identifier,  '<one>.<two>'),
			array($two_string,      '<pre_one>.<two>'),
			array($two_table,       '<pre_one>.<two>'),

			// Identifiers, two namespaces
			array($three_array,         '<one>.<pre_two>.<three>'),
			array($three_identifier,    '<one>.<two>.<three>'),
			array($three_string,        '<one>.<pre_two>.<three>'),
			array($three_table,         '<one>.<pre_two>.<three>'),

			// Strings with asterisks
			array('*',          '*'),
			array('one.*',      '<pre_one>.*'),
			array('one.two.*',  '<one>.<pre_two>.*'),

			// Arrays of strings with asterisks
			array(array('*'),               '*'),
			array(array('one','*'),         '<pre_one>.*'),
			array(array('one','two','*'),   '<one>.<pre_two>.*'),

			// Identifiers with asterisks
			array($one_star,    '*'),
			array($two_star,    '<pre_one>.*'),
			array($three_star,  '<one>.<pre_two>.*'),
		);
	}

	/**
	 * @covers  Database::quote_column
	 * @dataProvider    provider_quote_column
	 */
	public function test_quote_column($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array(), array('<','>')));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame($expected, $db->quote_column($value));
	}

	public function provider_quote_expression()
	{
		return array
		(
			// No arguments
			array(new SQL_Expression(''),          ''),
			array(new SQL_Expression('expr'),      'expr'),
			array(new SQL_Expression('?'),         '?'),
			array(new SQL_Expression(':param'),    ':param'),

			// Empty
			array(new SQL_Expression('', array(NULL)), ''),
			array(new SQL_Expression('', array(1)),    ''),
			array(new SQL_Expression('', array('a')),  ''),

			// No parameters
			array(new SQL_Expression('expr', array(NULL)), 'expr'),
			array(new SQL_Expression('expr', array(1)),    'expr'),
			array(new SQL_Expression('expr', array('a')),  'expr'),

			// Positional parameter
			array(new SQL_Expression('?', array(NULL)),    'NULL'),
			array(new SQL_Expression('?', array(1)),       '1'),
			array(new SQL_Expression('?', array('a')),     "'a'"),

			array(new SQL_Expression('before ?', array(1)),        'before 1'),
			array(new SQL_Expression('? after', array(1)),         '1 after'),
			array(new SQL_Expression('before ? after', array(1)),  'before 1 after'),

			// Positional Parameters
			array(new SQL_Expression('? split ?', array(1, 2)),                '1 split 2'),
			array(new SQL_Expression('before ? split ?', array(1, 2)),         'before 1 split 2'),
			array(new SQL_Expression('? split ? after', array(1, 2)),          '1 split 2 after'),
			array(new SQL_Expression('before ? split ? after', array(1, 2)),   'before 1 split 2 after'),

			// Named parameter
			array(new SQL_Expression(':param', array(':param' => NULL)),   'NULL'),
			array(new SQL_Expression(':param', array(':param' => 1)),      '1'),
			array(new SQL_Expression(':param', array(':param' => 'a')),    "'a'"),

			array(new SQL_Expression('before :param', array(':param' => 1)),       'before 1'),
			array(new SQL_Expression(':param after', array(':param' => 1)),        '1 after'),
			array(new SQL_Expression('before :param after', array(':param' => 1)), 'before 1 after'),

			// Named parameters
			array(new SQL_Expression(':a split :b', array(':a' => 1, ':b' => 2)),              '1 split 2'),
			array(new SQL_Expression('before :a split :b', array(':a' => 1, ':b' => 2)),       'before 1 split 2'),
			array(new SQL_Expression(':a split :b after', array(':a' => 1, ':b' => 2)),        '1 split 2 after'),
			array(new SQL_Expression('before :a split :b after', array(':a' => 1, ':b' => 2)), 'before 1 split 2 after'),
		);
	}

	/**
	 * @covers  Database::quote_expression
	 * @dataProvider    provider_quote_expression
	 */
	public function test_quote_expression($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame($expected, $db->quote_expression($value));
	}

	public function provider_quote_expression_lacking_parameter()
	{
		return array
		(
			array(new SQL_Expression('?', array(1 => NULL))),
			array(new SQL_Expression('?', array(1 => 2))),
			array(new SQL_Expression('?', array(1 => 'a'))),

			array(new SQL_Expression(':param', array(NULL))),
			array(new SQL_Expression(':param', array(1))),
			array(new SQL_Expression(':param', array('a'))),
		);
	}

	/**
	 * @covers  Database::quote_expression
	 * @dataProvider    provider_quote_expression_lacking_parameter
	 * @expectedException   PHPUnit_Framework_Error
	 */
	public function test_quote_expression_lacking_parameter($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$db->quote_expression($value);
	}

	public function provider_quote()
	{
		return array
		(
			// Literals
			array(NULL, 'NULL'),
			array(1,    '1'),
			array('a',  "'a'"),

			// Expression
			array(new SQL_Expression('expr'), 'expr'),

			// Identifiers
			array(new SQL_Identifier('one.two'),    '"one"."two"'),
			array(new SQL_Column('one.two'),        '"pre_one"."two"'),
			array(new SQL_Table('one.two'),         '"one"."pre_two"'),

			// Array
			array(array(NULL, 1 ,'a'), "NULL, 1, 'a'"),
		);
	}

	/**
	 * @covers  Database::quote
	 *
	 * @dataProvider    provider_quote
	 *
	 * @param   mixed   $value      Argument to the method
	 * @param   string  $expected   Expected result
	 */
	public function test_quote($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame($expected, $db->quote($value));
	}

	/**
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  Database::quote
	 */
	public function test_quote_object()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$object = $this->getMock('stdClass', array('__toString'));
		$object->expects($this->exactly(3))
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$this->assertSame("'object__toString'", $db->quote($object));
		$this->assertSame("'object__toString', 'object__toString'", $db->quote(array($object, $object)));
	}
}
