<?php

/**
 * @package     RealDatabase
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Result_Array extends Database_Result
{
	/**
	 * @var array   Rows
	 */
	protected $_data;

	/**
	 * @param   array           $data       Rows
	 * @param   string|boolean  $as_object  Class of each row, TRUE for stdClass or FALSE for associative array
	 */
	public function __construct($data, $as_object)
	{
		parent::__construct($as_object);

		$this->_count = count($data);
		$this->_data = $data;
	}

	public function as_array($key = NULL, $value = NULL)
	{
		if ($key === NULL AND $value === NULL OR $this->_count === 0)
			return $this->_data;

		return parent::as_array($key, $value);
	}

	public function current()
	{
		return $this->_data[$this->_position];
	}
}
