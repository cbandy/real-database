<?php

/**
 * @package     RealDatabase
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Query_Cached
{
	/**
	 * @var mixed   Type as which to return results
	 */
	protected $_as_object = FALSE;

	/**
	 * @var Database    Database on which to execute
	 */
	protected $_db;

	/**
	 * @var integer Cache lifetime
	 */
	protected $_lifetime;

	/**
	 * @var Database_Query  Query to cache when executed
	 */
	protected $_query;

	/**
	 * Resets the Query to return array results
	 *
	 * @param   integer         $lifetime   Cache lifetime
	 * @param   Database        $db         Database on which to execute
	 * @param   Database_Query  $query      Query to execute
	 */
	public function __construct($lifetime, $db, $query)
	{
		$this->_db = $db;
		$this->_lifetime = $lifetime;
		$this->_query = $query;

		$this->_query->as_object($this->_as_object);
	}

	/**
	 * Return results as associative arrays when executed
	 *
	 * @return  $this
	 */
	public function as_assoc()
	{
		$this->_as_object = FALSE;

		$this->_query->as_assoc();

		return $this;
	}

	/**
	 * Return results as objects when executed
	 *
	 * @param   mixed   $class  Class to return or TRUE for stdClass
	 * @return  $this
	 */
	public function as_object($class = TRUE)
	{
		$this->_as_object = $class;

		$this->_query->as_object($this->_as_object);

		return $this;
	}

	/**
	 * Delete this query from the cache
	 *
	 * @return  void
	 */
	public function delete()
	{
		Kohana::cache($this->key(), NULL, -1);
	}

	/**
	 * Execute this query or retrieve its result set from the cache. Returns
	 * NULL when the statement is not a query (e.g., a DELETE statement).
	 *
	 * @throws  Database_Exception
	 * @return  Database_Result Result set
	 */
	public function execute()
	{
		if ($this->_lifetime < 0)
			return $this->_query->execute($this->_db);

		$key = $this->key();

		if ($result = Kohana::cache($key, NULL, $this->_lifetime))
			return new Database_Result_Array($result, $this->_as_object);

		$result = $this->_query->execute($this->_db);

		Kohana::cache($key, $result->as_array(), $this->_lifetime);

		return $result;
	}

	/**
	 * Generate a cache key for this query
	 *
	 * @return  string
	 */
	public function key()
	{
		return 'Database_Query_Cached('.$this->_db.','.$this->_query.','.serialize($this->_query->parameters).','.$this->_as_object.')';
	}
}
