<?php

/**
 * A natively parameterized SQL statement. Placeholders are driver-specific and
 * all parameters are unquoted literals.
 *
 * This is not a prepared statement.
 *
 * @package     RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Statement
{
	/**
	 * @var array   Unquoted literal parameters
	 */
	protected $_parameters;

	/**
	 * @var string  SQL statement
	 */
	protected $_statement;

	/**
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Unquoted literal parameters
	 */
	public function __construct($statement, $parameters = array())
	{
		$this->_statement = $statement;
		$this->_parameters = $parameters;
	}

	public function __toString()
	{
		return $this->_statement;
	}

	/**
	 * Bind a variable to a parameter. Parameter names are driver-specific.
	 *
	 * @param   integer|string  $param  Parameter index or name
	 * @param   mixed           $var    Variable to bind
	 * @return  $this
	 */
	public function bind($param, & $var)
	{
		$this->_parameters[$param] =& $var;

		return $this;
	}

	/**
	 * Set the value of a parameter. Parameter names are driver-specific.
	 *
	 * @param   integer|string  $param  Parameter index or name
	 * @param   mixed           $value  Value to assign
	 * @return  $this
	 */
	public function param($param, $value)
	{
		$this->_parameters[$param] = $value;

		return $this;
	}

	/**
	 * Set multiple parameter values or return the current parameter values.
	 * Parameter names are driver-specific.
	 *
	 * @param   array   $params Values to assign or NULL to return the current values
	 * @return  $this|array
	 */
	public function parameters($params = NULL)
	{
		if ($params === NULL)
			return $this->_parameters;

		$this->_parameters = $params + $this->_parameters;

		return $this;
	}
}
