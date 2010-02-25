<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Query_From extends Database_Expression
{
	protected $_empty = TRUE;

	/**
	 * @param   mixed   Converted to Database_Table
	 * @param   string
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
	 * @param   string
	 * @param   mixed   Converted to Database_Table
	 * @param   string
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
		$this->_value .= '?';
		$this->_parameters[] = $table;

		if ( ! empty($alias))
		{
			$this->_value .= ' AS ?';
			$this->_parameters[] = new Database_Identifier($alias);
		}

		return $this;
	}

	/**
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
	 * @return  $this
	 */
	public function close()
	{
		$this->_empty = FALSE;
		$this->_value .= ')';

		return $this;
	}

	/**
	 * @param   mixed   Converted to Database_Table
	 * @param   string
	 * @return  $this
	 */
	public function add($table, $alias = NULL)
	{
		return $this->_add(',', $table, $alias);
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @param   $type   string  Join type
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
	 * @param   Database_Query_Conditions
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
	 * @param   $columns    array
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
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function cross_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'CROSS');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'FULL');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function inner_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'INNER');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'LEFT');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL FULL');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_inner_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL INNER');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL LEFT');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL RIGHT');
	}

	/**
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'RIGHT');
	}
}
