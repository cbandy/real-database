<?php

/**
 * SELECT statement for PostgreSQL. Allows the criteria for DISTINCT rows to be set.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-select.html
 */
class Database_PostgreSQL_Select extends Database_Select
{
	public function __toString()
	{
		$value = 'SELECT';

		if ( ! empty($this->parameters[':distinct']))
		{
			$value .= ' DISTINCT ON (:distinct)';
		}
		elseif ($this->_distinct)
		{
			$value .= ' DISTINCT';
		}

		$value .= ' :columns';

		if ( ! empty($this->parameters[':from']))
		{
			$value .= ' FROM :from';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		if ( ! empty($this->parameters[':groupby']))
		{
			$value .= ' GROUP BY :groupby';
		}

		if ( ! empty($this->parameters[':having']))
		{
			$value .= ' HAVING :having';
		}

		if ( ! empty($this->parameters[':orderby']))
		{
			$value .= ' ORDER BY :orderby';
		}

		if (isset($this->parameters[':limit']))
		{
			$value .= ' LIMIT :limit';
		}

		if ( ! empty($this->parameters[':offset']))
		{
			$value .= ' OFFSET :offset';
		}

		return $value;
	}

	/**
	 * Set values by which rows should be considered unique
	 *
	 * @link http://www.postgresql.org/docs/current/static/sql-select.html#SQL-DISTINCT
	 *
	 * @param   mixed   $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function distinct($columns = TRUE)
	{
		if (is_bool($columns) OR $columns === NULL)
		{
			$this->parameters[':distinct'] = array();

			return parent::distinct($columns);
		}

		$this->_distinct = NULL;

		if (is_array($columns))
		{
			foreach ($columns as & $column)
			{
				if ( ! $column instanceof SQL_Expression
					AND ! $column instanceof SQL_Identifier)
				{
					$column = new SQL_Column($column);
				}
			}
		}

		$this->parameters[':distinct'] = $columns;

		return $this;
	}
}
