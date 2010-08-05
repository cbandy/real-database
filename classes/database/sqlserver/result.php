<?php

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLServer_Result extends Database_Result
{
	/**
	 * @var resource    From sqlsrv_prepare() or sqlsrv_query()
	 */
	protected $_statement;

	/**
	 * @param   resource    $statement  From sqlsrv_prepare() or sqlsrv_query()
	 * @param   mixed       $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 */
	public function __construct($statement, $as_object)
	{
		parent::__construct($statement, $as_object);

		if ($as_object === TRUE)
		{
			$this->_as_object = 'stdClass';
		}

		$this->_count = sqlsrv_num_rows($statement);
		$this->_statement = $statement;
	}

	public function current()
	{
		if ($this->_as_object)
			return sqlsrv_fetch_object($this->_statement, $this->_as_object, NULL, SQLSRV_SCROLL_ABSOLUTE, $this->_position);

		return sqlsrv_fetch_array($this->_statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE, $this->_position);
	}

	public function get($name = NULL, $default = NULL)
	{
		if ($this->_as_object OR $name !== NULL)
			return parent::get($name, $default);

		if ($this->valid())
		{
			sqlsrv_fetch($this->_statement, SQLSRV_SCROLL_ABSOLUTE, $this->_position);

			if (($result = sqlsrv_get_field($this->_statement, 0)) !== NULL)
				return $result;
		}

		return $default;
	}
}
