<?php

/**
 * Stack for maintaining savepoint names and their committed status.
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    TODO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLServer_Savepoints extends Database_Savepoint_Deep
{
	/**
	 * @var array   Hash of uncommitted savepoint positions in the savepoint stack
	 */
	protected $_positions_uncommitted = array();

	/**
	 * @var array   Stack of uncommitted savepoint names
	 */
	protected $_uncommitted_names = array();

	/**
	 * @var array   Hash of savepoint positions in the uncommitted savepoint stack
	 */
	protected $_uncommitted_positions = array();

	/**
	 * Remove a name from the uncommitted stack.
	 *
	 * @return  string  Savepoint name
	 */
	public function commit()
	{
		$name = array_pop($this->_uncommitted_names);

		if (count($this->_uncommitted_positions[$name]) > 1)
		{
			array_pop($this->_positions_uncommitted[$name]);
			array_pop($this->_uncommitted_positions[$name]);
		}
		else
		{
			unset($this->_positions_uncommitted[$name]);
			unset($this->_uncommitted_positions[$name]);
		}

		return $name;
	}

	/**
	 * Remove a name and all the following names from the uncommitted stack.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack
	 */
	public function commit_to($name)
	{
		if ( ! isset($this->_uncommitted_positions[$name]))
			return NULL;

		$result = end($this->_positions_uncommitted[$name]);
		$i = end($this->_uncommitted_positions[$name]);

		for ($max = count($this->_uncommitted_names); $i <= $max; ++$i)
		{
			$this->commit();
		}

		return $result;
	}

	public function pop()
	{
		$name = array_pop($this->_names);

		if ($this->position($name) === $this->uncommitted_position($name))
		{
			$this->commit();
		}

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
	 * Return the most recent position of an uncommitted name in the stack.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack or NULL if not found
	 */
	public function position_uncommitted($name)
	{
		if (isset($this->_positions_uncommitted[$name]))
			return end($this->_positions_uncommitted[$name]);

		return NULL;
	}

	/**
	 * Add a name to the stack and the uncommitted stack.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack
	 */
	public function push($name)
	{
		$this->_uncommitted_positions[$name][] = array_push(
			$this->_uncommitted_names, $name
		);

		return $this->_positions[$name][]
			= $this->_positions_uncommitted[$name][]
			= array_push($this->_names, $name);
	}

	public function reset()
	{
		parent::reset();

		$this->_positions_uncommitted = array();
		$this->_uncommitted_names = array();
		$this->_uncommitted_positions = array();
	}

	/**
	 * @return  integer Number of savepoints in the uncommitted stack
	 */
	public function uncommitted_count()
	{
		return count($this->_uncommitted_names);
	}

	/**
	 * Return the most recent position of a name in the uncommitted stack.
	 *
	 * @param   string  $name   Savepoint name
	 * @return  integer Position on the stack or NULL if not found
	 */
	public function uncommitted_position($name)
	{
		if (isset($this->_uncommitted_positions[$name]))
			return end($this->_uncommitted_positions[$name]);

		return NULL;
	}
}
