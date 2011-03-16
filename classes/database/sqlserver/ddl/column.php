<?php

/**
 * Column definition for SQL Server. Identity columns are IDENTITY PRIMARY KEY.
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/en-us/library/ms174979.aspx
 */
class Database_SQLServer_DDL_Column extends Database_DDL_Column
{
	public function __toString()
	{
		$value = ':name :type';

		if ($this->_not_null)
		{
			$value .= ' NOT NULL';
		}

		if ( ! empty($this->parameters[':identity']))
		{
			$value .= ' IDENTITY (:identity)';
		}
		elseif (array_key_exists(':default', $this->parameters))
		{
			$value .= ' DEFAULT :default';
		}

		if ( ! empty($this->parameters[':constraints']))
		{
			$value .= ' :constraints';
		}

		return $value;
	}

	/**
	 * Add a PRIMARY KEY constraint and generate a unique incremental value for
	 * each inserted row. Requires an integer type or a numeric type with a
	 * scale of zero.
	 *
	 * @param   integer $seed       The value for the first row inserted
	 * @param   integer $increment  The incremental value added to the previously inserted row
	 * @return  $this
	 */
	public function identity($seed = 1, $increment = 1)
	{
		$this->parameters[':identity'] = array($seed, $increment);

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
