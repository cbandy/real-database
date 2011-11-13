<?php
/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_SQLite_DML_Set_Test extends PHPUnit_Framework_TestCase
{
	public function provider_add_empty()
	{
		$db = Database::factory();

		$result = array(
			array(NULL, new SQL_Expression(''), ''),
			array(NULL, new SQL_Expression('a'), 'a'),
			array(NULL, new SQL_DML_Select, 'SELECT *'),

			array('a', new SQL_Expression(''), ''),
			array('a', new SQL_Expression('b'), 'b'),
			array('a', new SQL_DML_Select, 'SELECT *'),
		);

		$select = new SQL_DML_Select;
		$select->limit(0);

		$result[] = array(NULL, $select, 'SELECT * FROM (SELECT * LIMIT 0)');
		$result[] = array('a', $select, 'SELECT * FROM (SELECT * LIMIT 0)');

		$select = new SQL_DML_Select;
		$select->limit(5);

		$result[] = array(NULL, $select, 'SELECT * FROM (SELECT * LIMIT 5)');
		$result[] = array('a', $select, 'SELECT * FROM (SELECT * LIMIT 5)');

		$select = new SQL_DML_Select;
		$select->order_by('a');

		$result[] = array(NULL, $select,
			'SELECT * FROM (SELECT * ORDER BY '.$db->quote_column('a').')'
		);
		$result[] = array('b', $select,
			'SELECT * FROM (SELECT * ORDER BY '.$db->quote_column('a').')'
		);

		return $result;
	}

	/**
	 * @covers  Database_SQLite_DML_Set::add
	 *
	 * @dataProvider    provider_add_empty
	 *
	 * @param   string          $operator   First argument to the method
	 * @param   SQL_Expression  $query      Second argument to the method
	 * @param   string          $expected
	 */
	public function test_add_empty($operator, $query, $expected)
	{
		$db = Database::factory();
		$statement = new Database_SQLite_DML_Set;

		$this->assertSame(
			$statement, $statement->add($operator, $query), 'Chainable'
		);

		$this->assertSame($expected, $db->quote($statement));
	}

	public function provider_add_not_empty()
	{
		$db = Database::factory();

		$result = array(
			array(NULL, new SQL_Expression(''), '  '),
			array(NULL, new SQL_Expression('a'), '  a'),
			array(NULL, new SQL_DML_Select, '  SELECT *'),

			array('a', new SQL_Expression(''), ' A '),
			array('a', new SQL_Expression('b'), ' A b'),
			array('a', new SQL_DML_Select, ' A SELECT *'),
		);

		$select = new SQL_DML_Select;
		$select->limit(0);

		$result[] = array(NULL, $select, '  SELECT * FROM (SELECT * LIMIT 0)');
		$result[] = array('a', $select, ' A SELECT * FROM (SELECT * LIMIT 0)');

		$select = new SQL_DML_Select;
		$select->limit(5);

		$result[] = array(NULL, $select, '  SELECT * FROM (SELECT * LIMIT 5)');
		$result[] = array('a', $select, ' A SELECT * FROM (SELECT * LIMIT 5)');

		$select = new SQL_DML_Select;
		$select->order_by('a');

		$result[] = array(NULL, $select,
			'  SELECT * FROM (SELECT * ORDER BY '.$db->quote_column('a').')'
		);
		$result[] = array('b', $select,
			' B SELECT * FROM (SELECT * ORDER BY '.$db->quote_column('a').')'
		);

		return $result;
	}

	/**
	 * @covers  Database_SQLite_DML_Set::add
	 *
	 * @dataProvider    provider_add_not_empty
	 *
	 * @param   string          $operator   First argument to the method
	 * @param   SQL_Expression  $query      Second argument to the method
	 * @param   string          $expected
	 */
	public function test_add_not_empty($operator, $query, $expected)
	{
		$db = Database::factory();
		$statement = new Database_SQLite_DML_Set(new SQL_Expression(''));

		$this->assertSame(
			$statement, $statement->add($operator, $query), 'Chainable'
		);

		$this->assertSame($expected, $db->quote($statement));
	}
}
