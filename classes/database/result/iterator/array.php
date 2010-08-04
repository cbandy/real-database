<?php

/**
 * @package     RealDatabase
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Result_Iterator_Array extends Database_Result_Iterator
{
	/**
	 * @var array
	 */
	protected $_results;

	/**
	 * @param   array   $results
	 */
	public function __construct($results)
	{
		$this->_results = $results;
	}

	public function current()
	{
		return $this->_results[$this->_position];
	}

	public function valid()
	{
		return ($this->_position < count($this->_results));
	}
}
