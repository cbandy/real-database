<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Conditions extends Database_Expression
{
	/**
	 * @var bool    Whether or not the (sub-)expression has just begun
	 */
	protected $_empty = TRUE;

	/**
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
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
	 * Add a condition using a logical operator when necessary
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function add($logic, $left, $operator = NULL, $right = NULL)
	{
		if ( ! $this->_empty)
		{
			// Only append the logical operator between conditions
			$this->_value .= ' '.strtoupper($logic).' ';
		}

		$this->_empty = FALSE;
		$this->parameters[] = $left;
		$this->_value .= '?';

		if ( ! empty($operator))
		{
			// SQL operators are always uppercase
			$operator = strtoupper($operator);

			$this->_value .= " $operator ";

			if ($operator === 'IN')
			{
				$this->parameters[] = $right;
				$this->_value .= '(?)';
			}
			elseif ($operator === 'BETWEEN' AND is_array($right))
			{
				// BETWEEN always has exactly two arguments
				list($min, $max) = $right;

				$this->parameters[] = $min;
				$this->parameters[] = $max;
				$this->_value .= "? AND ?";
			}
			else
			{
				$this->parameters[] = $right;
				$this->_value .= '?';
			}
		}

		return $this;
	}

	/**
	 * Add a condition while converting the LHS to a column
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
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
	 * Add a condition while converting both operands to columns
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
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
	 * Open parenthesis using a logical operator when necessary, optionally
	 * adding another condition.
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function open($logic, $left = NULL, $operator = NULL, $right = NULL)
	{
		if ( ! $this->_empty)
		{
			// Only append the logical operator between conditions
			$this->_value .= ' '.strtoupper($logic).' ';
		}

		$this->_empty = TRUE;
		$this->_value .= '(';

		if ($left !== NULL OR $operator !== NULL)
		{
			$this->add(NULL, $left, $operator, $right);
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
	 * Add a condition using AND while converting the LHS to a column
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function and_column($left_column, $operator, $right)
	{
		return $this->column('AND', $left_column, $operator, $right);
	}

	/**
	 * Add a condition using AND while converting both operands to columns
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function and_columns($left_column, $operator, $right_column)
	{
		return $this->columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * Open a parenthesis using AND, optionally adding another condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function and_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->open('AND', $left, $operator, $right);
	}

	/**
	 * Add a condition using OR while converting the LHS to a column
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function or_column($left_column, $operator, $right)
	{
		return $this->column('OR', $left_column, $operator, $right);
	}

	/**
	 * Add a condition using OR while converting both operands to columns
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function or_columns($left_column, $operator, $right_column)
	{
		return $this->columns('OR', $left_column, $operator, $right_column);
	}

	/**
	 * Open a parenthesis using OR, optionally adding another condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function or_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->open('OR', $left, $operator, $right);
	}
}
