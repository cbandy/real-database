<?php

/**
 * SELECT statement for SQL Server.
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/library/ms189499.aspx
 */
class Database_SQLServer_DML_Select extends Database_DML_Select
{
	public function __toString()
	{
		$value = 'SELECT';

		if ($this->_distinct)
		{
			$value .= ' DISTINCT';
		}

		if (isset($this->parameters[':limit'])
			AND empty($this->parameters[':offset']))
		{
			$value .= ' TOP (:limit)';
		}

		$value .= empty($this->parameters[':values']) ? ' *' : ' :values';

		if ( ! empty($this->parameters[':offset']))
		{
			$value .= ', ROW_NUMBER() OVER(ORDER BY :orderby) AS kohana_row_number';
		}

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

		if ( ! empty($this->parameters[':offset']))
		{
			$table = 'kohana_'.sha1($value.','.serialize($this->parameters));

			// Using a CTE here would prevent this query from being a subquery
			$value = 'SELECT * FROM ('.$value.') AS '.$table
				.' WHERE '.$table.'.kohana_row_number > :offset';

			if (isset($this->parameters[':limit']))
			{
				$value .= ' AND '.$table.'.kohana_row_number <= (:offset + :limit)';
			}
		}
		elseif ( ! empty($this->parameters[':orderby']))
		{
			$value .= ' ORDER BY :orderby';
		}

		return $value;
	}
}
