<?php

/**
 * @package     RealDatabase
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Prepared_Query extends Database_Prepared_Command
{
	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	/**
	 * Return rows as associative arrays when executed.
	 *
	 * @return  $this
	 */
	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	/**
	 * Set the class as which to return rows when executed.
	 *
	 * @param   string|boolean  $class  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @return  $this
	 */
	public function as_object($class = TRUE)
	{
		$this->as_object = $class;

		return $this;
	}

	/**
	 * Execute the query, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement)
	 *
	 * @throws  Database_Exception
	 * @return  Database_Result Result set
	 */
	public function execute()
	{
		return $this->_db->execute_query($this->_db->quote($this), $this->as_object);
	}
}
