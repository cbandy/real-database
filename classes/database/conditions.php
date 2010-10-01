<?php

/**
 * @package     RealDatabase
 * @category    Expressions
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
	 * Append an operator and process its right operand.
	 *
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  void
	 */
	protected function _add_rhs($operator, $right)
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

	/**
	 * Add a condition using a logical operator when necessary.
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
			$this->_add_rhs($operator, $right);
		}

		return $this;
	}

	/**
	 * Add a condition while converting the LHS to a column.
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
	 * Add a condition while converting both operands to columns.
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
	 * Add a negated condition using a logical operator when necessary.
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function not($logic, $left, $operator = NULL, $right = NULL)
	{
		if ( ! $this->_empty)
		{
			// Only append the logical operator between conditions
			$this->_value .= ' '.strtoupper($logic).' ';
		}

		$this->_empty = FALSE;
		$this->parameters[] = $left;
		$this->_value .= 'NOT ?';

		if ( ! empty($operator))
		{
			$this->_add_rhs($operator, $right);
		}

		return $this;
	}

	/**
	 * Add a negated condition while converting the LHS to a column.
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function not_column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Database_Expression
			AND ! $left_column instanceof Database_Identifier)
		{
			$left_column = new Database_Column($left_column);
		}

		return $this->not($logic, $left_column, $operator, $right);
	}

	/**
	 * Add a negated condition while converting both operands to columns.
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function not_columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Database_Expression
			AND ! $right_column instanceof Database_Identifier)
		{
			$right_column = new Database_Column($right_column);
		}

		return $this->not_column($logic, $left_column, $operator, $right_column);
	}

	/**
	 * Open a negated parenthesis using a logical operator when necessary, optionally adding another
	 * condition.
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function not_open($logic, $left = NULL, $operator = NULL, $right = NULL)
	{
		if ( ! $this->_empty)
		{
			// Only append the logical operator between conditions
			$this->_value .= ' '.strtoupper($logic).' ';
		}

		$this->_empty = TRUE;
		$this->_value .= 'NOT (';

		if ($left !== NULL OR $operator !== NULL)
		{
			$this->add(NULL, $left, $operator, $right);
		}

		return $this;
	}

	/**
	 * Open a negated parenthesis while converting the LHS to a column.
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function not_open_column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Database_Expression
			AND ! $left_column instanceof Database_Identifier)
		{
			$left_column = new Database_Column($left_column);
		}

		return $this->not_open($logic, $left_column, $operator, $right);
	}

	/**
	 * Open a negated parenthesis while converting both operands to columns.
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function not_open_columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Database_Expression
			AND ! $right_column instanceof Database_Identifier)
		{
			$right_column = new Database_Column($right_column);
		}

		return $this->not_open_column($logic, $left_column, $operator, $right_column);
	}

	/**
	 * Open a parenthesis using a logical operator when necessary, optionally adding another
	 * condition.
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
	 * Open a parenthesis while converting the LHS to a column.
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function open_column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Database_Expression
			AND ! $left_column instanceof Database_Identifier)
		{
			$left_column = new Database_Column($left_column);
		}

		return $this->open($logic, $left_column, $operator, $right);
	}

	/**
	 * Open a parenthesis while converting both operands to columns.
	 *
	 * @param   string  $logic          Logical operator
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function open_columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Database_Expression
			AND ! $right_column instanceof Database_Identifier)
		{
			$right_column = new Database_Column($right_column);
		}

		return $this->open_column($logic, $left_column, $operator, $right_column);
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
	 * Add a condition using AND while converting the LHS to a column.
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
	 * Add a condition using AND while converting both operands to columns.
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
	 * Add a negated condition using AND.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function and_not($left, $operator = NULL, $right = NULL)
	{
		return $this->not('AND', $left, $operator, $right);
	}

	/**
	 * Add a negated condition using AND while converting the LHS to a column.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function and_not_column($left_column, $operator, $right)
	{
		return $this->not_column('AND', $left_column, $operator, $right);
	}

	/**
	 * Add a negated condition using AND while converting both operands to columns.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function and_not_columns($left_column, $operator, $right_column)
	{
		return $this->not_columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * Open a negated parenthesis using AND, optionally adding another condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function and_not_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->not_open('AND', $left, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using AND while converting the LHS to a column.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function and_not_open_column($left_column, $operator, $right)
	{
		return $this->not_open_column('AND', $left_column, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using AND while converting both operands to columns.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function and_not_open_columns($left_column, $operator, $right_column)
	{
		return $this->not_open_columns('AND', $left_column, $operator, $right_column);
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
	 * Open a parenthesis using AND while converting the LHS to a column.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function and_open_column($left_column, $operator, $right)
	{
		return $this->open_column('AND', $left_column, $operator, $right);
	}

	/**
	 * Open a parenthesis using AND while converting both operands to columns.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function and_open_columns($left_column, $operator, $right_column)
	{
		return $this->open_columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * Add a condition using OR while converting the LHS to a column.
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
	 * Add a condition using OR while converting both operands to columns.
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
	 * Add a negated condition using OR.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function or_not($left, $operator = NULL, $right = NULL)
	{
		return $this->not('OR', $left, $operator, $right);
	}

	/**
	 * Add a negated condition using OR while converting the LHS to a column.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function or_not_column($left_column, $operator, $right)
	{
		return $this->not_column('OR', $left_column, $operator, $right);
	}

	/**
	 * Add a negated condition using OR while converting both operands to columns.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function or_not_columns($left_column, $operator, $right_column)
	{
		return $this->not_columns('OR', $left_column, $operator, $right_column);
	}

	/**
	 * Open a negated parenthesis using OR, optionally adding another condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function or_not_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->not_open('OR', $left, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using OR while converting the LHS to a column.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function or_not_open_column($left_column, $operator, $right)
	{
		return $this->not_open_column('OR', $left_column, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using OR while converting both operands to columns.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function or_not_open_columns($left_column, $operator, $right_column)
	{
		return $this->not_open_columns('OR', $left_column, $operator, $right_column);
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

	/**
	 * Open a parenthesis using OR while converting the LHS to a column.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right          Right operand
	 * @return  $this
	 */
	public function or_open_column($left_column, $operator, $right)
	{
		return $this->open_column('OR', $left_column, $operator, $right);
	}

	/**
	 * Open a parenthesis using OR while converting both operands to columns.
	 *
	 * @param   mixed   $left_column    Left operand, converted to Database_Column
	 * @param   string  $operator       Comparison operator
	 * @param   mixed   $right_column   Right operand, converted to Database_Column
	 * @return  $this
	 */
	public function or_open_columns($left_column, $operator, $right_column)
	{
		return $this->open_columns('OR', $left_column, $operator, $right_column);
	}
}
