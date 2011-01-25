<?php

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Statement Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_PostgreSQL_iReturning extends Database_iQuery
{
	/**
	 * Append values to return when executed.
	 *
	 * @param   mixed   $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function returning($columns);
}
