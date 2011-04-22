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
	 * @var Database    Database on which to execute
	 */
	protected $_db;

	/**
	 * @var integer Cache lifetime
	 */
	protected $_lifetime;

	/**
	 * @var Database_iQuery Query to cache when executed
	 */
	protected $_query;

	/**
	 * @param   integer         $lifetime   Cache lifetime
	 * @param   Database        $db         Database on which to execute
	 * @param   Database_iQuery $query      Query to execute
	 */
	public function __construct($lifetime, $db, $query)
	{
		$this->_db = $db;
		$this->_lifetime = $lifetime;
		$this->_query = $query;
	}

	/**
	 * Delete this query from the cache
	 *
	 * @return  void
	 */
	public function delete()
	{
		// Any negative number should clear the cache, but allow for some time
		// discrepancy on a remote file system
		Kohana::cache($this->key(), NULL, -60);
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
			return $this->_db->execute($this->_query);

		$key = $this->key();

		if ($result = Kohana::cache($key, NULL, $this->_lifetime))
			return new Database_Result_Array($result, $this->_query->as_object);

		$result = $this->_db->execute($this->_query);

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
		return 'Database_Query_Cached('
			.$this->_db.','
			.$this->_query.','
			.serialize($this->_query->parameters).','
			.$this->_query->as_object.','
			.serialize($this->_query->arguments)
			.')';
	}
}
