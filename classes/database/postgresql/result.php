<?php

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PostgreSQL_Result extends Database_Result
{
	protected $_result;

	/**
	 * @param   resource    $result
	 * @param   mixed       $as_object
	 */
	public function __construct($result, $as_object)
	{
		parent::__construct($result, $as_object);

		if ($as_object === TRUE)
		{
			$this->_as_object = 'stdClass';
		}

		$this->_count = pg_num_rows($result);
		$this->_result = $result;
	}

	public function __destruct()
	{
		pg_free_result($this->_result);
	}

	public function as_array($key = NULL, $value = NULL)
	{
		if ($this->_count === 0)
			return array();

		if ( ! $this->_as_object AND $key === NULL)
		{
			if ($value === NULL)
			{
				// Indexed rows
				return pg_fetch_all($this->_result);
			}

			// Indexed columns
			return pg_fetch_all_columns($this->_result, pg_field_num($this->_result, $value));
		}

		return parent::as_array($key, $value);
	}

	public function current()
	{
		if ($this->_as_object)
			return pg_fetch_object($this->_result, $this->_position, $this->_as_object);

		return pg_fetch_assoc($this->_result, $this->_position);
	}
}
