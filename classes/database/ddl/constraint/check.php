<?php

/**
 * @package     RealDatabase
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/ddl-constraints.html PostgreSQL
 * @link http://www.sqlite.org/syntaxdiagrams.html#table-constraint SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms188258.aspx Transact-SQL
 */
class Database_DDL_Constraint_Check extends Database_DDL_Constraint
{
	/**
	 * @uses Database_DDL_Constraint_Check::conditions()
	 *
	 * @param   Database_Conditions $conditions
	 */
	public function __construct($condtions)
	{
		parent::__construct('CHECK (:conditions)');

		$this->conditions($conditions);
	}

	public function __toString()
	{
		return parent::__toString().$this->_value;
	}

	/**
	 * Set the conditions of the constraint
	 *
	 * @param   Database_Conditions $conditions
	 * @return  $this
	 */
	public function conditions($conditions)
	{
		$this->parameters[':conditions'] = $conditions;

		return $this;
	}
}
