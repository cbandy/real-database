<?php

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLite_Set extends Database_Query_Set
{
	public function add($operator, $query)
	{
		if ( ! $this->_empty)
		{
			$this->_value .= ' '.strtoupper($operator).' ';
		}

		$this->_empty = FALSE;
		$this->_value .= '?';

		if ($query instanceof SQL_DML_Select
			AND (isset($query->parameters[':limit'])
				OR ! empty($query->parameters[':orderby'])))
		{
			$select = new SQL_DML_Select;
			$select->from($query);

			$this->parameters[] = $select;
		}
		else
		{
			$this->parameters[] = $query;
		}

		return $this;
	}
}
