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
class Database_MySQL_Create_Table extends SQL_DDL_Create_Table
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
	 * Set the table from which to copy this table definition.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @return  $this
	 */
	public function like($table)
	{
		if ( ! $table instanceof SQL_Expression
			AND ! $table instanceof SQL_Identifier)
		{
			$table = new SQL_Table($table);
		}

		$this->parameters[':like'] = $table;

		return $this;
	}

	/**
	 * Append table options.
	 *
	 * @param   array   $options    Hash of (option => value) pairs or NULL to reset
	 * @return  $this
	 */
	public function options($options)
	{
		if ($options === NULL)
		{
			$this->parameters[':options'] = array();
		}
		else
		{
			foreach ($options as $option => $value)
			{
				$this->parameters[':options'][] = new SQL_Expression(
					$option.' ?',
					array($value)
				);
			}
		}

		return $this;
	}
}
