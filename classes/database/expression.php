<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Expression implements Countable
{
	// Raw expression string
	protected $_value;

	// Unquoted parameters
	protected $_parameters;

	/**
	 * @param   string
	 * @param   array
	 */
	public function __construct($value, array $parameters = NULL)
	{
		$this->_value = $value;
		$this->_parameters = $parameters;
	}

	/**
	 * Bind a variable to a parameter. Names must begin with colon.
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
	 * Set the value of a parameter. Names must begin with colon.
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
	 * Add multiple parameter values. Names must begin with colon.
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
		if ($this->count() === 0)
			return (string) $this->_value;

		$position = 0;

		return preg_replace('/(:\w++|\?)/e', '$db->quote($this->_parameters[ ("$1" === "?") ? $position++ : "$1" ])', $this->_value);
	}

	/**
	 * Countable::count()
	 */
	public function count()
	{
		return count($this->_parameters);
	}
}
