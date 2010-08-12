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
class Database_DDL_Constraint_Check extends Database_Expression
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
		$value = '';

		if ( ! empty($this->parameters[':name']))
		{
			$value .= 'CONSTRAINT :name ';
		}

		$value .= $this->_value;

		return $value;
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

	/**
	 * Set the name of the constraint
	 *
	 * @param   mixed   $value  Converted to Database_Identifier
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof Database_Expression
			AND ! $value instanceof Database_Identifier)
		{
			$value = new Database_Identifier($value);
		}

		$this->parameters[':name'] = $value;

		return $this;
	}
}
