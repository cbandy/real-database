<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Database_Query_Having extends Database_Query_Where
{
	public function __construct($value, array $parameters = NULL)
	{
		parent::__construct($value, $parameters);

		$this->_reset_group_by()->_reset_having();
	}

	/**
	 * @return  $this
	 */
	protected function _reset_group_by()
	{
		return $this->param(':groupby', array());
	}

	/**
	 * @return  $this
	 */
	protected function _reset_having()
	{
		return $this->param(':having', new Database_Query_Conditions);
	}

	/**
	 * @param   array
	 * @return  $this
	 */
	public function group_by(array $columns)
	{
		foreach ($columns as &$column)
		{
			if ( ! $column instanceof Database_Expression
				AND ! $column instanceof Database_Identifier)
			{
				$column = new Database_Column($column);
			}
		}

		return $this->param(':groupby', $columns);
	}

	/**
	 * @param   Database_Query_Conditions
	 * @return  $this
	 */
	public function having($conditions)
	{
		$this->_parameters[':having'] = $conditions;

		return $this;
	}
}
