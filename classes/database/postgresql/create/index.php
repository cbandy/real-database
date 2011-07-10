<?php

/**
 * CREATE INDEX statement for PostgreSQL.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-createindex.html
 */
class Database_PostgreSQL_Create_Index extends SQL_DDL_Create_Index
{
	/**
	 * @var string  Index method
	 */
	protected $_using;

	public function __toString()
	{
		$value = 'CREATE';

		if ( ! empty($this->parameters[':type']))
		{
			$value .= ' :type';
		}

		$value .= ' INDEX :name ON :table';

		if ($this->_using)
		{
			$value .= ' USING '.$this->_using;
		}

		$value .= ' (:columns)';

		if ( ! empty($this->parameters[':with']))
		{
			$value .= ' WITH (:with)';
		}

		if ( ! empty($this->parameters[':tablespace']))
		{
			$value .= ' TABLESPACE :tablespace';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}

	/**
	 * Append one column or expression to be included in the index.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $column     Converted to SQL_Column or NULL to reset
	 * @param   string                                      $direction  Direction to sort, ASC or DESC
	 * @param   string                                      $nulls      Position to which NULL values should sort, FIRST or LAST
	 * @return  $this
	 */
	public function column($column, $direction = NULL, $nulls = NULL)
	{
		if ($column === NULL)
		{
			$this->parameters[':columns'] = array();
		}
		else
		{
			if ($column instanceof SQL_Expression)
			{
				// Wrap expression in parentheses
				$column = new SQL_Expression('(?)', array($column));
			}
			elseif ( ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}

			if ($direction OR $nulls)
			{
				if ( ! $column instanceof SQL_Expression)
				{
					$column = new SQL_Expression('?', array($column));
				}

				if ($direction)
				{
					$column->_value .= ' '.strtoupper($direction);
				}

				if ($nulls)
				{
					$column->_value .= ' NULLS '.strtoupper($nulls);
				}
			}

			$this->parameters[':columns'][] = $column;
		}

		return $this;
	}

	/**
	 * Set the tablespace in which to create the index
	 *
	 * @param   mixed   Converted to SQL_Identifier
	 * @return  $this
	 */
	public function tablespace($value)
	{
		if ( ! $value instanceof SQL_Expression
			AND ! $value instanceof SQL_Identifier)
		{
			$value = new SQL_Identifier($value);
		}

		$this->parameters[':tablespace'] = $value;

		return $this;
	}

	/**
	 * Set the index method
	 *
	 * @param   string  $method btree, hash, gist, gin, etc.
	 * @return  $this
	 */
	public function using($method)
	{
		$this->_using = $method;

		return $this;
	}

	/**
	 * Set the conditions for which a row is included in the partial index
	 *
	 * @param   SQL_Conditions  $conditions
	 * @return  $this
	 */
	public function where($conditions)
	{
		$this->parameters[':where'] = $conditions;

		return $this;
	}

	/**
	 * Append storage parameters for the index method.
	 *
	 * @param   array   Hash of (parameter => value) pairs or NULL to reset
	 * @return  $this
	 */
	public function with($parameters)
	{
		if ($parameters === NULL)
		{
			$this->parameters[':with'] = array();
		}
		else
		{
			foreach ($parameters as $param => $value)
			{
				$this->parameters[':with'][] = new SQL_Expression(
					$param.' = ?',
					array($value)
				);
			}
		}

		return $this;
	}
}
