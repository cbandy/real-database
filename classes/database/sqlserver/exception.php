<?php

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Exceptions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLServer_Exception extends Database_Exception
{
	/**
	 * @var array
	 */
	protected $_errors;

	/**
	 * @param   string  $message
	 * @param   array   $variables
	 */
	public function __construct($message = '', $variables = NULL)
	{
		if ($this->_errors = sqlsrv_errors(SQLSRV_ERR_ERRORS))
		{
			parent::__construct(':error', array(':error' => $this->_errors[0]['message']), $this->_errors[0]['code']);
		}
		else
		{
			parent::__construct($message, $variables);
		}
	}

	/**
	 * @return  array|NULL
	 */
	public function errors()
	{
		return $this->_errors;
	}
}
