<?php

/**
 * @package SQLite
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLite2_Result extends Database_Result
{
	/**
	 * @var SQLiteResult
	 */
	protected $_result;

	/**
	 * @param   SQLiteResult    $result
	 * @param   mixed           $as_object
	 */
	public function __construct($result, $as_object)
	{
		parent::__construct($result, $as_object);

		if ($as_object === TRUE)
		{
			$this->_as_object = 'stdClass';
		}

		$this->_count = $result->numRows();
		$this->_result = $result;
	}

	public function as_array($key = NULL, $value = NULL)
	{
		if ( ! $this->_as_object AND $key === NULL AND $value === NULL)
		{
			if ( ! $this->_result->rewind())
				return array();

			return $this->_result->fetchAll(SQLITE_ASSOC);
		}

		return parent::as_array($key, $value);
	}

	public function current()
	{
		if ($this->_result->key() !== $this->_position)
		{
			$this->_result->seek($this->_position);
		}

		if ($this->_as_object)
			return $this->_result->fetchObject($this->_as_object);

		return $this->_result->fetch(SQLITE_ASSOC);
	}
}
