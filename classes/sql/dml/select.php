<?php

/**
 * Query for building SELECT statements.
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
class SQL_DML_Select extends SQL_Expression
{
	/**
	 * @uses SQL_DML_Select::select()
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 */
	public function __construct($columns = NULL)
	{
		parent::__construct('');

		$this->select($columns);
	}

	public function __toString()
	{
		$value = 'SELECT';

		if ( ! empty($this->parameters[':distinct']))
		{
			$value .= ' :distinct';
		}

		$value .= ' :columns';

		if ( ! empty($this->parameters[':from']))
		{
			$value .= ' FROM :from';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		if ( ! empty($this->parameters[':groupby']))
		{
			$value .= ' GROUP BY :groupby';
		}

		if ( ! empty($this->parameters[':having']))
		{
			$value .= ' HAVING :having';
		}

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
	 * Append one column or expression to be selected
	 *
	 * @param   mixed   $column Converted to SQL_Column
	 * @param   string  $alias  Column alias
	 * @return  $this
	 */
	public function column($column, $alias = NULL)
	{
		if ( ! $column instanceof SQL_Expression
			AND ! $column instanceof SQL_Identifier)
		{
			$column = new SQL_Column($column);
		}

		if ($alias)
		{
			$column = new SQL_Expression('? AS ?', array($column, new SQL_Identifier($alias)));
		}

		$this->parameters[':columns'][] = $column;

		return $this;
	}

	/**
	 * Set whether or not retrieved rows should be unique
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function distinct($value = TRUE)
	{
		$this->parameters[':distinct'] = $value ? new SQL_Expression('DISTINCT') : FALSE;

		return $this;
	}

	/**
	 * Set the table(s) from which to retrieve rows
	 *
	 * @param   mixed   $reference      SQL_From or converted to SQL_Table
	 * @param   string  $table_alias    Table alias when converting to SQL_Table
	 * @return  $this
	 */
	public function from($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof SQL_From)
		{
			$reference = new SQL_From($reference, $table_alias);
		}

		$this->parameters[':from'] = $reference;

		return $this;
	}

	/**
	 * Set the columns by which rows should be grouped
	 *
	 * @param   array   $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function group_by($columns)
	{
		foreach ($columns as & $column)
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}
		}

		$this->parameters[':groupby'] = $columns;

		return $this;
	}

	/**
	 * Set the group search condition(s). When no operator is specified, the
	 * first argument is used directly.
	 *
	 * @param   mixed   $left_column    Left operand, converted to SQL_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function having($left_column, $operator = NULL, $right = NULL)
	{
		if ($operator !== NULL)
		{
			if ( ! $left_column instanceof SQL_Expression
				AND ! $left_column instanceof SQL_Identifier)
			{
				$left_column = new SQL_Column($left_column);
			}

			$left_column = new SQL_Conditions($left_column, $operator, $right);
		}

		$this->parameters[':having'] = $left_column;

		return $this;
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
	 * @param   mixed   $column     Converted to SQL_Column
	 * @param   mixed   $direction  Direction of sort
	 * @return  $this
	 */
	public function order_by($column, $direction = NULL)
	{
		if ( ! $column instanceof SQL_Expression
			AND ! $column instanceof SQL_Identifier)
		{
			$column = new SQL_Column($column);
		}

		if ($direction)
		{
			$column = ($direction instanceof SQL_Expression)
				? new SQL_Expression('? ?', array($column, $direction))
				: new SQL_Expression('? '.strtoupper($direction), array($column));
		}

		$this->parameters[':orderby'][] = $column;

		return $this;
	}

	/**
	 * Append multiple columns or expressions to be selected
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 * @return  $this
	 */
	public function select($columns)
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

				$this->parameters[':columns'][] = $column;
			}
		}
		elseif ($columns === NULL)
		{
			$this->parameters[':columns'] = array();
		}
		else
		{
			$this->parameters[':columns'] = $columns;
		}

		return $this;
	}

	/**
	 * Set the search condition(s). When no operator is specified, the first
	 * argument is used directly.
	 *
	 * @param   mixed   $left_column    Left operand, converted to SQL_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function where($left_column, $operator = NULL, $right = NULL)
	{
		if ($operator !== NULL)
		{
			if ( ! $left_column instanceof SQL_Expression
				AND ! $left_column instanceof SQL_Identifier)
			{
				$left_column = new SQL_Column($left_column);
			}

			$left_column = new SQL_Conditions($left_column, $operator, $right);
		}

		$this->parameters[':where'] = $left_column;

		return $this;
	}
}