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
	 * Delete this query from the cache.
	 *
	 * @return  void
	 */
	public function delete()
	{
		$this->_cache->delete($this->key());
	}

	/**
	 * Execute this query or retrieve its result set from the cache. Returns
	 * NULL when the statement is not a query (e.g., a DELETE statement).
	 *
	 * @throws  Database_Exception
	 * @param   integer $lifetime   Cache lifetime in seconds or NULL to use the Cache default
	 * @return  Database_Result Result set
	 */
	public function execute($lifetime = NULL)
	{
		if ($lifetime < 0)
			return $this->_db->execute($this->_query);

		$key = $this->key();

		if ($result = $this->_cache->get($key))
			return new Database_Result_Array($result, $this->_query->as_object);

		$result = $this->_db->execute($this->_query);

		$this->_cache->set($key, $result->as_array(), $lifetime);

		return $result;
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
}
