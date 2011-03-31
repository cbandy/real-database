<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 */
class Database_Base_Exception_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array
		(
			array('a', array(), 'b', 'a', 0),
			array(':a', array(':a' => 'b'), 'c', 'b', 0),

			// @ticket 3358
			array('a', array(), '3F000', 'a', 3),

			// @ticket 3404
			array('a', array(), '42S22', 'a', 42),
		);
	}

	/**
	 * @covers  Database_Exception::__construct
	 * @covers  Database_Exception::code
	 *
	 * @dataProvider  provider_constructor
	 *
	 * @ticket  3358
	 * @ticket  3404
	 *
	 * @param   string  $message    First argument
	 * @param   array   $variables  Second argument
	 * @param   string  $str_code
	 * @param   string  $expected   Expected message
	 * @param   integer $int_code   Expected code
	 */
	public function test_constructor($message, $variables, $str_code, $expected, $int_code)
	{
		$exception = new Database_Exception($message, $variables, $str_code);

		$this->assertSame($expected, $exception->getMessage());
		$this->assertSame($int_code, $exception->getCode());
		$this->assertSame($str_code, $exception->code());
	}
}
