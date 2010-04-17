<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Query extends Database_Command
{
	protected $_as_object = FALSE;

	/**
	 * Return results as associative arrays when executed
	 *
	 * @return  $this
	 */
	public function as_assoc()
	{
		$this->_as_object = FALSE;

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

		return $this;
	}

	/**
	 * Execute the query against a Database
	 *
	 * @param   Database    $db Connection on which to execute
	 * @return  Database_Result Result set
	 */
	public function execute($db)
	{
		if ($db instanceof Database_Escape)
			return $db->execute_query($this->compile($db), $this->_as_object);

		return $this->prepare($db)->as_object($this->_as_object)->execute();
	}

	/**
	 * Prepare the query to be executed against a Database
	 *
	 * @param   Database    $db Connection on which to prepare (and later execute)
	 * @return  Database_Prepared_Query
	 */
	public function prepare($db)
	{
		return $db->prepare_query($this->_value, $this->parameters);
	}
}
