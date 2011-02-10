<?php

/**
 * Read-only, seekable result set iterator. Individual rows can be accessed using brackets, while
 * [Database_Result::as_array] retrieves one or more columns from multiple rows at once. A single
 * column can be retrieved with [Database_Result::get].
 *
 *     $result = $query->execute($db);
 *
 *     // iteration
 *     foreach ($result as $row)
 *     {
 *         $library->do_something($row['id'], $row['name']);
 *     }
 *
 *     // 5th row
 *     $row = $result[4];
 *
 * @package     RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database_Result implements ArrayAccess, Countable, Iterator, SeekableIterator
{
	protected $_as_object;
	protected $_count = 0;
	protected $_position = 0;

	/**
	 * @param   string|boolean  $as_object  Row object class, TRUE for stdClass or FALSE for associative array
	 */
	public function __construct($as_object)
	{
		$this->_as_object = ($as_object === TRUE) ? 'stdClass' : $as_object;
	}

	/**
	 * Return all of the rows as an array without moving the pointer.
	 *
	 *     // indexed array of rows
	 *     $rows = $result->as_array();
	 *
	 *     // indexed array of "name" values
	 *     $names = $result->as_array(NULL, 'name');
	 *
	 *     // associative array of rows by "id"
	 *     $rows = $result->as_array('id');
	 *
	 *     // associative array of "name" values by "id"
	 *     $names = $result->as_array('id', 'name');
	 *
	 * @param   string  $key    Column for associative keys
	 * @param   string  $value  Column for values
	 * @return  array
	 */
	public function as_array($key = NULL, $value = NULL)
	{
		$position = $this->_position;
		$results = array();

		if ($key === NULL AND $value === NULL)
		{
			// Indexed rows
			foreach ($this as $row)
			{
				$results[] = $row;
			}
		}
		elseif ($key === NULL)
		{
			// Indexed columns
			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[] = $row->$value;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[] = $row[$value];
				}
			}
		}
		elseif ($value === NULL)
		{
			// Associative rows
			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[$row->$key] = $row;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[$row[$key]] = $row;
				}
			}
		}
		else
		{
			// Associative columns
			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[$row->$key] = $row->$value;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[$row[$key]] = $row[$value];
				}
			}
		}

		$this->_position = $position;
		return $results;
	}

	/**
	 * The number of rows in the result set
	 *
	 * @link http://php.net/manual/countable.count Countable::count()
	 *
	 * @return  integer
	 */
	public function count()
	{
		return $this->_count;
	}

	/**
	 * Return the current row without validating the current position
	 *
	 * @link http://php.net/manual/iterator.current Iterator::current()
	 *
	 * @return  mixed
	 */
	//abstract public function current();

	/**
	 * Return a column from the current row.
	 *
	 *     // single column
	 *     $name = $result->get('name');
	 *
	 *     // first column
	 *     $count = $db->execute_query('SELECT COUNT(*) FROM '.$db->quote_table('things'))->get();
	 *
	 * @param   string  $name       Column name or NULL to return the first
	 * @param   mixed   $default    Default value if the column is NULL
	 * @return  mixed
	 */
	public function get($name = NULL, $default = NULL)
	{
		if ($this->valid())
		{
			$row = $this->current();

			if ($name === NULL)
			{
				if (($result = reset($row)) !== NULL)
					return $result;
			}
			elseif ($this->_as_object)
			{
				if (isset($row->$name))
					return $row->$name;
			}
			else
			{
				if (isset($row[$name]))
					return $row[$name];
			}
		}

		return $default;
	}

	/**
	 * The offset of the row that will be returned by the next call to current()
	 *
	 * @link http://php.net/manual/iterator.key Iterator::key()
	 *
	 * @return integer
	 */
	public function key()
	{
		return $this->_position;
	}

	/**
	 * Move the current position to the next row
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
	 * Whether or not an offset exists
	 *
	 * @link http://php.net/manual/arrayaccess.offsetexists ArrayAccess::offsetExists()
	 *
	 * @param   integer $offset
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return ($offset >= 0 AND $offset < $this->_count);
	}

	/**
	 * Return the row at the specified offset
	 *
	 * @link http://php.net/manual/arrayaccess.offsetget ArrayAccess::offsetGet()
	 *
	 * @throws  OutOfBoundsException
	 * @param   integer $offset
	 * @return  mixed
	 */
	public function offsetGet($offset)
	{
		return $this->seek($offset)->current();
	}

	/**
	 * No-op because this class is read-only
	 *
	 * @link http://php.net/manual/arrayaccess.offsetset ArrayAccess::offsetSet()
	 *
	 * @throws Kohana_Exception
	 */
	final public function offsetSet($offset, $value)
	{
		throw new Kohana_Exception('Database results are read-only');
	}

	/**
	 * No-op because this class is read-only
	 *
	 * @link http://php.net/manual/arrayaccess.offsetunset ArrayAccess::offsetUnset()
	 *
	 * @throws Kohana_Exception
	 */
	final public function offsetUnset($offset)
	{
		throw new Kohana_Exception('Database results are read-only');
	}

	/**
	 * Move the current position to the previous row
	 *
	 * @return  $this
	 */
	public function prev()
	{
		--$this->_position;
		return $this;
	}

	/**
	 * Move the current position to the first row
	 *
	 * @link http://php.net/manual/iterator.rewind Iterator::rewind()
	 *
	 * @return  $this
	 */
	public function rewind()
	{
		$this->_position = 0;
		return $this;
	}

	/**
	 * Set the current position
	 *
	 * @link http://php.net/manual/seekableiterator.seek SeekableIterator::seek()
	 *
	 * @throws  OutOfBoundsException
	 * @param   integer $position
	 * @return  $this
	 */
	public function seek($position)
	{
		if ( ! $this->offsetExists($position))
			throw new OutOfBoundsException;

		$this->_position = $position;
		return $this;
	}

	/**
	 * Whether or not the next call to current() will succeed
	 *
	 * @link http://php.net/manual/iterator.valid Iterator::valid()
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return $this->offsetExists($this->_position);
	}
}
