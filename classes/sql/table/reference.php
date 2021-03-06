<?php

/**
 * Expression for building a table reference. Some drivers do not support some
 * features.
 *
 * @package     RealDatabase
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class SQL_Table_Reference extends SQL_Expression
{
	/**
	 * @var bool    Whether or not the (sub-)expression has just begun
	 */
	protected $_empty = TRUE;

	/**
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 */
	public function __construct($table = NULL, $alias = NULL)
	{
		parent::__construct('');

		if ($table !== NULL)
		{
			$this->_add(NULL, $table, $alias);
		}
	}

	/**
	 * Add a table reference using a separator when necessary.
	 *
	 * @param   string                                      $glue   Comma or JOIN
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	protected function _add($glue, $table, $alias)
	{
		if ( ! $this->_empty)
		{
			$this->_value .= $glue.' ';
		}

		if ( ! $table instanceof SQL_Expression
			AND ! $table instanceof SQL_Identifier)
		{
			$table = new SQL_Table($table);
		}

		$this->_empty = FALSE;
		$this->_value .= ($table instanceof SQL_Expression) ? '(?)' : '?';
		$this->parameters[] = $table;

		if ($alias)
		{
			if ( ! $alias instanceof SQL_Expression
				AND ! $alias instanceof SQL_Identifier)
			{
				$alias = new SQL_Identifier($alias);
			}

			$this->_value .= ' AS ?';
			$this->parameters[] = $alias;
		}

		return $this;
	}

	/**
	 * Open parenthesis
	 *
	 * @return  $this
	 */
	public function open()
	{
		if ( ! $this->_empty)
		{
			$this->_value .= ', ';
		}

		$this->_empty = TRUE;
		$this->_value .= '(';

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
	 * Add a table or query.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function add($table, $alias = NULL)
	{
		return $this->_add(',', $table, $alias);
	}

	/**
	 * Join a table or query.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @param   string                                      $type   Join type (e.g., INNER)
	 * @return  $this
	 */
	public function join($table, $alias = NULL, $type = NULL)
	{
		if ($type)
		{
			$type = ' '.strtoupper($type);
		}

		return $this->_add($type.' JOIN', $table, $alias);
	}

	/**
	 * Set the join condition(s). When no operator is specified, the first
	 * argument is used directly.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $left_column    Left operand, converted to SQL_Column
	 * @param   string                                      $operator       Comparison operator
	 * @param   array|string|SQL_Expression|SQL_Identifier  $right_column   Right operand, converted to SQL_Column
	 * @return  $this
	 */
	public function on($left_column, $operator = NULL, $right_column = NULL)
	{
		if ($operator !== NULL)
		{
			if ( ! $left_column instanceof SQL_Expression
				AND ! $left_column instanceof SQL_Identifier)
			{
				$left_column = new SQL_Column($left_column);
			}

			if ( ! $right_column instanceof SQL_Expression
				AND ! $right_column instanceof SQL_Identifier)
			{
				$right_column = new SQL_Column($right_column);
			}

			$left_column = new SQL_Conditions($left_column, $operator, $right_column);
		}

		$this->_empty = FALSE;
		$this->_value .= ' ON (?)';
		$this->parameters[] = $left_column;

		return $this;
	}

	/**
	 * Set the join columns.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array   $columns    List of columns, each converted to SQL_Column
	 * @return  $this
	 */
	public function using($columns)
	{
		$result = array();

		foreach ($columns as $column)
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}

			$result[] = $column;
		}

		$this->_empty = FALSE;
		$this->_value .= ' USING (?)';
		$this->parameters[] = $result;

		return $this;
	}

	/**
	 * Cross join a table or query.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function cross_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'CROSS');
	}

	/**
	 * Full join a table or query.
	 *
	 * [!!] Not supported by MySQL nor SQLite
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'FULL');
	}

	/**
	 * Inner join a table or query.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function inner_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'INNER');
	}

	/**
	 * Left join a table or query.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'LEFT');
	}

	/**
	 * Naturally full join a table or query.
	 *
	 * [!!] Not supported by MySQL, SQLite nor SQL Server
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function natural_full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL FULL');
	}

	/**
	 * Naturally inner join a table or query.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function natural_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL');
	}

	/**
	 * Naturally left join a table or query.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function natural_left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL LEFT');
	}

	/**
	 * Naturally right join a table or query.
	 *
	 * [!!] Not supported by SQLite nor SQL Server
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function natural_right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL RIGHT');
	}

	/**
	 * Right join a table or query.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'RIGHT');
	}
}
