<?php

/**
 * Builder for combining queries using the UNION, INTERSECT and EXCEPT operators.
 *
 * @package     RealDatabase
 * @category    Queries
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
		parent::__construct('');

		if ($query !== NULL)
		{
			$this->add(NULL, $query);
		}
	}

	public function __toString()
	{
		$value = $this->_value;

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
	 * Open parenthesis using a combination operator when necessary, optionally
	 * adding another query
	 *
	 * @param   string          $operator   EXCEPT, INTERSECT, or UNION
	 * @param   Database_Query  $query
	 * @return  $this
	 */
	public function open($operator, $query = NULL)
	{
		if ( ! $this->_empty)
		{
			$this->_value .= ' '.strtoupper($operator).' ';
		}

		$this->_empty = TRUE;
		$this->_value .= '(';

		if ($query !== NULL)
		{
			$this->add(NULL, $query);
		}

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
		$this->_value .= ')';

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
			$this->_value .= ' '.strtoupper($operator).' ';
		}

		$this->_empty = FALSE;
		$this->parameters[] = $query;
		$this->_value .= '(?)';

		return $this;
	}

	/**
	 * Add a query using EXCEPT
	 *
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function except($query, $all = FALSE)
	{
		return $this->add($all ? 'EXCEPT ALL' : 'EXCEPT', $query);
	}

	/**
	 * Open a parenthesis using EXCEPT, optionally adding another query
	 *
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function except_open($query = NULL, $all = FALSE)
	{
		return $this->open($all ? 'EXCEPT ALL' : 'EXCEPT', $query);
	}

	/**
	 * Add a query using INTERSECT
	 *
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function intersect($query, $all = FALSE)
	{
		return $this->add($all ? 'INTERSECT ALL' : 'INTERSECT', $query);
	}

	/**
	 * Open a parenthesis using INTERSECT, optionally adding another query
	 *
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function intersect_open($query = NULL, $all = FALSE)
	{
		return $this->open($all ? 'INTERSECT ALL' : 'INTERSECT', $query);
	}

	/**
	 * Set the maximum number of rows
	 *
	 * @param   integer $count  Number of rows
	 * @return  $this
	 */
	public function limit($count)
	{
		$this->parameters[':limit'] = $count;

		return $this;
	}

	/**
	 * Set the number of rows to skip
	 *
	 * @param   integer $start  Number of rows
	 * @return  $this
	 */
	public function offset($start)
	{
		$this->parameters[':offset'] = $start;

		return $this;
	}

	/**
	 * Append a column or expression by which rows should be sorted
	 *
	 * @param   mixed   $column     Converted to Database_Column
	 * @param   mixed   $direction  Direction of sort
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
			$column = ($direction instanceof Database_Expression)
				? new Database_Expression('? ?', array($column, $direction))
				: new Database_Expression('? '.strtoupper($direction), array($column));
		}

		$this->parameters[':orderby'][] = $column;

		return $this;
	}

	/**
	 * Add a query using UNION
	 *
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function union($query, $all = FALSE)
	{
		return $this->add($all ? 'UNION ALL' : 'UNION', $query);
	}

	/**
	 * Open a parenthesis using UNION, optionally adding another query
	 *
	 * @param   Database_Query  $query
	 * @param   boolean         $all    Allow duplicate rows
	 * @return  $this
	 */
	public function union_open($query = NULL, $all = FALSE)
	{
		return $this->open($all ? 'UNION ALL' : 'UNION', $query);
	}
}
