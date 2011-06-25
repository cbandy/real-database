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
	 * @var Cache   Cache in which to store/retrieve results
	 */
	protected $_cache;

	/**
	 * @var Database    Database on which to execute
	 */
	protected $_db;

	/**
	 * @var Database_iQuery Query to cache when executed
	 */
	protected $_query;

	/**
	 * @param   Cache           $cache  Cache in which to store/retrieve results
	 * @param   Database        $db     Database on which to execute
	 * @param   Database_iQuery $query  Query to execute
	 */
	public function __construct($cache, $db, $query)
	{
		$this->_cache = $cache;
		$this->_db = $db;
		$this->_query = $query;
	}

	/**
	 * Execute this query and store its result set in the cache.
	 *
	 * If the query does not return a result set or if $lifetime is less than
	 * zero, nothing is stored.
	 *
	 * @throws  Database_Exception
	 * @param   string  $key        Cache key
	 * @param   integer $lifetime   Cache lifetime in seconds or NULL to use the Cache default
	 * @return  Database_Result Result set
	 */
	protected function _execute_set($key, $lifetime)
	{
		$result = $this->_db->execute($this->_query);

		if ($result AND $lifetime >= 0)
		{
			$this->_cache->set($key, $result->serializable(), $lifetime);
		}

		return $result;
	}

	/**
	 * Delete this query from the cache.
	 *
	 * @return  void
	 */
	public function delete()
	{
		$this->_cache->delete($this->key());
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
		$key = $this->key();

		if ($result = $this->_cache->get($key))
			return $result;

		return $this->_execute_set($key, $lifetime);
	}

	/**
	 * Get this query's result set from the cache.
	 *
	 * @return  Database_Result_Array   Result set or NULL if not in the cache
	 */
	public function get()
	{
		return $this->_cache->get($this->key());
	}

	/**
	 * Generate a cache key for this query.
	 *
	 * @return  string
	 */
	public function key()
	{
		return 'Database_Query_Cached('
			.$this->_db.','
			.$this->_query.','
			.serialize($this->_query->parameters).','
			.$this->_query->as_object.','
			.serialize($this->_query->arguments)
			.')';
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
		return $this->_execute_set($this->key(), $lifetime);
	}
}
