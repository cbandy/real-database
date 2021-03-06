<?php

/**
 * CREATE INDEX statement for MySQL.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-index.html
 */
class Database_MySQL_DDL_Create_Index extends SQL_DDL_Create_Index
{
	/**
	 * @var string  Index type
	 */
	protected $_using;

	public function __toString()
	{
		$value = parent::__toString();

		if ($this->_using)
		{
			$value .= ' USING '.$this->_using;
		}

		return $value;
	}

	/**
	 * Set the kind of index to be created
	 *
	 * @param   string  $type   UNIQUE, FULLTEXT, SPATIAL, etc.
	 * @return  $this
	 */
	public function type($type)
	{
		$this->parameters[':type'] = new SQL_Expression(strtoupper($type));

		return $this;
	}

	/**
	 * Set the index type
	 *
	 * @param   string  $type   BTREE, HASH, RTREE, etc.
	 * @return  $this
	 */
	public function using($type)
	{
		$this->_using = strtoupper($type);

		return $this;
	}
}
