<?php

/**
 * Result set for a PostgreSQL resource.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/pgsql.resources
 */
class Database_PostgreSQL_Result extends Database_Result
{
	/**
	 * @var resource    From pg_query() or pg_get_result()
	 */
	protected $_result;

	/**
	 * @param   resource        $result     From pg_query() or pg_get_result()
	 * @param   string|boolean  $as_object  Row object class, TRUE for stdClass or FALSE for associative array
	 */
	public function __construct($result, $as_object)
	{
		parent::__construct($as_object);

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

	public function get($name = NULL, $default = NULL)
	{
		if ($this->_as_object)
			return parent::get($name, $default);

		if ($this->valid()
			AND ($name === NULL OR ($name = pg_field_num($this->_result, $name)) >= 0)
			AND ($result = pg_fetch_result($this->_result, $this->_position, $name)) !== NULL)
		{
			// Field exists and is not NULL
			return $result;
		}

		return $default;
	}
}
