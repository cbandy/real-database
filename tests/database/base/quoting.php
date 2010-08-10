<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.quoting
 */
class Database_Base_Quoting_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 * @dataProvider    provider_literal
	 */
	public function test_literal($value, $expected_result)
	{
		$db = new Database_Quoting_Test_DB;
		$result = $db->quote_literal($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_literal()
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

			array(new Database_Quoting_Test_String, "'object'"),

			array(array(NULL), '(NULL)'),
			array(array(FALSE), "('0')"),
			array(array(TRUE), "('1')"),

			array(array(51678), '(51678)'),
			array(array(12.345), '(12.345000)'),

			array(array('string'), "('string')"),
			array(array("multiline\nstring"), "('multiline\nstring')"),

			array(array(new Database_Quoting_Test_String), "('object')"),

			array(array(NULL, FALSE, TRUE, 51678, 12.345, 'string', "multiline\nstring", new Database_Quoting_Test_String), "(NULL, '0', '1', 51678, 12.345000, 'string', 'multiline\nstring', 'object')"),
		);
	}

	/**
	 * @test
	 * @dataProvider    provider_identifier
	 */
	public function test_identifier($value, $expected_result)
	{
		$db = new Database_Quoting_Test_DB;
		$result = $db->quote_identifier($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_identifier()
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
	 * @dataProvider    provider_table
	 */
	public function test_table($value, $expected_result)
	{
		$db = new Database_Quoting_Test_DB;
		$result = $db->quote_table($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_table()
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
	 * @dataProvider    provider_column
	 */
	public function test_column($value, $expected_result)
	{
		$db = new Database_Quoting_Test_DB;
		$result = $db->quote_column($value);

		$this->assertSame($expected_result, $result);
	}

	public function provider_column()
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
		$db = new Database_Quoting_Test_DB;
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

			array(new Database_Quoting_Test_String, "'object'"),

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
					new Database_Quoting_Test_String,
					NULL, FALSE, TRUE,
					0, -1, 51678, 12.345,
					'string', "multiline\nstring",
				),
				'"one"."pre_two".*, "one"."pre_two"."three", "one"."two"."pre_three", "one"."two"."three", expression, '."'object', NULL, '0', '1', 0, -1, 51678, 12.345000, 'string', 'multiline\nstring'"
			),
		);
	}
}


class Database_Quoting_Test_DB extends Database
{
	public function __construct($name = NULL, $config = NULL) {}

	public function begin() {}

	public function commit() {}

	public function connect() {}

	public function disconnect() {}

	public function execute_command($statement) {}

	public function execute_query($statement, $as_object = FALSE) {}

	public function rollback() {}

	public function table_prefix()
	{
		return 'pre_';
	}
}

class Database_Quoting_Test_String
{
	public function __toString()
	{
		return 'object';
	}
}
