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

	public function test_factories_dynamic()
	{
		$db = $this->sharedFixture;

		$this->assertType('Database_Binary', $db->binary(''));
		$this->assertType('Database_DateTime', $db->datetime());

		$this->assertType('Database_Command', $db->command(''));
		$this->assertType('Database_Command_Delete', $db->delete());
		$this->assertType('Database_Command_Insert', $db->insert());
		$this->assertType('Database_Command_Update', $db->update());

		$this->assertType('Database_Query', $db->query(''));
		$this->assertType('Database_Query_Set', $db->query_set());
		$this->assertType('Database_Query_Select', $db->select());

		$this->assertType('Database_Column', $db->column(''));
		$this->assertType('Database_Identifier', $db->identifier(''));
		$this->assertType('Database_Table', $db->table(''));

		$this->assertType('Database_Conditions', $db->conditions());
		$this->assertType('Database_Expression', $db->expression(''));
		$this->assertType('Database_From', $db->from());

		$this->assertType('Database_Command_Alter_Table', $db->alter('table'));
		$this->assertType('Database_Command_Create_Index', $db->create('index'));
		$this->assertType('Database_Command_Create_Table', $db->create('table'));
		$this->assertType('Database_Command_Create_View', $db->create('view'));
		$this->assertType('Database_Command_Drop', $db->drop('index'));
		$this->assertType('Database_Command_Drop_Table', $db->drop('table'));

		$this->assertType('Database_DDL_Column', $db->ddl_column());
		$this->assertType('Database_DDL_Constraint_Check', $db->ddl_constraint('check'));
		$this->assertType('Database_DDL_Constraint_Foreign', $db->ddl_constraint('foreign'));
		$this->assertType('Database_DDL_Constraint_Primary', $db->ddl_constraint('primary'));
		$this->assertType('Database_DDL_Constraint_Unique', $db->ddl_constraint('unique'));
	}

	public function test_factories_static()
	{
		$this->assertType('Database_Binary', Database::binary(''));
		$this->assertType('Database_DateTime', Database::datetime());

		$this->assertType('Database_Command', Database::command(''));
		$this->assertType('Database_Command_Delete', Database::delete());
		$this->assertType('Database_Command_Insert', Database::insert());
		$this->assertType('Database_Command_Update', Database::update());

		$this->assertType('Database_Query', Database::query(''));
		$this->assertType('Database_Query_Set', Database::query_set());
		$this->assertType('Database_Query_Select', Database::select());

		$this->assertType('Database_Column', Database::column(''));
		$this->assertType('Database_Identifier', Database::identifier(''));
		$this->assertType('Database_Table', Database::table(''));

		$this->assertType('Database_Conditions', Database::conditions());
		$this->assertType('Database_Expression', Database::expression(''));
		$this->assertType('Database_From', Database::from());

		$this->assertType('Database_Command_Alter_Table', Database::alter('table'));
		$this->assertType('Database_Command_Create_Index', Database::create('index'));
		$this->assertType('Database_Command_Create_Table', Database::create('table'));
		$this->assertType('Database_Command_Create_View', Database::create('view'));
		$this->assertType('Database_Command_Drop', Database::drop('index'));
		$this->assertType('Database_Command_Drop_Table', Database::drop('table'));

		$this->assertType('Database_DDL_Column', Database::ddl_column());
		$this->assertType('Database_DDL_Constraint_Check', Database::ddl_constraint('check'));
		$this->assertType('Database_DDL_Constraint_Foreign', Database::ddl_constraint('foreign'));
		$this->assertType('Database_DDL_Constraint_Primary', Database::ddl_constraint('primary'));
		$this->assertType('Database_DDL_Constraint_Unique', Database::ddl_constraint('unique'));
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

	/**
	 * @test
	 * @dataProvider    provider_quote_literal
	 */
	public function test_quote_literal($value, $expected_result)
	{
		$db = $this->sharedFixture;
		$result = $db->quote_literal($value);

		$this->assertSame($expected_result, $result);
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

			array(new Database_Base_Database_Test_Object, "'object'"),

			array(array(NULL), '(NULL)'),
			array(array(FALSE), "('0')"),
			array(array(TRUE), "('1')"),

			array(array(51678), '(51678)'),
			array(array(12.345), '(12.345000)'),

			array(array('string'), "('string')"),
			array(array("multiline\nstring"), "('multiline\nstring')"),

			array(array(new Database_Base_Database_Test_Object), "('object')"),

			array(array(NULL, FALSE, TRUE, 51678, 12.345, 'string', "multiline\nstring", new Database_Base_Database_Test_Object), "(NULL, '0', '1', 51678, 12.345000, 'string', 'multiline\nstring', 'object')"),
		);
	}

	/**
	 * @test
	 * @dataProvider    provider_quote_identifier
	 */
	public function test_quote_identifier($value, $expected_result)
	{
		$db = $this->sharedFixture;
		$result = $db->quote_identifier($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_quote_identifier()
	{
		$one = new Database_Identifier('one');

		$two_array = new Database_Identifier('two');
		$two_array->namespace = array('one');

		$two_ident = new Database_Identifier('two');
		$two_ident->namespace = $one;

		$two_string = new Database_Identifier('two');
		$two_string->namespace = 'one';

		$three_array = new Database_Identifier('three');
		$three_array->namespace = array('one','two');

		$three_ident = new Database_Identifier('three');
		$three_ident->namespace = $two_ident;

		$three_string = new Database_Identifier('three');
		$three_string->namespace = 'one.two';

		return array
		(
			array('one', '"one"'),
			array('one.two', '"one"."two"'),
			array('one.two.three', '"one"."two"."three"'),
			array('one.two.three.four', '"one"."two"."three"."four"'),

			array(array('one'), '"one"'),
			array(array('one','two'), '"one"."two"'),
			array(array('one','two','three'), '"one"."two"."three"'),
			array(array('one','two','three','four'), '"one"."two"."three"."four"'),

			array($one, '"one"'),

			array($two_array, '"one"."two"'),
			array($two_ident, '"one"."two"'),
			array($two_string, '"one"."two"'),

			array($three_array, '"one"."two"."three"'),
			array($three_ident, '"one"."two"."three"'),
			array($three_string, '"one"."two"."three"'),
		);
	}

	/**
	 * @test
	 * @dataProvider    provider_quote_table
	 */
	public function test_quote_table($value, $expected_result)
	{
		$db = $this->sharedFixture;
		$result = $db->quote_table($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_quote_table()
	{
		$one = new Database_Identifier('one');

		$two_array = new Database_Identifier('two');
		$two_array->namespace = array('one');

		$two_ident = new Database_Identifier('two');
		$two_ident->namespace = $one;

		$two_string = new Database_Identifier('two');
		$two_string->namespace = 'one';

		return array
		(
			array('one', '"pre_one"'),
			array('one.two', '"one"."pre_two"'),

			array(array('one'), '"pre_one"'),
			array(array('one','two'), '"one"."pre_two"'),

			array($one, '"pre_one"'),

			array($two_array, '"one"."pre_two"'),
			array($two_ident, '"one"."pre_two"'),
			array($two_string, '"one"."pre_two"'),
		);
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
