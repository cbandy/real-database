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
	protected $_internal_position = 0;
	protected $_result;

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
}
