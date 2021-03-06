<?php

/**
 * ALTER TABLE statement for MySQL. Allows the name, type and position of
 * columns to be changed.
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
class Database_MySQL_DDL_Alter_Table extends SQL_DDL_Alter_Table
{
	/**
	 * Append a FIRST or AFTER clause to an SQL_Expression.
	 *
	 * @param   SQL_Expression                                      $expression
	 * @param   array|boolean|string|SQL_Expression|SQL_Identifier  $after      TRUE for FIRST or Converted to SQL_Column
	 * @return  SQL_Expression Modified expression object
	 */
	protected function _position($expression, $after)
	{
		if ($after === TRUE)
		{
			$expression->_value .= ' FIRST';
		}
		elseif ($after)
		{
			if ( ! $after instanceof SQL_Expression
				AND ! $after instanceof SQL_Identifier)
			{
				$after = new SQL_Column($after);
			}

			$expression->_value .= ' AFTER ?';
			$expression->parameters[] = $after;
		}

		return $expression;
	}

	/**
	 * Add a column to the table, optionally specifying the position.
	 *
	 * @param   SQL_DDL_Column                                      $column
	 * @param   array|boolean|string|SQL_Expression|SQL_Identifier  $after  TRUE for FIRST or Converted to SQL_Column
	 * @return  $this
	 */
	public function add_column($column, $after = FALSE)
	{
		$this->parameters[':actions'][] = $this->_position(
			new SQL_Expression('ADD ?', array($column)), $after
		);

		return $this;
	}

	/**
	 * Change a column in the table, optionally specifying the position.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier          $name   Converted to SQL_Column
	 * @param   SQL_DDL_Column                                      $column
	 * @param   array|boolean|string|SQL_Expression|SQL_Identifier  $after  TRUE for FIRST or Converted to SQL_Column
	 * @return  $this
	 */
	public function change_column($name, $column, $after = FALSE)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->parameters[':actions'][] = $this->_position(
			new SQL_Expression('CHANGE ? ?', array($name, $column)), $after
		);

		return $this;
	}

	public function drop_constraint($type, $name)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Identifier($name);
		}

		$type = strtoupper($type);

		if ($type === 'FOREIGN')
		{
			$this->parameters[':actions'][] = new SQL_Expression(
				'DROP FOREIGN KEY ?', array($name)
			);
		}
		elseif ($type === 'PRIMARY')
		{
			$this->parameters[':actions'][] = new SQL_Expression(
				'DROP PRIMARY KEY'
			);
		}
		elseif ($type !== 'CHECK')
		{
			$this->parameters[':actions'][] = new SQL_Expression(
				'DROP INDEX ?', array($name)
			);
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
		$this->parameters[':actions'][] = new SQL_Expression(
			$option.' ?', array($value)
		);

		return $this;
	}
}
