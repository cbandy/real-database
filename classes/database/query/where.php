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
	public function __construct($value, array $parameters = NULL)
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
	 * @param   mixed   Converted to Database_Table
	 * @param   string
	 * @return  $this
	 */
	public function join($table, $alias = NULL)
	{
		$this->_parameters[':from']->join($table, $alias);

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
	 * @param   Database_Query_Conditions
	 * @return  $this
	 */
	public function where($conditions)
	{
		$this->_parameters[':where'] = $conditions;

		return $this;
	}
}