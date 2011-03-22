<?php

require_once dirname(dirname(dirname(__FILE__))).'/abstract/result/object'.EXT;

/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.result
 */
class Database_Base_Result_Object_Test extends Database_Abstract_Result_Object_Test
{
	protected function _select_all()
	{
		return new Database_Base_Result_Object_Test_Result(array(
			(object) array('value' => 50),
			(object) array('value' => 55),
			(object) array('value' => 60),
		));
	}

	protected function _select_null()
	{
		return new Database_Base_Result_Object_Test_Result(array(
			(object) array('value' => NULL),
		));
	}
}

class Database_Base_Result_Object_Test_Result extends Database_Result
{
	protected $_data;

	public function __construct($data)
	{
		parent::__construct(TRUE, count($data));

		$this->_data = $data;
	}

	public function current()
	{
		return $this->_data[$this->_position];
	}
}
