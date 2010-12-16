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
	 * @test
	 * @dataProvider    provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->datatype($type, $attribute));
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
	 * @test
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
			// Datatypes

			array('binary', array('a'), new Database_Binary('a')),

			array('datetime', array(1258461296), new Database_DateTime(1258461296)),
			array('datetime', array(1258461296, 'UTC'), new Database_DateTime(1258461296, 'UTC')),
			array('datetime', array(1258461296, 'UTC', 'Y-m-d'), new Database_DateTime(1258461296, 'UTC', 'Y-m-d')),

			// Expressions

			array('conditions', array(), new Database_Conditions),
			array('conditions', array('a'), new Database_Conditions('a')),
			array('conditions', array('a', '='), new Database_Conditions('a', '=')),
			array('conditions', array('a', '=', 'b'), new Database_Conditions('a', '=', 'b')),

			array('expression', array('a'), new Database_Expression('a')),
			array('expression', array('a', array('b')), new Database_Expression('a', array('b'))),

			array('from', array(), new Database_From),
			array('from', array('a'), new Database_From('a')),
			array('from', array('a', 'b'), new Database_From('a', 'b')),

			// Identifiers

			array('column', array('a'), new Database_Column('a')),
			array('identifier', array('a'), new Database_Identifier('a')),
			array('table', array('a'), new Database_Table('a')),

			// Commands

			array('command', array('a'), new Database_Command('a')),
			array('command', array('a', array('b')), new Database_Command('a', array('b'))),

			array('delete', array(), new Database_Command_Delete),
			array('delete', array('a'), new Database_Command_Delete('a')),
			array('delete', array('a', 'b'), new Database_Command_Delete('a', 'b')),

			array('insert', array(), new Database_Command_Insert),
			array('insert', array('a'), new Database_Command_Insert('a')),
			array('insert', array('a', array('b')), new Database_Command_Insert('a', array('b'))),

			array('update', array(), new Database_Command_Update),
			array('update', array('a'), new Database_Command_Update('a')),
			array('update', array('a', 'b'), new Database_Command_Update('a', 'b')),
			array('update', array('a', 'b', array('c' => 'd')), new Database_Command_Update('a', 'b', array('c' => 'd'))),

			// Queries

			array('query', array('a'), new Database_Query('a')),
			array('query', array('a', array('b')), new Database_Query('a', array('b'))),

			array('query_set', array(), new Database_Query_Set),
			array('query_set', array(new Database_Query('a')), new Database_Query_Set(new Database_Query('a'))),

			array('select', array(), new Database_Query_Select),
			array('select', array(array('a' => 'b')), new Database_Query_Select(array('a' => 'b'))),

			// DDL Commands

			array('alter', array('table'), new Database_Command_Alter_Table),
			array('alter', array('table', 'a'), new Database_Command_Alter_Table('a')),

			array('create', array('index'), new Database_Command_Create_Index),
			array('create', array('index', 'a'), new Database_Command_Create_Index('a')),

			array('create', array('table'), new Database_Command_Create_Table),
			array('create', array('table', 'a'), new Database_Command_Create_Table('a')),

			array('create', array('view'), new Database_Command_Create_View),
			array('create', array('view', 'a'), new Database_Command_Create_View('a')),

			array('drop', array('index'), new Database_Command_Drop('index')),
			array('drop', array('index', 'a'), new Database_Command_Drop('index', 'a')),

			array('drop', array('table'), new Database_Command_Drop_Table),
			array('drop', array('table', 'a'), new Database_Command_Drop_Table('a')),

			// DDL Expressions

			array('ddl_column', array(), new Database_DDL_Column),
			array('ddl_column', array('a'), new Database_DDL_Column('a')),
			array('ddl_column', array('a', 'b'), new Database_DDL_Column('a', 'b')),

			array('ddl_constraint', array('check'), new Database_DDL_Constraint_Check),
			array('ddl_constraint', array('foreign'), new Database_DDL_Constraint_Foreign),
			array('ddl_constraint', array('primary'), new Database_DDL_Constraint_Primary),
			array('ddl_constraint', array('unique'), new Database_DDL_Constraint_Unique),
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

	/**
	 * @test
	 * @dataProvider    provider_prepare_command
	 */
	public function test_prepare_command($statement, $parameters)
	{
		$db = $this->sharedFixture;
		$result = $db->prepare_command($statement, $parameters);

		$this->assertType('Database_Prepared_Command', $result);
		$this->assertEquals($statement, (string) $result);
		$this->assertEquals($parameters, $result->parameters);
	}

	public function provider_prepare_command()
	{
		return array
		(
			array('', array()),
		);
	}

	/**
	 * @test
	 * @dataProvider    provider_prepare_query
	 */
	public function test_prepare_query($statement, $parameters)
	{
		$db = $this->sharedFixture;
		$result = $db->prepare_query($statement, $parameters);

		$this->assertType('Database_Prepared_Query', $result);
		$this->assertEquals($statement, (string) $result);
		$this->assertEquals($parameters, $result->parameters);
	}

	public function provider_prepare_query()
	{
		return array
		(
			array('', array()),
		);
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
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->quote_literal($value));
	}

	/**
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  Database::quote_literal
	 */
	public function test_quote_literal_object()
	{
		$db = $this->sharedFixture;

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
		$db = $this->sharedFixture;

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
		$db = $this->sharedFixture;

		$this->assertSame($expected, $db->quote_table($value));
	}

	/**
	 * @test
	 * @dataProvider    provider_quote_column
	 */
	public function test_quote_column($value, $expected_result)
	{
		$db = $this->sharedFixture;
		$result = $db->quote_column($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_quote_column()
	{
		$one = new Database_Identifier('one');

		$two_array = new Database_Identifier('two');
		$two_array->namespace = array('one');

		$two_ident = new Database_Identifier('two');
		$two_ident->namespace = $one;

		$two_string = new Database_Identifier('two');
		$two_string->namespace = 'one';

		$two_table = new Database_Identifier('two');
		$two_table->namespace = new Database_Table('one');

		$three_array = new Database_Identifier('three');
		$three_array->namespace = array('one','two');

		$three_ident = new Database_Identifier('three');
		$three_ident->namespace = $two_ident;

		$three_string = new Database_Identifier('three');
		$three_string->namespace = 'one.two';

		$three_table = new Database_Identifier('three');
		$three_table->namespace = new Database_Table('two');
		$three_table->namespace->namespace = 'one';

		$one_star = new Database_Identifier('*');
		$two_star = new Database_Identifier('one.*');
		$three_star = new Database_Identifier('one.two.*');

		return array
		(
			array('one', '"one"'),
			array('one.two', '"pre_one"."two"'),
			array('one.two.three', '"one"."pre_two"."three"'),

			array(array('one'), '"one"'),
			array(array('one','two'), '"pre_one"."two"'),
			array(array('one','two','three'), '"one"."pre_two"."three"'),

			array($one, '"one"'),

			array($two_array, '"pre_one"."two"'),
			array($two_ident, '"one"."two"'),
			array($two_string, '"pre_one"."two"'),
			array($two_table, '"pre_one"."two"'),

			array($three_array, '"one"."pre_two"."three"'),
			array($three_ident, '"one"."two"."three"'),
			array($three_string, '"one"."pre_two"."three"'),
			array($three_table, '"one"."pre_two"."three"'),

			array('*', '*'),
			array('one.*', '"pre_one".*'),
			array('one.two.*', '"one"."pre_two".*'),

			array(array('*'), '*'),
			array(array('one','*'), '"pre_one".*'),
			array(array('one','two','*'), '"one"."pre_two".*'),

			array($one_star, '*'),
			array($two_star, '"pre_one".*'),
			array($three_star, '"one"."pre_two".*'),
		);
	}

	/**
	 * @test
	 * @dataProvider    provider_quote
	 */
	public function test_quote($value, $expected_result)
	{
		$db = $this->sharedFixture;
		$result = $db->quote($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_quote()
	{
		return array
		(
			array(new Database_Column('one.two.*'), '"one"."pre_two".*'),
			array(new Database_Column('one.two.three'), '"one"."pre_two"."three"'),

			array(new Database_Table('one.two.three'), '"one"."two"."pre_three"'),

			array(new Database_Identifier('one.two.three'), '"one"."two"."three"'),

			array(new Database_Expression('expression'), 'expression'),

			array(new Database_Base_Database_Test_Object, "'object'"),

			array(NULL, 'NULL'),
			array(FALSE, "'0'"),
			array(TRUE, "'1'"),

			array(0, '0'),
			array(-1, '-1'),
			array(51678, '51678'),

			array('string', "'string'"),
			array("multiline\nstring", "'multiline\nstring'"),

			array(
				array(
					new Database_Column('one.two.*'),
					new Database_Column('one.two.three'),
					new Database_Table('one.two.three'),
					new Database_Identifier('one.two.three'),
					new Database_Expression('expression'),
					new Database_Base_Database_Test_Object,
					NULL, FALSE, TRUE,
					0, -1, 51678, 12.345,
					'string', "multiline\nstring",
				),
				'"one"."pre_two".*, "one"."pre_two"."three", "one"."two"."pre_three", "one"."two"."three", expression, '."'object', NULL, '0', '1', 0, -1, 51678, 12.345000, 'string', 'multiline\nstring'"
			),
		);
	}

	/**
	 * @expectedException   Kohana_Exception
	 */
	public function test_instance_incomplete_config()
	{
		if ( ! $name = Database_Base_TestSuite_Database::testsuite_generate_instance_name())
			$this->markTestSkipped('Unable to find unused instance name');

		Database::instance($name, array());
	}

	public function test_instance_load_config()
	{
		if ( ! $name = Database_Base_TestSuite_Database::testsuite_generate_instance_name())
			$this->markTestSkipped('Unable to find unused instance name');

		$config = Kohana::config('database');
		$config[$name] = array('type' => 'Base_TestSuite_Database');

		$result = Database::instance($name);

		$this->assertType('Database_Base_TestSuite_Database', $result);
		$this->assertSame($name, (string) $result);

		Database_Base_TestSuite_Database::testsuite_unset_instance($name);
	}
}


class Database_Base_Database_Test_Object
{
	public function __toString()
	{
		return 'object';
	}
}
