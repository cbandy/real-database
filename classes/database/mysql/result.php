<?php

/**
 * Result set for a MySQL resource.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/mysql.resources
 */
class Database_MySQL_Result extends Database_Result
{
	/**
	 * @var array   Arguments to pass to the class constructor
	 */
	protected $_arguments;

	/**
	 * @var integer Position of the result resource
	 */
	protected $_internal_position = 0;

	/**
	 * @var resource    From mysql_query()
	 */
	protected $_result;

	/**
	 * @param   resource        $result     From mysql_query()
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the class constructor
	 */
	public function __construct($result, $as_object, $arguments)
	{
		parent::__construct($as_object, mysql_num_rows($result));

		$this->_arguments = $arguments;
		$this->_result = $result;
	}

	public function __destruct()
	{
		mysql_free_result($this->_result);
	}

	public function as_array($key = NULL, $value = NULL)
	{
		if ($this->_count === 0)
			return array();

		return parent::as_array($key, $value);
	}

	public function current()
	{
		if ($this->_internal_position !== $this->_position)
		{
			// Raises E_WARNING when position is out of bounds
			if ( ! mysql_data_seek($this->_result, $this->_position))
				throw new OutOfBoundsException;

			$this->_internal_position = $this->_position + 1;
		}
		else
		{
			++$this->_internal_position;
		}

		// Associative array
		if ( ! $this->_as_object)
			return mysql_fetch_assoc($this->_result);

		// Object without constructor arguments
		if ( ! $this->_arguments)
			return mysql_fetch_object($this->_result, $this->_as_object);

		// Object with constructor arguments
		return mysql_fetch_object(
			$this->_result,
			$this->_as_object,
			$this->_arguments
		);
	}

	public function get($name = NULL, $default = NULL)
	{
		if ($this->_as_object OR $name !== NULL)
			return parent::get($name, $default);

		if ($this->valid())
		{
			$this->_internal_position = $this->_position + 1;

			if (($result = mysql_result($this->_result, $this->_position)) !== NULL)
				return $result;
		}

		return $default;
	}
}
