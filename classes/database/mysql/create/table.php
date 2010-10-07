<?php

/**
 * CREATE TABLE statement for MySQL.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html
 */
class Database_MySQL_Create_Table extends Database_Command_Create_Table
{
	/**
	 * @var boolean
	 */
	protected $_if_not_exists;

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->_temporary)
		{
			$value .= ' TEMPORARY';
		}

		$value .= ' TABLE';

		if ($this->_if_not_exists)
		{
			$value .= ' IF NOT EXISTS';
		}

		$value .= ' :name';

		if ( ! empty($this->parameters[':like']))
		{
			$value .= ' LIKE :like';
		}
		else
		{
			if ( ! empty($this->parameters[':columns']))
			{
				$value .= ' (:columns';

				if ( ! empty($this->parameters[':constraints']))
				{
					$value .= ', :constraints';
				}

				$value .= ')';
			}

			if ( ! empty($this->parameters[':options']))
			{
				$value .= ' :options';
			}

			if ( ! empty($this->parameters[':query']))
			{
				$value .= ' AS :query';
			}
		}

		return $value;
	}

	/**
	 * Set whether or not an error should be suppressed if the object exists.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function if_not_exists($value = TRUE)
	{
		$this->_if_not_exists = $value;

		return $this;
	}

	/**
	 * Set the table from which to copy this table definition
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @return  $this
	 */
	public function like($table)
	{
		if ( ! $table instanceof Database_Expression
			AND ! $table instanceof Database_Identifier)
		{
			$table = new Database_Table($table);
		}

		$this->parameters[':like'] = $table;

		return $this;
	}

	/**
	 * Set the table options
	 *
	 * @param   array   $options    Hash of (option => value) pairs
	 * @return  $this
	 */
	public function options($options)
	{
		$result = array();

		foreach ($options as $option => $value)
		{
			$result[] = new Database_Expression("$option ?", array($value));
		}

		$this->parameters[':options'] = $result;

		return $this;
	}
}
