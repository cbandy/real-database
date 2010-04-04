<?php

/**
 * @package PDO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_Result extends Database_Result
{
	/**
	 * @var array   Fetched rows
	 */
	protected $_data;

	/**
	 * @param   PDOStatement    $statement
	 * @param   mixed           $as_object
	 */
	public function __construct($statement, $as_object)
	{
		parent::__construct($statement, $as_object);

		if ($as_object === FALSE)
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

		$this->_data = $statement->fetchAll();

		// PDOStatement->rowCount() should not be relied upon
		// @link http://php.net/manual/pdostatement.rowcount
		$this->_count = count($this->_data);
	}

	public function as_array($key = NULL, $value = NULL)
	{
		if ($key === NULL AND $value === NULL)
			return $this->_data;

		return parent::as_array($key, $value);
	}

	public function current()
	{
		return $this->_data[$this->_position];
	}
}
