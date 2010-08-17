<?php

/**
 * @package     RealDatabase
 * @subpackage  PDO
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_Result_Iterator extends Database_Result_Iterator
{
	/**
	 * @var mixed   Result object class
	 */
	protected $_as_object;

	/**
	 * @var PDOStatement    Executed statement
	 */
	protected $_statement;

	/**
	 * @param   PDOStatement    $statement  Executed statement
	 * @param   mixed           $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 */
	public function __construct($statement, $as_object)
	{
		$this->_as_object = $as_object;
		$this->_statement = $statement;
	}

	public function current()
	{
		if ($this->_statement->columnCount() === 0)
			return $this->_statement->rowCount();

		return new Database_PDO_Result($this->_statement, $this->_as_object);
	}

	public function next()
	{
		if ( ! $this->_statement->nextRowset())
		{
			$this->_statement = NULL;
		}

		return parent::next();
	}

	public function valid()
	{
		return ($this->_statement !== NULL);
	}
}
