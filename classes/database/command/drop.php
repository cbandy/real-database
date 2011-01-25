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
class Database_Command_Drop extends Database_Command
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
	 * @uses Database_Command_Drop::name()
	 * @uses Database_Command_Drop::cascade()
	 *
	 * @param   string  $type       SCHEMA, TABLE, VIEW, etc
	 * @param   mixed   $name       Converted to SQL_Identifier
	 * @param   boolean $cascade    Whether or not dependent objects should be dropped
	 */
	public function __construct($type, $name = NULL, $cascade = NULL)
	{
		parent::__construct('DROP '.strtoupper($type));

		if ($name !== NULL)
		{
			$this->name($name);
		}

		if ($cascade !== NULL)
		{
			$this->cascade($cascade);
		}
	}

	public function __toString()
	{
		$value = $this->_value;

		if ( ! empty($this->_if_exists))
		{
			// Not allowed in MSSQL
			$value .= ' IF EXISTS';
		}

		$value .= ' :name';

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
	 * Set the name of the object to be dropped
	 *
	 * @param   mixed   $value  Converted to SQL_Identifier
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof SQL_Expression
			AND ! $value instanceof SQL_Identifier)
		{
			$value = new SQL_Identifier($value);
		}

		$this->parameters[':name'] = $value;

		return $this;
	}

	/**
	 * Set the names of multiple objects to be dropped
	 *
	 * @param   mixed   $values Each element converted to SQL_Identifier
	 * @return  $this
	 */
	public function names($values)
	{
		if (is_array($values))
		{
			// SQLite allows only one
			foreach ($values as & $value)
			{
				if ( ! $value instanceof SQL_Expression
					AND ! $value instanceof SQL_Identifier)
				{
					$value = new SQL_Identifier($value);
				}
			}
		}

		$this->parameters[':name'] = $values;

		return $this;
	}
}
