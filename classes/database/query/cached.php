<?php

/**
 * @package     RealDatabase
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Query_Cached
{
	/**
	 * @var Database_Query_Cache    Cache in which to store/retrieve results
	 */
	protected $_cache;

	/**
	 * @var Database_iQuery Query to cache when executed
	 */
	protected $_query;

	/**
	 * @param   Database_Query_Cache    $cache  Cache in which to store/retrieve results
	 * @param   Database_iQuery         $query  Query to execute
	 */
	public function __construct($cache, $query)
	{
		$this->_cache = $cache;
		$this->_query = $query;
	}

	/**
	 * Delete this query from the cache.
	 *
	 * @return  void
	 */
	public function delete()
	{
		$this->_cache->delete($this->_query);
	}

	/**
	 * Get this query's result set from the cache. If not available, execute the
	 * query and store its result set in the cache. Returns NULL when the
	 * statement is not a query (e.g., a DELETE statement).
	 *
	 * When $lifetime is less than zero, the executed result set is not stored
	 * in the cache.
	 *
	 * @throws  Database_Exception
	 * @param   integer $lifetime   Cache lifetime in seconds or NULL to use the Cache default
	 * @return  Database_Result Result set
	 */
	public function execute($lifetime = NULL)
	{
		return $this->_cache->execute($this->_query, $lifetime);
	}

	/**
	 * Get this query's result set from the cache.
	 *
	 * @return  Database_Result_Array   Result set or NULL if not in the cache
	 */
	public function get()
	{
		return $this->_cache->get($this->_query);
	}

	/**
	 * Generate a cache key for this query.
	 *
	 * @return  string
	 */
	public function key()
	{
		return $this->_cache->key($this->_query);
	}

	/**
	 * Execute this query and, when $lifetime is not less than zero, store its
	 * result set in the cache. Returns NULL when the statement is not a query
	 * (e.g., a DELETE statement).
	 *
	 * @throws  Database_Exception
	 * @param   integer $lifetime   Cache lifetime in seconds or NULL to use the Cache default
	 * @return  Database_Result Result set
	 */
	public function set($lifetime = NULL)
	{
		return $this->_cache->set($this->_query, $lifetime);
	}
}
