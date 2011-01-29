<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.identifiers
 */
class Database_SQL_Identifier_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		$a = new stdClass;
		$b = new stdClass;
		$c = new stdClass;

		return array(
			array('', '', array()),
			array('a', 'a', array()),
			array('b.c', 'c', array('b')),
			array('d.e.f', 'f', array('d','e')),
			array(array(), NULL, array()),
			array(array('a'), 'a', array()),
			array(array('b','c'), 'c', array('b')),
			array(array('d','e','f'), 'f', array('d','e')),
			array(array($a), $a, array()),
			array(array($a, $b), $b, array($a)),
			array(array($a, $b, $c), $c, array($a, $b)),
		);
	}

	/**
	 * @covers  SQL_Identifier::__construct
	 * @dataProvider    provider_constructor
	 *
	 * @param   array|string    $argument   Argument to the constructor
	 * @param   mixed           $name       Expected name
	 * @param   mixed           $namespace  Expected namespace
	 */
	public function test_constructor($argument, $name, $namespace)
	{
		$identifier = new SQL_Identifier($argument);

		$this->assertSame($name, $identifier->name);
		$this->assertSame($namespace, $identifier->namespace);
	}
}
