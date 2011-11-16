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
	 * @var array   Arguments to pass to the class constructor
	 */
	protected $_arguments;

	/**
	 * @var resource    From pg_query() or pg_get_result()
	 */
	protected $_result;

	/**
	 * @param   resource        $result     From pg_query() or pg_get_result()
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the class constructor
	 */
	public function __construct($result, $as_object, $arguments)
	{
		parent::__construct($as_object, pg_num_rows($result));

		$this->_arguments = $arguments;
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

		if ($this->_as_object OR $key !== NULL)
			return parent::as_array($key, $value);

		// Indexed rows
		if ($value === NULL)
			return pg_fetch_all($this->_result);

		// Indexed columns
		return pg_fetch_all_columns(
			$this->_result,
			pg_field_num($this->_result, $value)
		);
	}

	/**
	 * Return the current row without validating the current position.
	 * Implements [Iterator::current].
	 *
	 * @return  mixed
	 */
	public function current()
	{
		return $this->fetch($this->_position);
	}

	/**
	 * Retrieve a specific row without moving the pointer.
	 *
	 * Raises E_WARNING and returns FALSE when $position is invalid.
	 *
	 * [!!] Position `-1` does not behave reliably before PHP 5.3.9
	 *
	 * @link http://bugs.php.net/60244
	 *
	 * @param   integer $position
	 * @return  mixed
	 */
	public function fetch($position)
	{
		// Associative array
		if ( ! $this->_as_object)
			return pg_fetch_assoc($this->_result, $position);

		// Object without constructor arguments
		if ( ! $this->_arguments)
			return pg_fetch_object(
				$this->_result,
				$position,
				$this->_as_object
			);

		// Object with constructor arguments
		return pg_fetch_object(
			$this->_result,
			$position,
			$this->_as_object,
			$this->_arguments
		);
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

	/**
	 * Return the row at the specified offset without moving the pointer.
	 * Returns NULL if the offset does not exist. Implements
	 * [ArrayAccess::offsetGet].
	 *
	 * @param   integer $offset
	 * @return  mixed
	 */
	public function offsetGet($offset)
	{
		if ( ! $this->offsetExists($offset))
			return NULL;

		return $this->fetch($offset);
	}
}
