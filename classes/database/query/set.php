<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/select.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-select.html PostgreSQL
 * @link http://www.sqlite.org/lang_select.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms189499.aspx Transact-SQL
 */
class Database_Query_Set extends Database_Query
{
	/**
	 * @var bool    Whether or not the (sub-)expression has just begun
	 */
	protected $_empty = TRUE;

	/**
	 * @param   Database_Query  $query
	 */
	public function __construct($query = NULL)
	{
		parent::__construct('', array(':queries' => new Database_Expression('')));

		if ($query !== NULL)
		{
			$this->add(NULL, $query);
		}
	}

	public function __toString()
	{
		$value = ':queries';

		if ( ! empty($this->parameters[':orderby']))
		{
			$value .= ' ORDER BY :orderby';
		}

		if (isset($this->parameters[':limit']))
		{
			// Not allowed in MSSQL
			$value .= ' LIMIT :limit';
		}

		if ( ! empty($this->parameters[':offset']))
		{
			// LIMIT required by MySQL and SQLite
			// Not allowed in MSSQL
			$value .= ' OFFSET :offset';
		}

		return $value;
	}

	/**
	 * Open parenthesis
	 *
	 * @param   string  $operator   EXCEPT, INTERSECT, or UNION
	 * @return  $this
	 */
	public function open($operator)
	{
		if ( ! $this->_empty)
		{
			$this->parameters[':queries']->_value .= ' '.strtoupper($operator).' ';
		}

		$this->_empty = TRUE;
		$this->parameters[':queries']->_value .= '(';

		return $this;
	}

	/**
	 * Close parenthesis
	 *
	 * @return  $this
	 */
	public function close()
	{
		$this->_empty = FALSE;
		$this->parameters[':queries']->_value .= ')';

		return $this;
	}

	/**
	 * Add a query using a combination operator when necessary
	 *
	 * @param   string          $operator   EXCEPT, INTERSECT, or UNION
	 * @param   Database_Query  $query
	 * @return  $this
	 */
	public function add($operator, $query)
	{
		if ( ! $this->_empty)
		{
			$this->parameters[':queries']->_value .= ' '.strtoupper($operator).' ';
		}

		$this->_empty = FALSE;
		$this->parameters[':queries']->parameters[] = $query;
		$this->parameters[':queries']->_value .= "(?)";

		return $this;
	}

	/**
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function except($query, $all = FALSE)
	{
		return $this->add($all ? 'EXCEPT ALL' : 'EXCEPT', $query);
	}

	/**
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function intersect($query, $all = FALSE)
	{
		return $this->add($all ? 'INTERSECT ALL' : 'INTERSECT', $query);
	}

	/**
	 * Set the maximum number of rows
	 *
	 * @param   integer $count  Number of rows
	 * @return  $this
	 */
	public function limit($count)
	{
		return $this->param(':limit', $count);
	}

	/**
	 * Set the number of rows to skip
	 *
	 * @param   integer $start  Number of rows
	 * @return  $this
	 */
	public function offset($start)
	{
		return $this->param(':offset', $start);
	}

	/**
	 * @param   mixed   Converted to Database_Column
	 * @param   mixed
	 * @return  $this
	 */
	public function order_by($column, $direction = NULL)
	{
		if ( ! $column instanceof Database_Expression
			AND ! $column instanceof Database_Identifier)
		{
			$column = new Database_Column($column);
		}

		if ($direction)
		{
			if ( ! $direction instanceof Database_Expression)
			{
				$direction = new Database_Expression(strtoupper($direction));
			}

			$column = new Database_Expression('? ?', array($column, $direction));
		}

		$this->parameters[':orderby'][] = $column;

		return $this;
	}

	/**
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function union($query, $all = FALSE)
	{
		return $this->add($all ? 'UNION ALL' : 'UNION', $query);
	}
}
