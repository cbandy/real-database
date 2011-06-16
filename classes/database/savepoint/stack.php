<?php

/**
 * Stack for maintaining savepoint names.
 *
 * Pushing an existing name onto the stack will first remove the duplicate.
 * The position of the name at the bottom of the stack is one.
 *
 * @package     RealDatabase
 * @category    TODO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Savepoint_Stack implements Countable
{
	/**
	 * @var array   Stack of savepoint names
	 */
	protected $_names = array();

	/**
	 * @var array   Hash of savepoint positions in the stack
	 */
	protected $_positions = array();

	/**
	 * @return  integer Number of savepoints on the stack
	 */
	public function count()
	{
		return count($this->_names);
	}

	/**
	 * Remove a name from the stack.
	 *
	 * @return  string  Savepoint name
	 */
	public function pop()
	{
		$name = array_pop($this->_names);

		unset($this->_positions[$name]);

		return $name;
	}

	/**
	 * Remove from the stack all the names added after a name.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack
	 */
	public function pop_until($name)
	{
		if ($result = $this->position($name))
		{
			for ($i = $result, $max = $this->count(); $i < $max; ++$i)
			{
				$this->pop();
			}
		}

		return $result;
	}

	/**
	 * Return the position of a name in the stack.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack or NULL if not found
	 */
	public function position($name)
	{
		if (isset($this->_positions[$name]))
			return $this->_positions[$name];

		return NULL;
	}

	/**
	 * Add a name to the stack, removing any duplicates first.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack
	 */
	public function push($name)
	{
		if (isset($this->_positions[$name]))
		{
			// Remove duplicated name and update affected positions

			$i = 1;

			foreach ($this->_names as $k => $v)
			{
				if ($i > $this->_positions[$name])
				{
					// Move names on top of the duplicated name down one
					--$this->_positions[$v];
				}
				elseif ($i === $this->_positions[$name])
				{
					// Remove the duplicated name
					unset($this->_names[$k]);
				}

				++$i;
			}
		}

		// Push the name onto the stack
		return $this->_positions[$name] = array_push(
			$this->_names, $name
		);
	}

	/**
	 * Reset the stack.
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->_names = array();
		$this->_positions = array();
	}
}
