<?php

/**
 * @package     RealDatabase
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database_Result_Iterator implements Iterator
{
	/**
	 * @var integer
	 */
	protected $_position = 0;

	///**
	// * Return the current result set or number of rows affected
	// *
	// * @link http://php.net/manual/iterator.current Iterator::current()
	// *
	// * @return  integer|Database_Result
	// */
	//abstract public function current();

	/**
	 * Return the current position
	 *
	 * @link http://php.net/manual/iterator.key Iterator::key()
	 *
	 * @return  integer
	 */
	public function key()
	{
		return $this->_position;
	}

	/**
	 * Advance to the next result
	 *
	 * @link http://php.net/manual/iterator.next Iterator::next()
	 *
	 * @return  $this
	 */
	public function next()
	{
		++$this->_position;
		return $this;
	}

	/**
	 * No-op because this iterator is forward-only
	 *
	 * @link http://php.net/manual/iterator.rewind Iterator::rewind()
	 *
	 * @return  $this
	 */
	public function rewind()
	{
		return $this;
	}

	///**
	// * Whether or not the next call to current() will succeed
	// *
	// * @link http://php.net/manual/iterator.valid Iterator::valid()
	// *
	// * @return  boolean
	// */
	//abstract public function valid();
}
