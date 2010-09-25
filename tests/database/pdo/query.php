<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Query_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';
	protected $_column = 'value';

	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' ('.$column.' integer)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @dataProvider    provider_bind
	 *
	 * @param   mixed   $initial    Value used when preparing the statement
	 * @param   mixed   $changed    Value used to alter the bound variable
	 */
	public function test_bind($initial, $changed)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$query = $db->prepare_query("SELECT * FROM $table WHERE $column = ?", array($initial));

		$var = $initial;
		$this->assertSame($query, $query->bind(1, $var), 'Chainable');
		$this->assertSame($initial, $var, 'Not modified by PDO during bind');
		$this->assertSame($initial, $query->parameters[1], 'Parameter visible');

		$var = $changed;
		$this->assertSame($changed, $query->parameters[1], 'Changed by reference');
	}

	public function provider_bind()
	{
		return array
		(
			array('a', 'b'),
			array(1, 2),
			array(FALSE, TRUE),
		);
	}
}
