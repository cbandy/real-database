<?php

/**
 * @package PDO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * Prefetches all data since scrollable cursors do not work for most drivers
 * and even PDOStatement->rowCount() should not be relied upon.
 *
 * @link http://bugs.php.net/44475  No MySQL cursor
 * @link http://bugs.php.net/44861  No PostgreSQL cursor
 * @link http://php.net/manual/pdostatement.rowcount
 */
class Database_PDO_Result extends Database_Result_Array
{
	/**
	 * @param   PDOStatement    $statement
	 * @param   mixed           $as_object
	 */
	public function __construct($statement, $as_object)
	{
		if ( ! $as_object)
		{
			$statement->setFetchMode(PDO::FETCH_ASSOC);
		}
		elseif (is_string($as_object))
		{
			$statement->setFetchMode(PDO::FETCH_CLASS, $as_object);
		}
		else
		{
			$statement->setFetchMode(PDO::FETCH_OBJ);
		}

		parent::__construct($statement->fetchAll(), $as_object);
	}
}
