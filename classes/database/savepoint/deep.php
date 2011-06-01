<?php

/**
 * Stack for maintaining reusable savepoint names.
 *
 * The position of a duplicated name is that of the most recently added.
 *
 * @package     RealDatabase
 * @category    TODO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Savepoint_Deep extends Database_Savepoint_Stack
{
	public function pop()
	{
		$name = array_pop($this->_names);

		if (count($this->_positions[$name]) > 1)
		{
			array_pop($this->_positions[$name]);
		}
		else
		{
			unset($this->_positions[$name]);
		}

		return $name;
	}

	/**
	 * Return the most recent position of a name in the stack.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack or NULL if not found
	 */
	public function position($name)
	{
		if (isset($this->_positions[$name]))
			return end($this->_positions[$name]);

		return NULL;
	}

	/**
	 * Add a name to the stack.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack
	 */
	public function push($name)
	{
		return $this->_positions[$name][] = array_push(
			$this->_names, $name
		);
	}
}
