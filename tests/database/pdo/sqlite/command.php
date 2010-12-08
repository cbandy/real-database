<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo.sqlite
 */
class Database_PDO_SQLite_Command_Test extends PHPUnit_Framework_TestCase
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
	 * Binding a variable can convert the value to string.
	 *
	 * @link    http://bugs.php.net/38334
	 *
	 * @dataProvider    provider_bind
	 *
	 * @param   mixed   $initial    Value used when preparing the statement
	 * @param   mixed   $bound      Value after the variable has been bound
	 * @param   mixed   $changed    Value used to alter the bound variable
	 */
	public function test_bind($initial, $bound, $changed)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$command = $db->prepare_command("DELETE FROM $table WHERE $column = ?", array($initial));

		$var = $initial;
		$this->assertSame($command, $command->bind(1, $var), 'Chainable');
		$this->assertSame($bound, $command->parameters[1], 'Parameter visible');
		$this->assertSame($bound, $var, 'Modified by PDO during bind');

		$var = $changed;
		$this->assertSame($changed, $command->parameters[1], 'Changed by reference');
	}

	public function provider_bind()
	{
		return array
		(
			array(NULL, NULL, 'x'),
			array(FALSE, FALSE, 'x'),
			array(TRUE, TRUE, 'x'),
			array(0, 0, 'x'),
			array(1, 1, 'x'),
			array('a', 'a', 'x'),
			array(new Database_Binary('b'), 'b', 'x'),
		);
	}

	protected function _test_bind_execute($value, $expected)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$command = $db->prepare_query("SELECT * FROM $table WHERE $column = ?", array($value));

		$var = $value;
		$command->bind(1, $var);
		$command->execute();

		$this->assertSame($expected, $var, 'Modified by PDO during execution');
	}

	/**
	 * Executing a statement with a bound variable will convert the value to
	 * string in PHP 5.2.
	 *
	 * @dataProvider    provider_bind_execute_52
	 *
	 * @param   mixed   $value      Value used when preparing the statement
	 * @param   string  $expected   Value after the statement is executed
	 */
	public function test_bind_execute_52($value, $expected)
	{
		if (version_compare(PHP_VERSION, '5.3', '>='))
			$this->markTestSkipped();

		$this->_test_bind_execute($value, $expected);
	}

	public function provider_bind_execute_52()
	{
		return array
		(
			array(NULL, '0'),
			array(FALSE, '0'),
			array(TRUE, '1'),
			array(0, '0'),
			array(1, '1'),
			array('a', 'a'),
			array(new Database_Binary('b'), 'b'),
		);
	}

	/**
	 * Executing a statement with a bound variable will convert values other
	 * than NULL to integer or string.
	 *
	 * @dataProvider    provider_bind_execute
	 *
	 * @param   mixed   $value      Value used when preparing the statement
	 * @param   mixed   $expected   Value after the statement is executed
	 */
	public function test_bind_execute($value, $expected)
	{
		if (version_compare(PHP_VERSION, '5.3', '<'))
			$this->markTestSkipped();

		$this->_test_bind_execute($value, $expected);
	}

	public function provider_bind_execute()
	{
		return array
		(
			array(NULL, NULL),
			array(FALSE, 0),
			array(TRUE, 1),
			array(0, 0),
			array(1, 1),
			array('a', 'a'),
			array(new Database_Binary('b'), 'b'),
		);
	}
}
