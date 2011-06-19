<?php

/**
 * ENUM expression for MySQL.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/enum.html
 */
class Database_MySQL_DDL_Enum extends SQL_Expression
{
	/**
	 * @uses Database_MySQL_DDL_Enum::values()
	 *
	 * @param   array   $values
	 */
	public function __construct($values = NULL)
	{
		parent::__construct('ENUM (:values)');

		$this->values($values);
	}

	/**
	 * Append one value to the permitted values.
	 *
	 * @param   string|SQL_Expression   $value  Literal value or NULL to reset
	 * @return  $this
	 */
	public function value($value)
	{
		if ($value === NULL)
		{
			$this->parameters[':values'] = array();
		}
		else
		{
			$this->parameters[':values'][] = $value;
		}

		return $this;
	}

	/**
	 * Append multiple values to the permitted values.
	 *
	 * @param   array   $values List of literal values or NULL to reset
	 * @return  $this
	 */
	public function values($values)
	{
		if ($values === NULL)
		{
			$this->parameters[':values'] = array();
		}
		else
		{
			foreach ($values as $value)
			{
				$this->parameters[':values'][] = $value;
			}
		}

		return $this;
	}
}
