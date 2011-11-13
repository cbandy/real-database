<?php

/**
 * CREATE VIEW statement for MySQL. Allows the ALGORITHM and CHECK OPTION to be specified.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-view.html
 */
class Database_MySQL_DDL_Create_View extends SQL_DDL_Create_View
{
	/**
	 * @var string  MERGE, TEMPTABLE or UNDEFINED
	 */
	protected $_algorithm;

	/**
	 * @var string  CASCADED or LOCAL check option
	 */
	protected $_check;

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->_replace)
		{
			$value .= ' OR REPLACE';
		}

		if ($this->_algorithm)
		{
			$value .= ' ALGORITHM = '.$this->_algorithm;
		}

		$value .= ' VIEW :name';

		if ( ! empty($this->parameters[':columns']))
		{
			$value .= ' (:columns)';
		}

		$value .= ' AS :query';

		if ($this->_check)
		{
			$value .= ' WITH '.$this->_check.' CHECK OPTION';
		}

		return $value;
	}

	/**
	 * Set the algorithm used to process the view
	 *
	 * @link http://dev.mysql.com/doc/en/view-algorithms.html
	 *
	 * @param   string  $value  MERGE, TEMPTABLE or UNDEFINED
	 * @return  $this
	 */
	public function algorithm($value)
	{
		$this->_algorithm = strtoupper($value);

		return $this;
	}

	/**
	 * Set the CHECK OPTION clause for an updatable view
	 *
	 * @link http://dev.mysql.com/doc/en/view-updatability.html
	 *
	 * @param   string  $value  CASCADED or LOCAL
	 * @return  $this
	 */
	public function check($value)
	{
		$this->_check = strtoupper($value);

		return $this;
	}
}
