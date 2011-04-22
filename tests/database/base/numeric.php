<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.literals
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

	public function provider_constructor()
	{
		return array
		(
			array(12.345, 0, '%.0F'),
			array(12.345, 1, '%.1F'),

			array('12.345', 2, '%.2F'),
			array('12.345', 3, '%.3F'),

			array(12, 4,    '%.4F'),
			array(1234, 5,  '%.5F'),
		);
	}

	/**
	 * @covers  Database_Numeric::__construct
	 * @dataProvider  provider_constructor
	 *
	 * @param   mixed   $value  First argument to the constructor
	 * @param   integer $scale  Second argument to the constructor
	 * @param   string  $format Expected format
	 */
	public function test_constructor($value, $scale, $format)
	{
		$numeric = new Database_Numeric($value, $scale);

		$this->assertSame($value, $numeric->value);
		$this->assertSame($format, $numeric->format);
	}

	/**
	 * Test in a locale that uses non-SQL separators.
	 *
	 * @covers  Database_Numeric::__toString
	 */
	public function test_locale()
	{
		if ( ! setlocale(LC_NUMERIC, array('de_DE', 'deu', 'fr_FR', 'fra', 'nl_NL', 'nld')))
			return $this->markTestSkipped('Lacking necessary locale');

		$this->assertSame('12.3450', (string) new Database_Numeric(12.345, 4), 'Fraction separator is period');
		$this->assertSame('1234.56', (string) new Database_Numeric(1234.56, 2), 'Thousand separator is absent');
	}

	public function provider_toString()
	{
		return array
		(
			// Float Tens
			array(12.345, 0,    '12'),
			array(12.345, 2,    '12.35'),
			array(12.345, 4,    '12.3450'),

			// Float Thousands
			array(1234.56, 0,   '1235'),
			array(1234.56, 2,   '1234.56'),
			array(1234.56, 4,   '1234.5600'),

			// String Tens
			array('12.345', 0,  '12'),
			array('12.345', 2,  '12.35'),
			array('12.345', 4,  '12.3450'),

			// String Thousands
			array('1234.56', 0, '1235'),
			array('1234.56', 2, '1234.56'),
			array('1234.56', 4, '1234.5600'),

			// Integer Tens
			array(12, 0,    '12'),
			array(12, 2,    '12.00'),
			array(12, 4,    '12.0000'),

			// Integer Thousands
			array(1234, 0,  '1234'),
			array(1234, 2,  '1234.00'),
			array(1234, 4,  '1234.0000'),
		);
	}

	/**
	 * @covers  Database_Numeric::__toString
	 * @dataProvider  provider_toString
	 *
	 * @param   mixed   $value      First argument to the constructor
	 * @param   integer $scale      Second argument to the constructor
	 * @param   string  $expected   Expected result
	 */
	public function test_toString($value, $scale, $expected)
	{
		$this->assertSame($expected, (string) new Database_Numeric($value, $scale));
	}
}
