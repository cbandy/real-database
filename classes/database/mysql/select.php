<?php

/**
 * SELECT statement for MySQL. Allows OFFSET without LIMIT, and automatically
 * uses the DUAL table when necessary.
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/select.html
 */
class Database_MySQL_Select extends Database_DML_Select
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
		elseif ( ! empty($this->parameters[':where']))
		{
			$value .= ' FROM DUAL';
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
				// The maximum value of bigint unsigned
				$value .= '18446744073709551615';
			}
		}
		elseif (isset($this->parameters[':limit']))
		{
			$value .= ' LIMIT :limit';
		}

		return $value;
	}
}
