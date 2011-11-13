<?php

/**
 * INSERT statement for PostgreSQL.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-insert.html
 */
class Database_PostgreSQL_DML_Insert extends Database_DML_Insert
{
	/**
	 * Set the name of the column to return from the first row when executed.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $column Converted to SQL_Column
	 * @return  $this
	 */
	public function identity($column)
	{
		if ($column)
		{
			parent::identity($column);

			$this->parameters[':returning'] = $this->identity;
		}
		else
		{
			if (isset($this->parameters[':returning'])
				AND $this->parameters[':returning'] === $this->identity)
			{
				$this->parameters[':returning'] = array();
			}

			parent::identity($column);
		}

		return $this;
	}

	public function returning($columns)
	{
		if ($this->identity)
		{
			$this->parameters[':returning'] = array();
		}

		return parent::returning($columns);
	}
}
