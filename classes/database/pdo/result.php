<?php

/**
 * Result set for a PDOStatement.
 *
 * Prefetches all data since scrollable cursors do not work for most drivers and even
 * PDOStatement->rowCount() should not be relied upon.
 *
 * @package     RealDatabase
 * @subpackage  PDO
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://bugs.php.net/44475  No MySQL cursor
 * @link http://bugs.php.net/44861  No PostgreSQL cursor
 * @link http://php.net/manual/pdostatement.rowcount
 */
class Database_PDO_Result extends Database_Result_Array
{
	/**
	 * @param   PDOStatement    $statement  Executed statement
	 * @param   string|boolean  $as_object  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @param   array           $arguments  Arguments to pass to the class constructor
	 */
	public function __construct($statement, $as_object, $arguments)
	{
		if ( ! $as_object)
		{
			$statement->setFetchMode(PDO::FETCH_ASSOC);
		}
		else
		{
			// The objects returned by PDO::FETCH_OBJ differ between drivers
			$statement->setFetchMode(
				PDO::FETCH_CLASS,
				($as_object === TRUE) ? 'stdClass' : $as_object,
				$arguments
			);
		}

		parent::__construct($statement->fetchAll(), $as_object);
	}
}
