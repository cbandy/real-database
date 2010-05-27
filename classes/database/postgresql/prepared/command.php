<?php

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PostgreSQL_Prepared_Command extends Database_Prepared_Command
{
	/**
	 * @var string  Statement name
	 */
	protected $_name;

	/**
	 * @param   Database_PostgreSQL $db
	 * @param   string  $name       Statement name
	 * @param   mixed   $statement  SQL command
	 * @param   array   $parameters Unquoted parameters
	 */
	public function __construct($db, $name, $statement, $parameters = array())
	{
		parent::__construct($db, $statement, $parameters);

		$this->_name = $name;
	}

	/**
	 * Deallocate this this prepared statement
	 *
	 * If you do not explicitly deallocate a prepared statement, it is
	 * deallocated when the session ends.
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	public function deallocate()
	{
		$this->_db->execute_command('DEALLOCATE '.$this->_db->quote_identifier($this->_name));
	}

	public function execute()
	{
		return $this->_db->execute_prepared_command($this->_name, $this->parameters);
	}
}
