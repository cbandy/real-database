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
	 * @param   mixed   Converted to Database_Table
	 * @param   string
	 * @return  $this
	 */
	public function join($table, $alias = NULL)
	{
		return $this->_add(' JOIN', $table, $alias);
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
}
