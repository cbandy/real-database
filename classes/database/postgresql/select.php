<?php

/**
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
class Database_PostgreSQL_Select extends Database_Query_Select
{
	/**
	 * Set values by which rows should be considered unique
	 *
	 * @link http://www.postgresql.org/docs/current/static/sql-select.html#SQL-DISTINCT
	 *
	 * @param   mixed   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function distinct($columns = TRUE)
	{
		if (is_bool($columns) OR $columns === NULL)
			return parent::distinct($columns);

		if (is_array($columns))
		{
			foreach ($columns as & $column)
			{
				if ( ! $column instanceof Database_Expression
					AND ! $column instanceof Database_Identifier)
				{
					$column = new Database_Column($column);
				}
			}
		}

		$this->parameters[':distinct'] = new Database_Expression('DISTINCT ON (?)', array($columns));

		return $this;
	}
}
