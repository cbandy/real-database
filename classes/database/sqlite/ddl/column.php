<?php

/**
 * Column definition for SQLite. Identity columns must be INTEGER PRIMARY KEY.
 *
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_createtable.html#rowid
 */
class Database_SQLite_DDL_Column extends Database_DDL_Column_Identity
{
	public function identity()
	{
		$this->not_null();

		$this->parameters[':type'] = new Database_Expression('INTEGER');

		if (isset($this->parameters[':constraints']))
		{
			foreach ($this->parameters[':constraints']->parameters as $constraint)
			{
				if ($constraint instanceof Database_DDL_Constraint_Primary)
				{
					// Already has a PRIMARY KEY constraint
					return $this;
				}
			}
		}

		// Add a PRIMARY KEY constraint
		return $this->constraint(new Database_DDL_Constraint_Primary);
	}
}
