<?php

/**
 * SELECT statement for SQLite. Allows OFFSET without LIMIT.
 *
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_select.html
 * @link http://www.sqlite.org/limits.html
 */
class Database_SQLite_Select extends Database_DML_Select
{
	public function __toString()
	{
		$value = 'SELECT';

		if ($this->_distinct)
		{
			$value .= ' DISTINCT';
		}

		$value .= empty($this->parameters[':values']) ? ' *' : ' :values';

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

		if ( ! empty($this->parameters[':offset']))
		{
			$value .= ' LIMIT :offset,';

			if (isset($this->parameters[':limit']))
			{
				$value .= ':limit';
			}
			else
			{
				// The maximum value of bigint
				$value .= '9223372036854775807';
			}
		}
		elseif (isset($this->parameters[':limit']))
		{
			$value .= ' LIMIT :limit';
		}

		return $value;
	}
}
