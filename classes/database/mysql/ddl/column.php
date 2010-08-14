<?php

/**
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html
 * @link http://dev.mysql.com/doc/en/example-auto-increment.html
 */
class Database_MySQL_DDL_Column extends Database_DDL_Column_Identity
{
	/**
	 * @var boolean
	 */
	protected $_auto_increment;

	public function __toString()
	{
		$value = ':name :type';

		if ($this->_not_null)
		{
			$value .= ' NOT NULL';
		}

		if (array_key_exists(':default', $this->parameters))
		{
			$value .= ' DEFAULT :default';
		}

		if ($this->_auto_increment)
		{
			$value .= ' AUTO_INCREMENT';
		}

		if ( ! empty($this->parameters[':unique']))
		{
			$value .= ' :unique';
		}

		if ( ! empty($this->parameters[':comment']))
		{
			$value .= ' COMMENT :comment';
		}

		if ( ! empty($this->parameters[':foreign']))
		{
			$value .= ' :foreign';
		}

		return $value;
	}

	/**
	 * Set whether or not the default value of the column should be automatically generated.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function auto_increment($value = TRUE)
	{
		$this->_auto_increment = $value;

		return $this;
	}

	/**
	 * Set the comment for the column.
	 *
	 * @param   string  $value
	 * @return  $this
	 */
	public function comment($value)
	{
		$this->parameters[':comment'] = $value;

		return $this;
	}

	/**
	 * Append a constraint to the column.
	 *
	 * @param   Database_DDL_Constraint $constraint
	 * @return  $this
	 */
	public function constraint($constraint)
	{
		if ($constraint === NULL)
		{
			$this->parameters[':foreign'] = $this->parameters[':unique'] = NULL;
		}
		elseif ($constraint instanceof Database_DDL_Constraint_Foreign)
		{
			$this->parameters[':foreign'] = $constraint;
		}
		elseif ( ! $constraint instanceof Database_DDL_Constraint_Check)
		{
			$this->parameters[':unique'] = $constraint;
		}

		return $this;
	}

	public function identity()
	{
		$this->_auto_increment = TRUE;
		$this->parameters[':unique'] = new Database_DDL_Constraint_Primary;

		return $this;
	}
}
