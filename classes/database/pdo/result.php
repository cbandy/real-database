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
	 * @param   string|boolean  $as_object  Row object class, TRUE for stdClass or FALSE for associative array
	 */
	public function __construct($statement, $as_object)
	{
		if ( ! $as_object)
		{
			$statement->setFetchMode(PDO::FETCH_ASSOC);
		}
		else
		{
			$statement->setFetchMode(
				PDO::FETCH_CLASS,
				($as_object === TRUE) ? 'stdClass' : $as_object
			);
		}

		parent::__construct($statement->fetchAll(), $as_object);
	}
}
