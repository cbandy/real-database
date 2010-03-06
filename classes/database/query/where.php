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
	/**
	 * @param   Database_Query_From $reference
	 * @return  $this
	 */
	public function from($reference)
	{
		return $this->param(':from', $reference);
	}

	/**
	 * @param   Database_Query_Conditions   $conditions
	 * @return  $this
	 */
	public function where($conditions)
	{
		return $this->param(':where', $conditions);
	}
}
