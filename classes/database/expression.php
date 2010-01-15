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
	// Raw expression string
	protected $_value;

	// Unquoted parameters
	protected $_parameters;

	public function __construct($value, $parameters = NULL)
	{
		$this->_value = $value;
		$this->_parameters = $parameters;
	}

	/**
	 * Bind a variable to a parameter.
	 * TODO explain names
	 *
	 * @param   integer|string
	 * @param   mixed
	 * @return  $this
	 */
	public function bind($param, & $var)
	{
		$this->_parameters[$param] =& $var;

		return $this;
	}

	/**
	 * Set the value of a parameter.
	 * TODO explain names
	 *
	 * @param   integer|string
	 * @param   mixed
	 * @return  $this
	 */
	public function param($param, $value)
	{
		$this->_parameters[$param] = $value;

		return $this;
	}

	/**
	 * Add multiple parameter values.
	 * TODO explain names
	 *
	 * @param   array
	 * @return  $this
	 */
	public function parameters(array $params)
	{
		$this->_parameters = $params + $this->_parameters;

		return $this;
	}

	/**
	 * Compile the expression by replacing parameters with their values.
	 *
	 * @uses Database::quote()
	 *
	 * @param   Database
	 * @return  string
	 */
	public function compile(Database $db)
	{
		if (empty($this->_parameters))
			return (string) $this->_value;

		$position = 0;

		return preg_replace('/(:\w++|\?)/e', '$db->quote($this->_parameters[ ("$1" === "?") ? $position++ : "$1" ])', $this->_value);
	}
}
