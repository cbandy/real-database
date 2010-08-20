<?php

require_once dirname(dirname(dirname(__FILE__))).'/abstract/result/assoc'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.result
 */
class Database_Base_Result_Array_Test extends Database_Abstract_Result_Assoc_Test
{
	protected function _select_all()
	{
		return new Database_Result_Array(array(
			array('value' => 50),
			array('value' => 55),
			array('value' => 60),
		), FALSE);
	}

	protected function _select_null()
	{
		return new Database_Result_Array(array(
			array('value' => NULL),
		), FALSE);
	}
}
