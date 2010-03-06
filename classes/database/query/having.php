<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database_Query_Having extends Database_Query_Where
{
	/**
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function group_by(array $columns)
	{
		foreach ($columns as &$column)
		{
			if ( ! $column instanceof Database_Expression
				AND ! $column instanceof Database_Identifier)
			{
				$column = new Database_Column($column);
			}
		}

		return $this->param(':groupby', $columns);
	}

	/**
	 * @param   Database_Query_Conditions   $conditions
	 * @return  $this
	 */
	public function having($conditions)
	{
		return $this->param(':having', $conditions);
	}
}
