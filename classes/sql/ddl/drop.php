<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/sql-syntax-data-definition.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-commands.html PostgreSQL
 * @link http://www.sqlite.org/lang.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/cc879259.aspx Transact-SQL
 */
class SQL_DDL_Drop extends SQL_Expression
{
	/**
	 * @var boolean Whether or not dependent objects should be dropped
	 */
	protected $_cascade;

	/**
	 * @var boolean Whether or not an error should be suppressed if the object does not exist
	 */
	protected $_if_exists;

	/**
	 * @uses SQL_DDL_Drop::name()
	 * @uses SQL_DDL_Drop::cascade()
	 *
	 * @param   string  $type       INDEX, SCHEMA, VIEW, etc.
	 * @param   mixed   $name       Converted to SQL_Identifier
	 * @param   boolean $cascade    Whether or not dependent objects should be dropped
	 */
	public function __construct($type, $name = NULL, $cascade = NULL)
	{
		parent::__construct('DROP '.strtoupper($type));

		$this->name($name);
		$this->cascade($cascade);
	}

	public function __toString()
	{
		$value = $this->_value;

		if ($this->_if_exists)
		{
			// Not allowed in MSSQL
			$value .= ' IF EXISTS';
		}

		$value .= ' :names';

		if (isset($this->_cascade))
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			// Not allowed in SQLite
			$value .= $this->_cascade ? ' CASCADE' : ' RESTRICT';
		}

		return $value;
	}

	/**
	 * Set whether or not dependent objects should be dropped
	 *
	 * [!!] Not supported by MySQL, SQLite or SQL Server
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function cascade($value = TRUE)
	{
		$this->_cascade = $value;

		return $this;
	}

	/**
	 * Set whether or not an error should be suppressed if the object does not
	 * exist
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function if_exists($value = TRUE)
	{
		$this->_if_exists = $value;

		return $this;
	}

	/**
	 * Append the name of an object to be dropped.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Identifier or NULL to reset
	 * @return  $this
	 */
	public function name($name)
	{
		if ($name === NULL)
		{
			$this->parameters[':names'] = array();
		}
		else
		{
			if ( ! $name instanceof SQL_Expression
				AND ! $name instanceof SQL_Identifier)
			{
				$name = new SQL_Identifier($name);
			}

			$this->parameters[':names'][] = $name;
		}

		return $this;
	}

	/**
	 * Append the names of multiple objects to be dropped.
	 *
	 * @param   array   $names  List of names converted to SQL_Identifier or NULL to reset
	 * @return  $this
	 */
	public function names($names)
	{
		if ($names === NULL)
		{
			$this->parameters[':names'] = array();
		}
		else
		{
			// SQLite allows only one
			foreach ($names as $name)
			{
				if ( ! $name instanceof SQL_Expression
					AND ! $name instanceof SQL_Identifier)
				{
					$name = new SQL_Identifier($name);
				}

				$this->parameters[':names'][] = $name;
			}
		}

		return $this;
	}
}
