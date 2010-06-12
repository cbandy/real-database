<?php

/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_MySQL_Result extends Database_Result
{
	/**
	 * @var integer Position of the result resource
	 */
	protected $_internal_position = 0;

	/**
	 * @var resource    From mysql_query()
	 */
	protected $_result;

	/**
	 * @param   resource    $result     From mysql_query()
	 * @param   mixed       $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 */
	public function __construct($result, $as_object)
	{
		parent::__construct($result, $as_object);

		if ($as_object === TRUE)
		{
			$this->_as_object = 'stdClass';
		}

		$this->_count = mysql_num_rows($result);
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

		if ($this->_as_object)
		{
			// Raises E_WARNING when class does not exist
			return mysql_fetch_object($this->_result, $this->_as_object);
		}

		return mysql_fetch_assoc($this->_result);
	}

	public function get($name = NULL, $default = NULL)
	{
		if ($this->_as_object OR $name !== NULL)
			return parent::get($name, $default);

		if ($this->valid()
			AND ($result = mysql_result($this->_result, $this->_position)) !== NULL)
		{
			return $result;
		}

		return $default;
	}
}
