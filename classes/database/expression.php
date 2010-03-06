<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Expression
{
	/**
	 * @var string  Raw expression
	 */
	protected $_value;

	/**
	 * @var array   Unquoted parameters
	 */
	protected $_parameters;

	/**
	 * @param   string  $value      Raw expression
	 * @param   array   $parameters Unquoted parameters
	 */
	public function __construct($value, array $parameters = array())
	{
		$this->_value = $value;
		$this->_parameters = $parameters;
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
		$this->_parameters[$param] =& $var;

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
		$this->_parameters[$param] = $value;

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
		$this->_parameters = $params + $this->_parameters;

		return $this;
	}

	/**
	 * Compile the expression by replacing parameters with their quoted values
	 *
	 * @uses Database::quote()
	 *
	 * @param   Database    $db Connection with which to quote the values
	 * @return  string  Quoted SQL expression
	 */
	public function compile(Database $db)
	{
		if (empty($this->_parameters))
			return (string) $this->_value;

		$position = 0;

		return preg_replace('/(:\w++|\?)/e', '$db->quote($this->_parameters[ ("$1" === "?") ? $position++ : "$1" ])', $this->_value);
	}
}
