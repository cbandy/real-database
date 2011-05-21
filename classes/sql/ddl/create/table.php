<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createtable.html PostgreSQL
 * @link http://www.sqlite.org/lang_createtable.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms174979.aspx Transact-SQL
 */
class SQL_DDL_Create_Table extends SQL_Expression
{
	/**
	 * @var boolean
	 */
	protected $_temporary;

	/**
	 * @uses SQL_DDL_Create_Table::name()
	 *
	 * @param   mixed   $name   Converted to SQL_Table
	 */
	public function __construct($name = NULL)
	{
		parent::__construct('');

		$this->name($name);
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->_temporary)
		{
			// Not allowed in MSSQL
			$value .= ' TEMPORARY';
		}

		$value .= ' TABLE :name';

		if ( ! empty($this->parameters[':query']))
		{
			if ( ! empty($this->parameters[':columns']))
			{
				$value .= ' (:columns)';
			}

			// Not allowed in PostgreSQL
			// Not allowed in MSSQL
			$value .= ' AS (:query)';
		}
		else
		{
			$value .= ' (:columns';

			if ( ! empty($this->parameters[':constraints']))
			{
				$value .= ', :constraints';
			}

			$value .= ')';
		}

		return $value;
	}

	/**
	 * Append a column definition
	 *
	 * @param   SQL_DDL_Column  $column
	 * @return  $this
	 */
	public function column($column)
	{
		if ($column === NULL)
		{
			$this->parameters[':columns'] = array();
		}
		else
		{
			$this->parameters[':columns'][] = $column;
		}

		return $this;
	}

	/**
	 * Append a table constraint
	 *
	 * @param   SQL_DDL_Constraint  $constraint
	 * @return  $this
	 */
	public function constraint($constraint)
	{
		if ($constraint === NULL)
		{
			$this->parameters[':constraints'] = array();
		}
		else
		{
			$this->parameters[':constraints'][] = $constraint;
		}

		return $this;
	}

	/**
	 * Set the name of the table
	 *
	 * @param   mixed   $value  Converted to SQL_Table
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof SQL_Expression
			AND ! $value instanceof SQL_Identifier)
		{
			$value = new SQL_Table($value);
		}

		$this->parameters[':name'] = $value;

		return $this;
	}

	/**
	 * Set the query from which the table definition is inferred
	 *
	 * @param   SQL_Expression  $query
	 * @return  $this
	 */
	public function query($query)
	{
		$this->parameters[':query'] = $query;

		return $this;
	}

	/**
	 * Set whether or not the table should be dropped at the end of the session
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function temporary($value = TRUE)
	{
		$this->_temporary = $value;

		return $this;
	}
}
