<?php

/**
 * DELETE statement for PostgreSQL. Allows a result set from the deleted rows to be returned.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-delete.html
 */
class Database_PostgreSQL_Delete extends Database_Command_Delete
{
	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	public function __toString()
	{
		if (isset($this->parameters[':limit']))
		{
			$value = 'DELETE FROM :table WHERE ctid IN (SELECT ctid FROM :table';

			if ( ! empty($this->parameters[':where']))
			{
				$value .= ' WHERE :where';
			}

			$value .= ' LIMIT :limit)';
		}
		else
		{
			$value = parent::__toString();
		}

		if ( ! empty($this->parameters[':returning']))
		{
			$value .= ' RETURNING :returning';
		}

		return $value;
	}

	/**
	 * Return rows as associative arrays when executed.
	 *
	 * @return  $this
	 */
	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	/**
	 * Set the class as which to return rows when executed.
	 *
	 * @param   string|boolean  $class  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 * @return  $this
	 */
	public function as_object($class = TRUE)
	{
		$this->as_object = $class;

		return $this;
	}

	/**
	 * Execute the DELETE on a Database. Returns a result set when returning()
	 * is set.
	 *
	 * @param   Database_PostgreSQL $db Connection on which to execute
	 * @return  integer                     Number of affected rows
	 * @return  Database_PostgreSQL_Result  Result set
	 */
	public function execute($db)
	{
		if (empty($this->parameters[':returning']))
			return parent::execute($db);

		return $db->execute_query($db->quote($this), $this->as_object);
	}

	public function limit($count)
	{
		if ($count !== NULL and ! empty($this->parameters[':using']))
			throw new Kohana_Exception('PostgreSQL DELETE does not support LIMIT with USING');

		return parent::limit($count);
	}

	/**
	 * Append values to return when executed
	 *
	 * @param   mixed   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function returning($columns)
	{
		if (is_array($columns))
		{
			foreach ($columns as $alias => $column)
			{
				if ( ! $column instanceof Database_Expression
					AND ! $column instanceof Database_Identifier)
				{
					$column = new Database_Column($column);
				}

				if (is_string($alias) AND $alias !== '')
				{
					$column = new Database_Expression('? AS ?', array($column, new Database_Identifier($alias)));
				}

				$this->parameters[':returning'][] = $column;
			}
		}
		elseif ($columns === NULL)
		{
			unset($this->parameters[':returning']);
		}
		else
		{
			$this->parameters[':returning'] = $columns;
		}

		return $this;
	}

	public function using($reference, $table_alias = NULL)
	{
		if ( ! empty($reference) AND ! empty($this->parameters[':limit']))
			throw new Kohana_Exception('PostgreSQL DELETE does not support LIMIT with USING');

		return parent::using($reference, $table_alias);
	}
}
