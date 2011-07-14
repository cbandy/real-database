<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createtable.html PostgreSQL
 * @link http://www.sqlite.org/syntaxdiagrams.html#column-def SQLite
 * @link http://msdn.microsoft.com/library/ms174979.aspx Transact-SQL
 */
class SQL_DDL_Column extends SQL_Expression
{
	/**
	 * @var boolean
	 */
	protected $_not_null;

	/**
	 * @uses SQL_DDL_Column::name()
	 * @uses SQL_DDL_Column::type()
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @param   mixed                                       $type   Converted to SQL_Expression
	 */
	public function __construct($name = NULL, $type = NULL)
	{
		parent::__construct('');

		if ($name !== NULL)
		{
			$this->name($name);
		}

		if ($type !== NULL)
		{
			$this->type($type);
		}
	}

	public function __toString()
	{
		$value = ':name :type';

		if (array_key_exists(':default', $this->parameters))
		{
			$value .= ' DEFAULT :default';
		}

		if ($this->_not_null)
		{
			$value .= ' NOT NULL';
		}

		if ( ! empty($this->parameters[':constraints']))
		{
			$value .= ' :constraints';
		}

		return $value;
	}

	/**
	 * Set the name of the column.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $value  Converted to SQL_Column
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof SQL_Expression
			AND ! $value instanceof SQL_Identifier)
		{
			$value = new SQL_Column($value);
		}

		$this->parameters[':name'] = $value;

		return $this;
	}

	/**
	 * Unset the default value of the column
	 *
	 * @return  $this
	 */
	public function no_default()
	{
		unset($this->parameters[':default']);

		return $this;
	}

	/**
	 * Set whether or not NULL values are prohibited in the column
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function not_null($value = TRUE)
	{
		$this->_not_null = $value;

		return $this;
	}

	/**
	 * Set the default value of the column
	 *
	 * @param   mixed   $value
	 * @return  $this
	 */
	public function set_default($value)
	{
		$this->parameters[':default'] = $value;

		return $this;
	}

	/**
	 * Set the datatype of the column
	 *
	 * @param   mixed   $type   Converted to SQL_Expression
	 * @return  $this
	 */
	public function type($type)
	{
		if ( ! $type instanceof SQL_Expression)
		{
			$type = new SQL_Expression($type);
		}

		$this->parameters[':type'] = $type;

		return $this;
	}

	/**
	 * Append a constraint to the column
	 *
	 * @param   SQL_DDL_Constraint  $constraint
	 * @return  $this
	 */
	public function constraint($constraint)
	{
		if ($constraint === NULL)
		{
			$this->parameters[':constraints'] = NULL;
		}
		else
		{
			if (empty($this->parameters[':constraints']))
			{
				$this->parameters[':constraints'] = new SQL_Expression('?');
			}
			else
			{
				$this->parameters[':constraints']->_value .= ' ?';
			}

			$this->parameters[':constraints']->parameters[] = $constraint;
		}

		return $this;
	}
}
