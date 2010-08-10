<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.datatypes
 */
class Database_Base_Numeric_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var string  Backup of the LC_NUMERIC locale
	 */
	protected $_locale_numeric;

	public function setUp()
	{
		$this->_locale_numeric = setlocale(LC_NUMERIC, '0');
	}

	public function tearDown()
	{
		setlocale(LC_NUMERIC, $this->_locale_numeric);
	}

	public function test_constructor()
	{
		$this->assertSame('12', (string) new Database_Numeric(12.345, 0));
		$this->assertSame('12.35', (string) new Database_Numeric(12.345, 2));
		$this->assertSame('12.3450', (string) new Database_Numeric(12.345, 4));

		$this->assertSame('1234.56', (string) new Database_Numeric(1234.56, 2));
	}

	/**
	 * Test in a locale that uses non-SQL separators
	 */
	public function test_locale()
	{
		if ( ! setlocale(LC_NUMERIC, array('de_DE', 'fr_FR', 'nl_NL')))
			return $this->markTestSkipped('Lacking necessary locale');

		$this->assertSame('12.3450', (string) new Database_Numeric(12.345, 4), 'Fraction separator is period');
		$this->assertSame('1234.56', (string) new Database_Numeric(1234.56, 2), 'Thousand separator is absent');
	}
}
