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
	public function test_execute()
	{
		$mock = $this->getMockForAbstractClass('Database', array('name', array()));
		$mock->expects($this->once())
			->method('execute_command');

		$mock->execute('SELECT 1');
	}

	/**
	 * @covers  Database::alter
	 * @covers  Database::binary
	 * @covers  Database::column
	 * @covers  Database::command
	 * @covers  Database::conditions
	 * @covers  Database::create
	 * @covers  Database::datetime
	 * @covers  Database::ddl_column
	 * @covers  Database::ddl_constraint
	 * @covers  Database::delete
	 * @covers  Database::drop
	 * @covers  Database::expression
	 * @covers  Database::from
	 * @covers  Database::identifier
	 * @covers  Database::insert
	 * @covers  Database::query
	 * @covers  Database::query_set
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
			array('alter', array('table'), new Database_Command_Alter_Table),
			array('alter', array('table', 'a'), new Database_Command_Alter_Table('a')),

			array('binary', array('a'), new Database_Binary('a')),

			array('column', array('a'), new Database_Column('a')),

			array('command', array('a'), new Database_Command('a')),
			array('command', array('a', array('b')), new Database_Command('a', array('b'))),

			array('conditions', array(), new Database_Conditions),
			array('conditions', array('a'), new Database_Conditions('a')),
			array('conditions', array('a', '='), new Database_Conditions('a', '=')),
			array('conditions', array('a', '=', 'b'), new Database_Conditions('a', '=', 'b')),

			array('create', array('index'), new Database_Command_Create_Index),
			array('create', array('index', 'a'), new Database_Command_Create_Index('a')),

			array('create', array('table'), new Database_Command_Create_Table),
			array('create', array('table', 'a'), new Database_Command_Create_Table('a')),

			array('create', array('view'), new Database_Command_Create_View),
			array('create', array('view', 'a'), new Database_Command_Create_View('a')),

			array('datetime', array(1258461296), new Database_DateTime(1258461296)),
			array('datetime', array(1258461296, 'UTC'), new Database_DateTime(1258461296, 'UTC')),
			array('datetime', array(1258461296, 'UTC', 'Y-m-d'), new Database_DateTime(1258461296, 'UTC', 'Y-m-d')),

			array('ddl_column', array(), new Database_DDL_Column),
			array('ddl_column', array('a'), new Database_DDL_Column('a')),
			array('ddl_column', array('a', 'b'), new Database_DDL_Column('a', 'b')),

			array('ddl_constraint', array('check'), new Database_DDL_Constraint_Check),
			array('ddl_constraint', array('foreign'), new Database_DDL_Constraint_Foreign),
			array('ddl_constraint', array('primary'), new Database_DDL_Constraint_Primary),
			array('ddl_constraint', array('unique'), new Database_DDL_Constraint_Unique),

			array('delete', array(), new Database_Command_Delete),
			array('delete', array('a'), new Database_Command_Delete('a')),
			array('delete', array('a', 'b'), new Database_Command_Delete('a', 'b')),

			array('drop', array('index'), new Database_Command_Drop('index')),
			array('drop', array('index', 'a'), new Database_Command_Drop('index', 'a')),

			array('drop', array('table'), new Database_Command_Drop_Table),
			array('drop', array('table', 'a'), new Database_Command_Drop_Table('a')),

			array('expression', array('a'), new Database_Expression('a')),
			array('expression', array('a', array('b')), new Database_Expression('a', array('b'))),

			array('from', array(), new Database_From),
			array('from', array('a'), new Database_From('a')),
			array('from', array('a', 'b'), new Database_From('a', 'b')),

			array('identifier', array('a'), new Database_Identifier('a')),

			array('insert', array(), new Database_Command_Insert),
			array('insert', array('a'), new Database_Command_Insert('a')),
			array('insert', array('a', array('b')), new Database_Command_Insert('a', array('b'))),

			array('query', array('a'), new Database_Query('a')),
			array('query', array('a', array('b')), new Database_Query('a', array('b'))),

			array('query_set', array(), new Database_Query_Set),
			array('query_set', array(new Database_Query('a')), new Database_Query_Set(new Database_Query('a'))),

			array('select', array(), new Database_Query_Select),
			array('select', array(array('a' => 'b')), new Database_Query_Select(array('a' => 'b'))),

			array('table', array('a'), new Database_Table('a')),

			array('update', array(), new Database_Command_Update),
			array('update', array('a'), new Database_Command_Update('a')),
			array('update', array('a', 'b'), new Database_Command_Update('a', 'b')),
			array('update', array('a', 'b', array('c' => 'd')), new Database_Command_Update('a', 'b', array('c' => 'd'))),
		);

		$constraint = new Database_DDL_Constraint_Check;
		$constraint->name('a');
		$result[] = array('ddl_constraint', array('check', 'a'), $constraint);

		$constraint = new Database_DDL_Constraint_Foreign;
		$constraint->name('a');
		$result[] = array('ddl_constraint', array('foreign', 'a'), $constraint);

		$constraint = new Database_DDL_Constraint_Primary;
		$constraint->name('a');
		$result[] = array('ddl_constraint', array('primary', 'a'), $constraint);

		$constraint = new Database_DDL_Constraint_Unique;
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
		$one = new Database_Identifier('one');

		$two_array = new Database_Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new Database_Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new Database_Identifier('two');
		$two_string->namespace = 'one';

		$three_array = new Database_Identifier('three');
		$three_array->namespace = array('one','two');

		$three_identifier = new Database_Identifier('three');
		$three_identifier->namespace = $two_identifier;

		$three_string = new Database_Identifier('three');
		$three_string->namespace = 'one.two';

		return array
		(
			// Strings
			array('one',                '"one"'),
			array('one.two',            '"one"."two"'),
			array('one.two.three',      '"one"."two"."three"'),
			array('one.two.three.four', '"one"."two"."three"."four"'),

			// Arrays of strings
			array(array('one'),                      '"one"'),
			array(array('one','two'),                '"one"."two"'),
			array(array('one','two','three'),        '"one"."two"."three"'),
			array(array('one','two','three','four'), '"one"."two"."three"."four"'),

			// Identifier, no namespace
			array($one, '"one"'),

			// Identifier, one namespace
			array($two_array,      '"one"."two"'),
			array($two_identifier, '"one"."two"'),
			array($two_string,     '"one"."two"'),

			// Identifier, two namespaces
			array($three_array,      '"one"."two"."three"'),
			array($three_identifier, '"one"."two"."three"'),
			array($three_string,     '"one"."two"."three"'),
		);
	}

	/**
	 * @covers  Database::quote_identifier
	 * @dataProvider    provider_quote_identifier
	 */
	public function test_quote_identifier($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame($expected, $db->quote_identifier($value));
	}

	public function provider_quote_table()
	{
		$one = new Database_Identifier('one');

		$two_array = new Database_Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new Database_Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new Database_Identifier('two');
		$two_string->namespace = 'one';

		return array
		(
			// Strings
			array('one',     '"pre_one"'),
			array('one.two', '"one"."pre_two"'),

			// Array of strings
			array(array('one'),       '"pre_one"'),
			array(array('one','two'), '"one"."pre_two"'),

			// Identifier, no namespace
			array($one, '"pre_one"'),

			// Identifier, one namespace
			array($two_array,      '"one"."pre_two"'),
			array($two_identifier, '"one"."pre_two"'),
			array($two_string,     '"one"."pre_two"'),
		);
	}

	/**
	 * @covers  Database::quote_table
	 * @dataProvider    provider_quote_table
	 */
	public function test_quote_table($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->once())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$this->assertSame($expected, $db->quote_table($value));
	}

	public function provider_quote_column()
	{
		$one = new Database_Identifier('one');

		$two_array = new Database_Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new Database_Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new Database_Identifier('two');
		$two_string->namespace = 'one';

		$two_table = new Database_Identifier('two');
		$two_table->namespace = new Database_Table('one');

		$three_array = new Database_Identifier('three');
		$three_array->namespace = array('one','two');

		$three_identifier = new Database_Identifier('three');
		$three_identifier->namespace = $two_identifier;

		$three_string = new Database_Identifier('three');
		$three_string->namespace = 'one.two';

		$three_table = new Database_Identifier('three');
		$three_table->namespace = new Database_Table('one.two');

		$one_star = new Database_Identifier('*');
		$two_star = new Database_Identifier('one.*');
		$three_star = new Database_Identifier('one.two.*');

		return array
		(
			// Strings
			array('one',            '"one"'),
			array('one.two',        '"pre_one"."two"'),
			array('one.two.three',  '"one"."pre_two"."three"'),

			// Array of strings
			array(array('one'),                 '"one"'),
			array(array('one','two'),           '"pre_one"."two"'),
			array(array('one','two','three'),   '"one"."pre_two"."three"'),

			// Identifiers, no namespace
			array($one, '"one"'),

			// Identifiers, one namespace
			array($two_array,       '"pre_one"."two"'),
			array($two_identifier,  '"one"."two"'),
			array($two_string,      '"pre_one"."two"'),
			array($two_table,       '"pre_one"."two"'),

			// Identifiers, two namespaces
			array($three_array,         '"one"."pre_two"."three"'),
			array($three_identifier,    '"one"."two"."three"'),
			array($three_string,        '"one"."pre_two"."three"'),
			array($three_table,         '"one"."pre_two"."three"'),

			// Strings with asterisks
			array('*',          '*'),
			array('one.*',      '"pre_one".*'),
			array('one.two.*',  '"one"."pre_two".*'),

			// Arrays of strings with asterisks
			array(array('*'),               '*'),
			array(array('one','*'),         '"pre_one".*'),
			array(array('one','two','*'),   '"one"."pre_two".*'),

			// Identifiers with asterisks
			array($one_star,    '*'),
			array($two_star,    '"pre_one".*'),
			array($three_star,  '"one"."pre_two".*'),
		);
	}

	/**
	 * @covers  Database::quote_column
	 * @dataProvider    provider_quote_column
	 */
	public function test_quote_column($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
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
			array(new Database_Expression(''),          ''),
			array(new Database_Expression('expr'),      'expr'),
			array(new Database_Expression('?'),         '?'),
			array(new Database_Expression(':param'),    ':param'),

			// Empty
			array(new Database_Expression('', array(NULL)), ''),
			array(new Database_Expression('', array(1)),    ''),
			array(new Database_Expression('', array('a')),  ''),

			// No parameters
			array(new Database_Expression('expr', array(NULL)), 'expr'),
			array(new Database_Expression('expr', array(1)),    'expr'),
			array(new Database_Expression('expr', array('a')),  'expr'),

			// Positional parameter
			array(new Database_Expression('?', array(NULL)),    'NULL'),
			array(new Database_Expression('?', array(1)),       '1'),
			array(new Database_Expression('?', array('a')),     "'a'"),

			array(new Database_Expression('before ?', array(1)),        'before 1'),
			array(new Database_Expression('? after', array(1)),         '1 after'),
			array(new Database_Expression('before ? after', array(1)),  'before 1 after'),

			// Positional Parameters
			array(new Database_Expression('? split ?', array(1, 2)),                '1 split 2'),
			array(new Database_Expression('before ? split ?', array(1, 2)),         'before 1 split 2'),
			array(new Database_Expression('? split ? after', array(1, 2)),          '1 split 2 after'),
			array(new Database_Expression('before ? split ? after', array(1, 2)),   'before 1 split 2 after'),

			// Named parameter
			array(new Database_Expression(':param', array(':param' => NULL)),   'NULL'),
			array(new Database_Expression(':param', array(':param' => 1)),      '1'),
			array(new Database_Expression(':param', array(':param' => 'a')),    "'a'"),

			array(new Database_Expression('before :param', array(':param' => 1)),       'before 1'),
			array(new Database_Expression(':param after', array(':param' => 1)),        '1 after'),
			array(new Database_Expression('before :param after', array(':param' => 1)), 'before 1 after'),

			// Named parameters
			array(new Database_Expression(':a split :b', array(':a' => 1, ':b' => 2)),              '1 split 2'),
			array(new Database_Expression('before :a split :b', array(':a' => 1, ':b' => 2)),       'before 1 split 2'),
			array(new Database_Expression(':a split :b after', array(':a' => 1, ':b' => 2)),        '1 split 2 after'),
			array(new Database_Expression('before :a split :b after', array(':a' => 1, ':b' => 2)), 'before 1 split 2 after'),
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
			array(new Database_Expression('?', array(1 => NULL))),
			array(new Database_Expression('?', array(1 => 2))),
			array(new Database_Expression('?', array(1 => 'a'))),

			array(new Database_Expression(':param', array(NULL))),
			array(new Database_Expression(':param', array(1))),
			array(new Database_Expression(':param', array('a'))),
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
			array(array(NULL), 'NULL'),
			array(array(1),    '1'),
			array(array('a'),  "'a'"),

			// Literals with aliases
			array(array(NULL, 'alias'), 'NULL AS "alias"'),
			array(array(1, 'alias'),    '1 AS "alias"'),
			array(array('a', 'alias'),  "'a'".' AS "alias"'),

			// Expression
			array(array(new Database_Expression('expr')),           'expr'),
			array(array(new Database_Expression('expr'), 'alias'),  'expr AS "alias"'),

			// Identifiers
			array(array(new Database_Identifier('one.two')),    '"one"."two"'),
			array(array(new Database_Column('one.two')),        '"pre_one"."two"'),
			array(array(new Database_Table('one.two')),         '"one"."pre_two"'),

			// Identifiers with aliases
			array(array(new Database_Identifier('one.two'), 'alias'),   '"one"."two" AS "alias"'),
			array(array(new Database_Column('one.two'), 'alias'),       '"pre_one"."two" AS "alias"'),
			array(array(new Database_Table('one.two'), 'alias'),        '"one"."pre_two" AS "alias"'),

			// Array
			array(array(array(NULL, 1 ,'a')), "NULL, 1, 'a'"),
		);
	}

	/**
	 * @covers  Database::quote
	 * @dataProvider    provider_quote
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $expected   Expected result
	 */
	public function test_quote($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		if (count($arguments) === 1)
		{
			$result = $db->quote(reset($arguments));
		}
		elseif (count($arguments) === 2)
		{
			$result = $db->quote(reset($arguments), next($arguments));
		}

		$this->assertSame($expected, $result);
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
		$object->expects($this->exactly(4))
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$this->assertSame("'object__toString'", $db->quote($object));
		$this->assertSame("'object__toString'".' AS "alias"', $db->quote($object, 'alias'));
		$this->assertSame("'object__toString', 'object__toString'", $db->quote(array($object, $object)));
	}
}
