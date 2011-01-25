<?php

/**
 * Column definition for PostgreSQL. Identity columns are SERIAL PRIMARY KEY.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-createtable.html
 * @link http://www.postgresql.org/docs/current/static/datatype-numeric.html#DATATYPE-SERIAL
 */
class Database_PostgreSQL_DDL_Column extends Database_DDL_Column_Identity
{
	public function identity()
	{
		if (isset($this->parameters[':type']) AND in_array(strtolower($this->parameters[':type']->_value), array('bigint', 'bigserial', 'int8'), TRUE))
		{
			$this->parameters[':type'] = new SQL_Expression('BIGSERIAL');
		}
		else
		{
			$this->parameters[':type'] = new SQL_Expression('SERIAL');
		}

		if (isset($this->parameters[':constraints']))
		{
			foreach ($this->parameters[':constraints']->parameters as $constraint)
			{
				if ($constraint instanceof SQL_DDL_Constraint_Primary)
				{
					// Already has a PRIMARY KEY constraint
					return $this;
				}
			}
		}

		// Add a PRIMARY KEY constraint
		return $this->constraint(new SQL_DDL_Constraint_Primary);
	}
}
