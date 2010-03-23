<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_From extends Database_Expression
{
	/**
	 * @var bool    Whether or not the (sub-)expression has just begun
	 */
	protected $_empty = TRUE;

	/**
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 */
	public function __construct($table = NULL, $alias = NULL)
	{
		parent::__construct('');

		if ($table !== NULL)
		{
			$this->add($table, $alias);
		}
	}

	/**
	 * Add a table reference using a separator when necessary
	 *
	 * @param   string  $glue   Comma or JOIN
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	protected function _add($glue, $table, $alias)
	{
		if ( ! $this->_empty)
		{
			$this->_value .= "$glue ";
		}

		if ( ! $table instanceof Database_Expression
			AND ! $table instanceof Database_Identifier)
		{
			$table = new Database_Table($table);
		}

		$this->_empty = FALSE;
		$this->_value .= ($table instanceof Database_Query) ? '(?)' : '?';
		$this->_parameters[] = $table;

		if ( ! empty($alias))
		{
			$this->_value .= ' AS ?';
			$this->_parameters[] = new Database_Identifier($alias);
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
	 * Add a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function add($table, $alias = NULL)
	{
		return $this->_add(',', $table, $alias);
	}

	/**
	 * Join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @param   string  $type   Join type (e.g., INNER)
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
	 * Set the join conditions
	 *
	 * @param   Database_Query_Conditions   $conditions
	 * @return  $this
	 */
	public function on($conditions)
	{
		$this->_empty = FALSE;
		$this->_value .= ' ON (?)';
		$this->_parameters[] = $conditions;

		return $this;
	}

	/**
	 * Set the join columns
	 *
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function using(array $columns)
	{
		foreach ($columns as &$column)
		{
			if ( ! $column instanceof Database_Expression
				AND ! $column instanceof Database_Identifier)
			{
				$column = new Database_Column($column);
			}
		}

		$this->_empty = FALSE;
		$this->_value .= ' USING (?)';
		$this->_parameters[] = $columns;

		return $this;
	}

	/**
	 * Cross join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function cross_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'CROSS');
	}

	/**
	 * Full join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'FULL');
	}

	/**
	 * Inner join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function inner_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'INNER');
	}

	/**
	 * Left join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'LEFT');
	}

	/**
	 * Naturally full join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function natural_full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL FULL');
	}

	/**
	 * Naturally inner join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function natural_inner_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL INNER');
	}

	/**
	 * Naturally left join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function natural_left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL LEFT');
	}

	/**
	 * Naturally right join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function natural_right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL RIGHT');
	}

	/**
	 * Right join a table or query
	 *
	 * @param   mixed   $table  Converted to Database_Table
	 * @param   string  $alias  Table alias
	 * @return  $this
	 */
	public function right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'RIGHT');
	}
}
