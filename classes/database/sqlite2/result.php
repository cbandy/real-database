<?php

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Result Sets
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
	 * @param   mixed           $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 */
	public function __construct($result, $as_object)
	{
		parent::__construct($as_object);

		if ($as_object === TRUE)
		{
			$this->_as_object = 'stdClass';
		}

		$this->_count = $result->numRows();
		$this->_result = $result;
	}

	public function as_array($key = NULL, $value = NULL)
	{
		if ($this->_count === 0)
			return array();

		if ( ! $this->_as_object AND $key === NULL AND $value === NULL)
		{
			$this->_result->rewind();

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

	public function get($name = NULL, $default = NULL)
	{
		if ($this->_as_object OR $name !== NULL)
			return parent::get($name, $default);

		if ($this->valid())
		{
			if ($this->_result->key() !== $this->_position)
			{
				$this->_result->seek($this->_position);
			}

			if (($result = $this->_result->fetchSingle()) !== NULL)
				return $result;
		}

		return $default;
	}
}
