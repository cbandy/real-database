<?php

/**
 * ALTER TABLE statement for PostgreSQL. Allows the name and type of columns to be changed.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-altertable.html
 */
class Database_PostgreSQL_Alter_Table extends Database_Command_Alter_Table
{
	/**
	 * Remove a column from the table, optionally removing dependent objects.
	 *
	 * @param   mixed   $name       Converted to SQL_Column
	 * @param   boolean $cascade    Whether or not dependent objects should be dropped
	 * @return  $this
	 */
	public function drop_column($name, $cascade = NULL)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Column($name);
		}

		$result = new SQL_Expression('DROP COLUMN ?', array($name));

		if ($cascade !== NULL)
		{
			$result->_value .= $cascade ? ' CASCADE' : ' RESTRICT';
		}

		$this->parameters[':actions'][] = $result;

		return $this;
	}

	/**
	 * Remove a constraint from the table, optionally removing dependent objects.
	 *
	 * @param   string  $type       Unused
	 * @param   mixed   $name       Converted to SQL_Identifier
	 * @param   boolean $cascade    Whether or not dependent objects should be dropped
	 * @return  $this
	 */
	public function drop_constraint($type, $name, $cascade = NULL)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Identifier($name);
		}

		$result = new SQL_Expression('DROP CONSTRAINT ?', array($name));

		if ($cascade !== NULL)
		{
			$result->_value .= $cascade ? ' CASCADE' : ' RESTRICT';
		}

		$this->parameters[':actions'][] = $result;

		return $this;
	}

	/**
	 * Rename a column. This cannot be combined with other actions.
	 *
	 * @param   mixed   $old_name   Converted to SQL_Column
	 * @param   mixed   $new_name   Converted to SQL_Column
	 * @return  $this
	 */
	public function rename_column($old_name, $new_name)
	{
		if ( ! $old_name instanceof SQL_Expression
			AND ! $old_name instanceof SQL_Identifier)
		{
			$old_name = new SQL_Column($old_name);
		}

		if ( ! $new_name instanceof SQL_Expression
			AND ! $new_name instanceof SQL_Identifier)
		{
			$new_name = new SQL_Column($new_name);
		}

		$this->parameters[':actions'] = new SQL_Expression('RENAME ? TO ?', array($old_name, $new_name));

		return $this;
	}

	/**
	 * Add or remove the NOT NULL constraint on a column.
	 *
	 * @param   mixed   $name   Converted to SQL_Column
	 * @param   boolean $value  TRUE to add or FALSE to remove
	 * @return  $this
	 */
	public function set_not_null($name, $value = TRUE)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->parameters[':actions'][] = new SQL_Expression(($value ? 'SET' : 'DROP').' NOT NULL ?', array($name));

		return $this;
	}

	/**
	 * Change the type of a column, optionally using an expression to facilitate the conversion.
	 *
	 * @param   mixed   $column Converted to SQL_Column
	 * @param   mixed   $type   Converted to SQL_Expression
	 * @param   mixed   $using  Converted to SQL_Expression
	 * @return  $this
	 */
	public function type($name, $type, $using = NULL)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Column($name);
		}

		if ( ! $type instanceof SQL_Expression)
		{
			$type = new SQL_Expression($type);
		}

		$result = new SQL_Expression('ALTER ? TYPE ?', array($name, $type));

		if ($using !== NULL)
		{
			if ( ! $using instanceof SQL_Expression)
			{
				$using = new SQL_Expression($using);
			}

			$result->_value .= ' USING ?';
			$result->parameters[] = $using;
		}

		$this->parameters[':actions'][] = $result;

		return $this;
	}
}
