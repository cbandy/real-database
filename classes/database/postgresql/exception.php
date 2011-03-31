<?php

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Exceptions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PostgreSQL_Exception extends Database_Exception
{
	/**
	 * Creates an exception with a detailed error code from a result resource.
	 *
	 * Frees the resource.
	 *
	 * @param   resource    $result From pg_get_result()
	 */
	public function __construct($result)
	{
		parent::__construct(
			':error',
			array(':error' => pg_result_error($result)),
			pg_result_error_field($result, PGSQL_DIAG_SQLSTATE)
		);

		pg_free_result($result);
	}
}
