<?php

/**
 * @package     RealDatabase
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Expression
{
	/**
	 * @var mixed   SQL expression with or without parameter placeholders
	 */
	protected $_value;

	/**
	 * @var array   Unquoted parameters
	 */
	public $parameters;

	/**
	 * @param   mixed   $value      SQL expression
	 * @param   array   $parameters Unquoted parameters
	 */
	public function __construct($value, array $parameters = array())
	{
		$this->_value = $value;
		$this->parameters = $parameters;
	}

	public function __toString()
	{
		return (string) $this->_value;
	}

	/**
	 * Bind a variable to a parameter. Names must begin with colon.
	 *
	 * @param   int|string  $param  Parameter index or name
	 * @param   mixed       $var    Variable to bind
	 * @return  $this
	 */
	public function bind($param, & $var)
	{
		$this->parameters[$param] =& $var;

		return $this;
	}

	/**
	 * Set the value of a parameter. Names must begin with colon.
	 *
	 * @param   int|string  $param  Parameter index or name
	 * @param   mixed       $value  Value to assign
	 * @return  $this
	 */
	public function param($param, $value)
	{
		$this->parameters[$param] = $value;

		return $this;
	}

	/**
	 * Add multiple parameter values. Names must begin with colon.
	 *
	 * @param   array   $params Values to assign
	 * @return  $this
	 */
	public function parameters(array $params)
	{
		$this->parameters = $params + $this->parameters;

		return $this;
	}
}
