<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/ddl-constraints.html PostgreSQL
 * @link http://www.sqlite.org/syntaxdiagrams.html#table-constraint SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms189862.aspx Transact-SQL
 */
abstract class Database_DDL_Constraint extends Database_Expression
{
	public function __toString()
	{
		if ( ! empty($this->parameters[':name']))
			return 'CONSTRAINT :name ';

		return '';
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
