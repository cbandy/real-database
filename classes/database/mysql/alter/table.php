<?php

/**
 * ALTER TABLE statement for MySQL. Allows the name, type and position of columns to be changed.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/alter-table.html
 */
class Database_MySQL_Alter_Table extends Database_Command_Alter_Table
{
	/**
	 * Append a FIRST or AFTER clause to a Database_Expression.
	 *
	 * @param   Database_Expression $expression
	 * @param   boolean|mixed       $after      TRUE for FIRST or Converted to Database_Column
	 * @return  Database_Expression Modified expression object
	 */
	protected function _position($expression, $after)
	{
		if ($after === TRUE)
		{
			$expression->_value .= ' FIRST';
		}
		elseif ($after)
		{
			if ( ! $after instanceof Database_Expression
				AND ! $after instanceof Database_Identifier)
			{
				$after = new Database_Column($after);
			}

			$expression->_value .= ' AFTER ?';
			$expression->parameters[] = $after;
		}

		return $expression;
	}

	/**
	 * Add a column to the table, optionally specifying the position.
	 *
	 * @param   Database_DDL_Column $column
	 * @param   boolean|mixed       $after  TRUE for FIRST or Converted to Database_Column
	 * @return  $this
	 */
	public function add_column($column, $after = FALSE)
	{
		$this->parameters[':actions'][] = $this->_position(new Database_Expression('ADD ?', array($column)), $after);

		return $this;
	}

	/**
	 * Change a column in the table, optionally specifying the position.
	 *
	 * @param   mixed               $name   Converted to Database_Column
	 * @param   Database_DDL_Column $column
	 * @param   boolean|mixed       $after  TRUE for FIRST or Converted to Database_Column
	 * @return  $this
	 */
	public function change_column($name, $column, $after = FALSE)
	{
		if ( ! $name instanceof Database_Expression
			AND ! $name instanceof Database_Identifier)
		{
			$name = new Database_Column($name);
		}

		$this->parameters[':actions'][] = $this->_position(new Database_Expression('CHANGE ? ?', array($name, $column)), $after);

		return $this;
	}

	public function drop_constraint($type, $name)
	{
		if ( ! $name instanceof Database_Expression
			AND ! $name instanceof Database_Identifier)
		{
			$name = new Database_Identifier($name);
		}

		$type = strtoupper($type);

		if ($type === 'FOREIGN')
		{
			$this->parameters[':actions'][] = new Database_Expression('DROP FOREIGN KEY ?', array($name));
		}
		elseif ($type === 'PRIMARY')
		{
			$this->parameters[':actions'][] = new Database_Expression('DROP PRIMARY KEY');
		}
		elseif ($type !== 'CHECK')
		{
			$this->parameters[':actions'][] = new Database_Expression('DROP INDEX ?', array($name));
		}

		return $this;
	}

	/**
	 * Set a table option.
	 *
	 * @param   string  $option ENGINE, CHARACTER SET, etc.
	 * @param   mixed   $value
	 * @return  $this
	 */
	public function option($option, $value)
	{
		$this->parameters[':actions'][] = new Database_Expression("$option ?", array($value));

		return $this;
	}
}
