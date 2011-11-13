<?php

/**
 * INSERT statement for SQLite. Each row is a separate statement.
 *
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_insert.html
 */
class Database_SQLite_Insert extends Database_DML_Insert
{
	public function values($values)
	{
		if (is_array($values))
		{
			if (empty($this->parameters[':values']) AND func_num_args() === 1)
			{
				// Wrap in parentheses
				$this->parameters[':values'][] = new SQL_Expression(
					'(?)', array($values)
				);
			}
			else
			{
				if (empty($this->parameters[':values']))
				{
					// Initialize the query set
					$this->parameters[':values'] = new Database_SQLite_Set;
				}
				elseif (is_array($this->parameters[':values']))
				{
					$row = reset($this->parameters[':values']);

					// Convert the previous row into a query set
					$select = new SQL_DML_Select;
					$select->values(reset($row->parameters));

					$this->parameters[':values'] = new Database_SQLite_Set($select);
				}
				elseif ( ! $this->parameters[':values'] instanceof SQL_DML_Set)
				{
					throw new Kohana_Exception(
						'Parameter :values must be an array or SQL_DML_Select'
					);
				}

				foreach (func_get_args() as $row)
				{
					$select = new SQL_DML_Select;
					$select->values($row);

					$this->parameters[':values']->union($select, TRUE);
				}
			}
		}
		else
		{
			$this->parameters[':values'] = $values;
		}

		return $this;
	}
}
