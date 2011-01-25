<?php

/**
 * UPDATE statement for PostgreSQL. Allows a result set from the updated rows to be returned.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-update.html
 */
class Database_PostgreSQL_Update extends SQL_DML_Update
	implements Database_iExecutable, Database_PostgreSQL_iReturning
{
	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	public function __toString()
	{
		if (isset($this->parameters[':limit']))
		{
			$value = 'UPDATE :table SET :values WHERE ctid IN (SELECT ctid FROM :table';

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

	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	public function as_object($class = TRUE)
	{
		$this->as_object = $class;

		return $this;
	}

	/**
	 * Execute the UPDATE on a Database. Returns a result set when returning()
	 * is set.
	 *
	 * @param   Database_PostgreSQL $db Connection on which to execute
	 * @return  integer                     Number of affected rows
	 * @return  Database_PostgreSQL_Result  Result set
	 */
	public function execute($db)
	{
		if (empty($this->parameters[':returning']))
			return $db->execute_command($this);

		return $db->execute_query($this, $this->as_object);
	}

	public function from($reference, $table_alias = NULL)
	{
		if ( ! empty($reference) AND ! empty($this->parameters[':limit']))
			throw new Kohana_Exception('PostgreSQL UPDATE does not support LIMIT with FROM');

		return parent::from($reference, $table_alias);
	}

	public function limit($count)
	{
		if ($count !== NULL AND ! empty($this->parameters[':from']))
			throw new Kohana_Exception('PostgreSQL UPDATE does not support LIMIT with FROM');

		return parent::limit($count);
	}

	public function returning($columns)
	{
		if (is_array($columns))
		{
			foreach ($columns as $alias => $column)
			{
				if ( ! $column instanceof SQL_Expression
					AND ! $column instanceof SQL_Identifier)
				{
					$column = new SQL_Column($column);
				}

				if (is_string($alias) AND $alias !== '')
				{
					$column = new SQL_Expression('? AS ?', array($column, new SQL_Identifier($alias)));
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
}
