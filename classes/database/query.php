<?php

/**
 * A statement that returns a result set when executed.
 *
 * @package     RealDatabase
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Query extends Database_Command
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
	 * Execute the query against a Database
	 *
	 * @param   Database    $db Connection on which to execute
	 * @return  Database_Result Result set
	 */
	public function execute($db)
	{
		if ($db instanceof Database_iEscape)
			return $db->execute_query($db->quote($this), $this->as_object);

		return $this->prepare($db)->as_object($this->as_object)->execute();
	}

	/**
	 * Prepare the query to be executed against a Database
	 *
	 * @param   Database    $db Connection on which to prepare (and later execute)
	 * @return  Database_Prepared_Query
	 */
	public function prepare($db)
	{
		return $db->prepare_query($this->__toString(), $this->parameters);
	}
}
