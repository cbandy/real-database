<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database_Query_Where extends Database_Query
{
	public function __construct($value, array $parameters = array())
	{
		parent::__construct($value, $parameters);

		$this->_reset_from()->_reset_where();
	}

	/**
	 * @return  $this
	 */
	protected function _reset_from()
	{
		return $this->param(':from', new Database_Query_From);
	}

	/**
	 * @return  $this
	 */
	protected function _reset_where()
	{
		return $this->param(':where', new Database_Query_Conditions);
	}

	/**
	 * @param   mixed   Converted to Database_Table
	 * @param   string
	 * @return  $this
	 */
	public function from($table, $alias = NULL)
	{
		$this->_parameters[':from']->add($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::cross_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function cross_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->cross_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::full_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function full_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->full_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::inner_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function inner_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->inner_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @param   $type   string  Join type
	 * @return  $this
	 */
	public function join($table, $alias = NULL, $type = NULL)
	{
		$this->_parameters[':from']->join($table, $alias, $type);

		return $this;
	}

	/**
	 * @uses Database_Query_From::left_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function left_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->left_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::natural_full_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_full_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->natural_full_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::natural_inner_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_inner_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->natural_inner_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::natural_left_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_left_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->natural_left_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::natural_right_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function natural_right_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->natural_right_join($table, $alias);

		return $this;
	}

	/**
	 * @param   Database_Query_Conditions
	 * @return  $this
	 */
	public function on($conditions)
	{
		$this->_parameters[':from']->on($conditions);

		return $this;
	}

	/**
	 * @uses Database_Query_From::right_join()
	 *
	 * @param   $table  mixed   Converted to Database_Table
	 * @param   $alias  string  Table alias
	 * @return  $this
	 */
	public function right_join($table, $alias = NULL)
	{
		$this->_parameters[':from']->right_join($table, $alias);

		return $this;
	}

	/**
	 * @uses Database_Query_From::using()
	 *
	 * @param   $columns    array
	 * @return  $this
	 */
	public function using(array $columns)
	{
		$this->_parameters[':from']->using($columns);

		return $this;
	}

	/**
	 * @param   Database_Query_Conditions
	 * @return  $this
	 */
	public function where($conditions)
	{
		$this->_parameters[':where'] = $conditions;

		return $this;
	}
}
