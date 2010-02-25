<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Query_Conditions extends Database_Expression
{
	protected $_empty = TRUE;

	/**
	 * @param   mixed
	 * @param   string
	 * @param   mixed
	 */
	public function __construct($left = NULL, $operator = NULL, $right = NULL)
	{
		parent::__construct('');

		if ($left !== NULL OR $operator !== NULL)
		{
			$this->add(NULL, $left, $operator, $right);
		}
	}

	/**
	 * @param   string
	 * @return  $this
	 */
	public function open($logic)
	{
		if ( ! $this->_empty)
		{
			// Only append the logic operator between conditions
			$this->_value .= ' '.strtoupper($logic).' ';
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
	 * @param   string
	 * @param   mixed
	 * @param   string
	 * @param   mixed
	 * @return  $this
	 */
	public function add($logic, $left, $operator = NULL, $right = NULL)
	{
		if ( ! $this->_empty)
		{
			// Only append the logic operator between conditions
			$this->_value .= ' '.strtoupper($logic).' ';
		}

		$this->_empty = FALSE;
		$this->_parameters[] = $left;
		$this->_value .= '?';

		if ( ! empty($operator))
		{
			// Database operators are always uppercase
			$operator = strtoupper($operator);

			$this->_value .= " $operator ";

			if ($operator === 'IN')
			{
				$this->_parameters[] = $right;
				$this->_value .= '(?)';
			}
			elseif ($operator === 'BETWEEN' AND is_array($right))
			{
				// BETWEEN always has exactly two arguments
				list($min, $max) = $right;

				$this->_parameters[] = $min;
				$this->_parameters[] = $max;
				$this->_value .= "? AND ?";
			}
			else
			{
				$this->_parameters[] = $right;
				$this->_value .= '?';
			}
		}

		return $this;
	}

	/**
	 * @param   $logic          string
	 * @param   $left_column    mixed   Converted to Database_Column
	 * @param   $operator       string
	 * @param   $right          mixed
	 * @return  $this
	 */
	public function column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Database_Expression
			AND ! $left_column instanceof Database_Identifier)
		{
			$left_column = new Database_Column($left_column);
		}

		return $this->add($logic, $left_column, $operator, $right);
	}

	/**
	 * @param   $logic          string
	 * @param   $left_column    mixed   Converted to Database_Column
	 * @param   $operator       string
	 * @param   $right_column   mixed   Converted to Database_Column
	 * @return  $this
	 */
	public function columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Database_Expression
			AND ! $right_column instanceof Database_Identifier)
		{
			$right_column = new Database_Column($right_column);
		}

		return $this->column($logic, $left_column, $operator, $right_column);
	}

	/**
	 * @param   $left_column    mixed   Converted to Database_Column
	 * @param   $operator       string
	 * @param   $right          mixed
	 * @return  $this
	 */
	public function and_column($left_column, $operator, $right)
	{
		return $this->column('AND', $left_column, $operator, $right);
	}

	/**
	 * @param   $left_column    mixed   Converted to Database_Column
	 * @param   $operator       string
	 * @param   $right_column   mixed   Converted to Database_Column
	 * @return  $this
	 */
	public function and_columns($left_column, $operator, $right_column)
	{
		return $this->columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * @return  $this
	 */
	public function and_open()
	{
		return $this->open('AND');
	}

	/**
	 * @param   $left_column    mixed   Converted to Database_Column
	 * @param   $operator       string
	 * @param   $right          mixed
	 * @return  $this
	 */
	public function or_column($left_column, $operator, $right)
	{
		return $this->column('OR', $left_column, $operator, $right);
	}

	/**
	 * @param   $left_column    mixed   Converted to Database_Column
	 * @param   $operator       string
	 * @param   $right_column   mixed   Converted to Database_Column
	 * @return  $this
	 */
	public function or_columns($left_column, $operator, $right_column)
	{
		return $this->columns('OR', $left_column, $operator, $right_column);
	}

	/**
	 * @return  $this
	 */
	public function or_open()
	{
		return $this->open('OR');
	}
}
