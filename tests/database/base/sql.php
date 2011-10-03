<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Base_SQL_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), '', '""'),

			array(array(''), '', '""'),
			array(array('pre_'), 'pre_', '""'),

			array(array('', '$'), '', '$$'),
			array(array('', array('a', 'b')), '', 'ab'),

			array(array('pre_', '^'), 'pre_', '^^'),
			array(array('pre_', array('<', '>')), 'pre_', '<>'),
		);
	}

	/**
	 * @covers  SQL::__construct
	 * @covers  SQL::table_prefix
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $prefix     Expected table prefix
	 * @param   string  $quotes     Expected identifier quotes
	 */
	public function test_constructor($arguments, $prefix, $quotes)
	{
		$class = new ReflectionClass('SQL');
		$sql = $class->newInstanceArgs($arguments);

		$this->assertSame($quotes, $sql->quote_identifier(''));
		$this->assertSame($prefix, trim($sql->quote_table(''), $quotes));
		$this->assertSame($prefix, $sql->table_prefix());
	}

	public function provider_alias()
	{
		return array(
			array(array('a', 'b'), new SQL_Alias('a', 'b')),
		);
	}

	/**
	 * @covers  SQL::alias
	 *
	 * @dataProvider    provider_alias
	 *
	 * @param   array       $arguments
	 * @param   SQL_Alias   $expected
	 */
	public function test_alias($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::alias', $arguments)
		);
	}

	public function provider_alter_table()
	{
		return array(
			array(array(), new SQL_DDL_Alter_Table()),
			array(array('a'), new SQL_DDL_Alter_Table('a')),
		);
	}

	/**
	 * @covers  SQL::alter_table
	 *
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array               $arguments
	 * @param   SQL_DDL_Alter_Table $expected
	 */
	public function test_alter_table($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::alter_table', $arguments)
		);
	}

	/**
	 * @covers  SQL::bind
	 */
	public function test_bind()
	{
		$db = new SQL;

		$bound = SQL::bind($var);
		$this->assertSame('NULL', $db->quote($bound));

		$var = 1;
		$this->assertSame('1', $db->quote($bound));
	}

	public function provider_column()
	{
		return array(
			array(array('a'), new SQL_Column('a')),
		);
	}

	/**
	 * @covers  SQL::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array       $arguments
	 * @param   SQL_Column  $expected
	 */
	public function test_column($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::column', $arguments)
		);
	}

	public function provider_conditions()
	{
		return array(
			array(array(), new SQL_Conditions),
			array(array('a'), new SQL_Conditions('a')),
			array(array('a', '='), new SQL_Conditions('a', '=')),
			array(array('a', '=', 'b'), new SQL_Conditions('a', '=', 'b')),
		);
	}

	/**
	 * @covers  SQL::conditions
	 *
	 * @dataProvider    provider_conditions
	 *
	 * @param   array           $arguments
	 * @param   SQL_Conditions  $expected
	 */
	public function test_conditions($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::conditions', $arguments)
		);
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
	 * @covers  SQL::create_index
	 *
	 * @dataProvider    provider_create_index
	 *
	 * @param   array                   $arguments
	 * @param   SQL_DDL_Create_Index    $expected
	 */
	public function test_create_index($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::create_index', $arguments)
		);
	}

	public function provider_create_table()
	{
		return array(
			array(array(), new SQL_DDL_Create_Table),
			array(array('a'), new SQL_DDL_Create_Table('a')),
		);
	}

	/**
	 * @covers  SQL::create_table
	 *
	 * @dataProvider    provider_create_table
	 *
	 * @param   array                   $arguments
	 * @param   SQL_DDL_Create_Table    $expected
	 */
	public function test_create_table($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::create_table', $arguments)
		);
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
	 * @covers  SQL::create_view
	 *
	 * @dataProvider    provider_create_view
	 *
	 * @param   array               $arguments
	 * @param   SQL_DDL_Create_View $expected
	 */
	public function test_create_view($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::create_view', $arguments)
		);
	}

	public function provider_datatype()
	{
		return array(
			array(array('binary'), array('type' => 'binary', 'exact' => TRUE)),
			array(array('varchar'), array('type' => 'string')),

			array(array('blob', 'type'), 'binary'),
			array(array('float', 'type'), 'float'),
			array(array('integer', 'type'), 'integer'),
			array(array('varchar', 'type'), 'string'),

			array(array('not-a-type'), array()),
			array(array('not-a-type', 'type'), NULL),
		);
	}

	/**
	 * @covers  SQL::datatype
	 *
	 * @dataProvider    provider_datatype
	 *
	 * @param   array   $arguments
	 * @param   mixed   $expected
	 */
	public function test_datatype($arguments, $expected)
	{
		$sql = new SQL;

		$this->assertSame(
			$expected, call_user_func_array(array($sql, 'datatype'), $arguments)
		);
	}

	public function provider_ddl_check()
	{
		return array(
			array(array(), new SQL_DDL_Constraint_Check),
			array(array(new SQL_Conditions), new SQL_DDL_Constraint_Check(new SQL_Conditions)),
		);
	}

	/**
	 * @covers  SQL::ddl_check
	 *
	 * @dataProvider    provider_ddl_check
	 *
	 * @param   array                       $arguments
	 * @param   SQL_DDL_Constraint_Check    $expected
	 */
	public function test_ddl_check($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::ddl_check', $arguments)
		);
	}

	public function provider_ddl_column()
	{
		return array(
			array(array(), new SQL_DDL_Column),
			array(array('a'), new SQL_DDL_Column('a')),
			array(array('a', 'b'), new SQL_DDL_Column('a', 'b')),
		);
	}

	/**
	 * @covers  SQL::ddl_column
	 *
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array           $arguments
	 * @param   SQL_DDL_Column  $expected
	 */
	public function test_ddl_column($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::ddl_column', $arguments)
		);
	}

	public function provider_ddl_foreign()
	{
		return array(
			array(array(), new SQL_DDL_Constraint_Foreign),
			array(array('a'), new SQL_DDL_Constraint_Foreign('a')),
			array(array('a', array('b')), new SQL_DDL_Constraint_Foreign('a', array('b'))),
		);
	}

	/**
	 * @covers  SQL::ddl_foreign
	 *
	 * @dataProvider    provider_ddl_foreign
	 *
	 * @param   array                       $arguments
	 * @param   SQL_DDL_Constraint_Foreign  $expected
	 */
	public function test_ddl_foreign($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::ddl_foreign', $arguments)
		);
	}

	public function provider_ddl_primary()
	{
		return array(
			array(array(), new SQL_DDL_Constraint_Primary),
			array(array(array('a')), new SQL_DDL_Constraint_Primary(array('a'))),
		);
	}

	/**
	 * @covers  SQL::ddl_primary
	 *
	 * @dataProvider    provider_ddl_primary
	 *
	 * @param   array                       $arguments
	 * @param   SQL_DDL_Constraint_Primary  $expected
	 */
	public function test_ddl_primary($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::ddl_primary', $arguments)
		);
	}

	public function provider_ddl_unique()
	{
		return array(
			array(array(), new SQL_DDL_Constraint_Unique),
			array(array(array('a')), new SQL_DDL_Constraint_Unique(array('a'))),
		);
	}

	/**
	 * @covers  SQL::ddl_unique
	 *
	 * @dataProvider    provider_ddl_unique
	 *
	 * @param   array                       $arguments
	 * @param   SQL_DDL_Constraint_Unique   $expected
	 */
	public function test_ddl_unique($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::ddl_unique', $arguments)
		);
	}

	public function provider_delete()
	{
		return array(
			array(array(), new SQL_DML_Delete),
			array(array('a'), new SQL_DML_Delete('a')),
			array(array('a', 'b'), new SQL_DML_Delete('a', 'b')),
		);
	}

	/**
	 * @covers  SQL::delete
	 *
	 * @dataProvider    provider_delete
	 *
	 * @param   array           $arguments
	 * @param   SQL_DML_Delete  $expected
	 */
	public function test_delete($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::delete', $arguments)
		);
	}

	public function provider_drop()
	{
		return array(
			array(array('index'), new SQL_DDL_Drop('index')),
			array(array('index', 'a'), new SQL_DDL_Drop('index', 'a')),
			array(
				array('index', 'a', FALSE),
				new SQL_DDL_Drop('index', 'a', FALSE),
			),
			array(
				array('index', 'a', TRUE),
				new SQL_DDL_Drop('index', 'a', TRUE),
			),

			array(array('table'), new SQL_DDL_Drop('table')),
			array(array('table', 'a'), new SQL_DDL_Drop('table', 'a')),
			array(
				array('table', 'a', FALSE),
				new SQL_DDL_Drop('table', 'a', FALSE),
			),
			array(
				array('table', 'a', TRUE),
				new SQL_DDL_Drop('table', 'a', TRUE),
			),
		);
	}

	/**
	 * @covers  SQL::drop
	 *
	 * @dataProvider    provider_drop
	 *
	 * @param   array           $arguments
	 * @param   SQL_DDL_Drop    $expected
	 */
	public function test_drop($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::drop', $arguments)
		);
	}

	public function provider_drop_table()
	{
		return array(
			array(array(), new SQL_DDL_Drop_Table),
			array(array('a'), new SQL_DDL_Drop_Table('a')),
			array(array('a', FALSE), new SQL_DDL_Drop_Table('a', FALSE)),
			array(array('a', TRUE), new SQL_DDL_Drop_Table('a', TRUE)),
		);
	}

	/**
	 * @covers  SQL::drop_table
	 *
	 * @dataProvider    provider_drop_table
	 *
	 * @param   array               $arguments
	 * @param   SQL_DDL_Drop_Table  $expected
	 */
	public function test_drop_table($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::drop_table', $arguments)
		);
	}

	public function provider_expression()
	{
		return array(
			array(array('a'), new SQL_Expression('a')),
			array(array('a', array('b')), new SQL_Expression('a', array('b'))),
		);
	}

	/**
	 * @covers  SQL::expression
	 *
	 * @dataProvider    provider_expression
	 *
	 * @param   array           $arguments
	 * @param   SQL_Expression  $expected
	 */
	public function test_expression($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::expression', $arguments)
		);
	}

	public function provider_identical()
	{
		return array(
			array(array('a', '=', 'b'), new SQL_Identical('a', '=', 'b')),
			array(array('a', '<>', 'b'), new SQL_Identical('a', '<>', 'b')),
		);
	}

	/**
	 * @covers  SQL::identical
	 *
	 * @dataProvider    provider_identical
	 *
	 * @param   array           $arguments
	 * @param   SQL_Identical   $expected
	 */
	public function test_identical($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::identical', $arguments)
		);
	}

	public function provider_identifier()
	{
		return array(
			array(array('a'), new SQL_Identifier('a')),
		);
	}

	/**
	 * @covers  SQL::identifier
	 *
	 * @dataProvider    provider_identifier
	 *
	 * @param   array           $arguments
	 * @param   SQL_Identifier  $expected
	 */
	public function test_identifier($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::identifier', $arguments)
		);
	}

	public function provider_insert()
	{
		return array(
			array(array(), new SQL_DML_Insert),
			array(array('a'), new SQL_DML_Insert('a')),
			array(array('a', array('b')), new SQL_DML_Insert('a', array('b'))),
		);
	}

	/**
	 * @covers  SQL::insert
	 *
	 * @dataProvider    provider_insert
	 *
	 * @param   array           $arguments
	 * @param   SQL_DML_Insert  $expected
	 */
	public function test_insert($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::insert', $arguments)
		);
	}

	public function provider_query_set()
	{
		return array(
			array(array(), new SQL_DML_Set),
			array(array(new SQL_Expression('a')), new SQL_DML_Set(new SQL_Expression('a'))),
		);
	}

	/**
	 * @covers  SQL::query_set
	 *
	 * @dataProvider    provider_query_set
	 *
	 * @param   array       $arguments
	 * @param   SQL_DML_Set $expected
	 */
	public function test_query_set($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::query_set', $arguments)
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
	 * @covers  SQL::quote_literal
	 *
	 * @dataProvider    provider_quote_literal
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_literal($value, $expected)
	{
		$db = new SQL;

		$this->assertSame($expected, $db->quote_literal($value));
	}

	/**
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  SQL::quote_literal
	 */
	public function test_quote_literal_object()
	{
		$db = new SQL;

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
	 * @covers  SQL::quote_identifier
	 *
	 * @dataProvider    provider_quote_identifier
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_identifier($value, $expected)
	{
		$sql = new SQL('pre_', array('<', '>'));

		$this->assertSame($expected, $sql->quote_identifier($value));
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
	 * @covers  SQL::quote_table
	 *
	 * @dataProvider    provider_quote_table
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_table($value, $expected)
	{
		$sql = new SQL('pre_', array('<', '>'));

		$this->assertSame($expected, $sql->quote_table($value));
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
	 * @covers  SQL::quote_column
	 *
	 * @dataProvider    provider_quote_column
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_column($value, $expected)
	{
		$sql = new SQL('pre_', array('<', '>'));

		$this->assertSame($expected, $sql->quote_column($value));
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
	 * @covers  SQL::quote_expression
	 *
	 * @dataProvider    provider_quote_expression
	 *
	 * @param   SQL_Expression  $value      Argument
	 * @param   string          $expected
	 */
	public function test_quote_expression($value, $expected)
	{
		$db = new SQL;

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
	 * @covers  SQL::quote_expression
	 *
	 * @dataProvider    provider_quote_expression_lacking_parameter
	 *
	 * @param   SQL_Expression  $value  Argument
	 */
	public function test_quote_expression_lacking_parameter($value)
	{
		$db = new SQL;

		if (error_reporting() & E_NOTICE)
		{
			$this->setExpectedException(
				'ErrorException', 'Undefined', E_NOTICE
			);

			$db->quote_expression($value);
		}
		else
		{
			$this->assertSame('NULL', $db->quote_expression($value));
		}
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
	 * @covers  SQL::quote
	 *
	 * @dataProvider    provider_quote
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote($value, $expected)
	{
		$sql = new SQL('pre_');

		$this->assertSame($expected, $sql->quote($value));
	}

	/**
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  SQL::quote
	 */
	public function test_quote_object()
	{
		$db = new SQL;

		$object = $this->getMock('stdClass', array('__toString'));
		$object->expects($this->exactly(3))
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$this->assertSame("'object__toString'", $db->quote($object));
		$this->assertSame("'object__toString', 'object__toString'", $db->quote(array($object, $object)));
	}

	public function provider_reference()
	{
		return array(
			array(array(), new SQL_Table_Reference),
			array(array('a'), new SQL_Table_Reference('a')),
			array(array('a', 'b'), new SQL_Table_Reference('a', 'b')),
		);
	}

	/**
	 * @covers  SQL::reference
	 *
	 * @dataProvider    provider_reference
	 *
	 * @param   array               $arguments
	 * @param   SQL_Table_Reference $expected
	 */
	public function test_reference($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::reference', $arguments)
		);
	}

	public function provider_select()
	{
		return array(
			array(array(), new SQL_DML_Select),
			array(array(array('a' => 'b')), new SQL_DML_Select(array('a' => 'b'))),
		);
	}

	/**
	 * @covers  SQL::select
	 *
	 * @dataProvider    provider_select
	 *
	 * @param   array           $arguments
	 * @param   SQL_DML_Select  $expected
	 */
	public function test_select($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::select', $arguments)
		);
	}

	public function provider_table()
	{
		return array(
			array(array('a'), new SQL_Table('a')),
		);
	}

	/**
	 * @covers  SQL::table
	 *
	 * @dataProvider    provider_table
	 *
	 * @param   array       $arguments
	 * @param   SQL_Table   $expected
	 */
	public function test_table($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::table', $arguments)
		);
	}

	public function provider_update()
	{
		return array(
			array(array(), new SQL_DML_Update),
			array(array('a'), new SQL_DML_Update('a')),
			array(array('a', 'b'), new SQL_DML_Update('a', 'b')),
			array(array('a', 'b', array('c' => 'd')), new SQL_DML_Update('a', 'b', array('c' => 'd'))),
		);
	}

	/**
	 * @covers  SQL::update
	 *
	 * @dataProvider    provider_update
	 *
	 * @param   array           $arguments
	 * @param   SQL_DML_Update  $expected
	 */
	public function test_update($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('SQL::update', $arguments)
		);
	}
}
