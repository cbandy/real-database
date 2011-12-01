<?php

/**
 * DELETE statement for MySQL. Allows rows to be deleted according to ORDER BY.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/delete.html
 */
class Database_MySQL_DML_Delete extends Database_DML_Delete
{
	public function __toString()
	{
		$value = 'DELETE FROM :table';

		if ( ! empty($this->parameters[':using']))
		{
			$value .= ' USING :using';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		if ( ! empty($this->parameters[':orderby']))
		{
			$value .= ' ORDER BY :orderby';
		}

		if (isset($this->parameters[':limit']))
		{
			$value .= ' LIMIT :limit';
		}

		return $value;
	}

	/**
	 * Append a column or expression specifying the order in which rows should
	 * be deleted.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $column     Converted to SQL_Column or NULL to reset
	 * @param   string|SQL_Expression                       $direction  Direction of sort
	 * @return  $this
	 */
	public function order_by($column, $direction = NULL)
	{
		if ($column === NULL)
		{
			$this->parameters[':orderby'] = array();
		}
		else
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}

			if ($direction)
			{
				$column = ($direction instanceof SQL_Expression)
					? new SQL_Expression('? ?', array($column, $direction))
					: new SQL_Expression('? '.strtoupper($direction), array($column));
			}

			$this->parameters[':orderby'][] = $column;
		}

		return $this;
	}
}
