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
	 * @param   $reference  Database_Query_From
	 * @return  $this
	 */
	public function from($reference)
	{
		$this->_parameters[':from'] = $reference;

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
