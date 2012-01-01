<?php

/**
 * @package     RealDatabase
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Query_Cache
{
	/**
	 * @var Cache   Cache in which to store/retrieve results
	 */
	protected $_cache;

	/**
	 * @var Database    Database on which to execute
	 */
	protected $_db;

	/**
	 * @param   Cache       $cache  Cache in which to store/retrieve results
	 * @param   Database    $db     Database on which to execute
	 */
	public function __construct($cache, $db)
	{
		$this->_cache = $cache;
		$this->_db = $db;
	}

	/**
	 * Execute a query and store its result set in the cache.
	 *
	 * If the query does not return a result set or if $lifetime is less than
	 * zero, nothing is stored.
	 *
	 * @throws  Database_Exception
	 * @param   string          $key        Cache key
	 * @param   Database_iQuery $query      Query to execute
	 * @param   integer         $lifetime   Cache lifetime in seconds or NULL to use the Cache default
	 * @return  Database_Result Result set
	 */
	protected function _execute_set($key, $query, $lifetime)
	{
		$result = $this->_db->execute($query);

		if ($result AND $lifetime >= 0)
		{
			$this->_cache->set($key, $result->serializable(), $lifetime);
		}

		return $result;
	}

	/**
	 * Delete a query from the cache.
	 *
	 * @param   Database_iQuery $query  Query to delete
	 * @return  void
	 */
	public function delete($query)
	{
		$this->_cache->delete($this->key($query));
	}

	/**
	 * Get a query's result set from the cache. If not available, execute the
	 * query and store its result set in the cache. Returns NULL when the
	 * statement is not a query (e.g., a DELETE statement).
	 *
	 * When $lifetime is less than zero, the executed result set is not stored
	 * in the cache.
	 *
	 * @throws  Database_Exception
	 * @param   Database_iQuery $query      Query to execute
	 * @param   integer         $lifetime   Cache lifetime in seconds or NULL to use the Cache default
	 * @return  Database_Result Result set
	 */
	public function execute($query, $lifetime = NULL)
	{
		$key = $this->key($query);

		if ($result = $this->_cache->get($key))
			return $result;

		return $this->_execute_set($key, $query, $lifetime);
	}

	/**
	 * Get a query's result set from the cache.
	 *
	 * @param   Database_iQuery $query  Query to retrieve
	 * @return  Database_Result_Array   Result set or NULL if not in the cache
	 */
	public function get($query)
	{
		return $this->_cache->get($this->key($query));
	}

	/**
	 * Generate a cache key for a query.
	 *
	 * @param   Database_iQuery $query  Query to examine
	 *
	 * @return  string
	 */
	public function key($query)
	{
		return 'Database_Query_Cache('
			.$this->_db.','
			.$query.','
			.serialize($query->parameters).','
			.$query->as_object.','
			.serialize($query->arguments)
			.')';
	}

	/**
	 * Execute a query and, when $lifetime is not less than zero, store its
	 * result set in the cache. Returns NULL when the statement is not a query
	 * (e.g., a DELETE statement).
	 *
	 * @throws  Database_Exception
	 * @param   Database_iQuery $query      Query to execute
	 * @param   integer         $lifetime   Cache lifetime in seconds or NULL to use the Cache default
	 * @return  Database_Result Result set
	 */
	public function set($query, $lifetime = NULL)
	{
		return $this->_execute_set($this->key($query), $query, $lifetime);
	}
}
