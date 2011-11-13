<?php

/**
 * Generic ALTER TABLE statement. Some drivers do not support some features.
 *
 * @package     RealDatabase
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/alter-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-altertable.html PostgreSQL
 * @link http://www.sqlite.org/lang_altertable.html SQLite
 * @link http://msdn.microsoft.com/library/ms190273.aspx Transact-SQL
 */
class SQL_DDL_Alter_Table extends SQL_Expression
{
	/**
	 * @uses SQL_DDL_Alter_Table::name()
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 */
	public function __construct($name = NULL)
	{
		parent::__construct('ALTER TABLE :name :actions');

		if ($name !== NULL)
		{
			$this->name($name);
		}
	}

	/**
	 * Add a column to the table.
	 *
	 * @param   SQL_DDL_Column  $column
	 * @return  $this
	 */
	public function add_column($column)
	{
		$this->parameters[':actions'][] = new SQL_Expression(
			'ADD ?', array($column)
		);

		return $this;
	}

	/**
	 * Add a constraint to the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   SQL_DDL_Constraint  $constraint
	 * @return  $this
	 */
	public function add_constraint($constraint)
	{
		$this->parameters[':actions'][] = new SQL_Expression(
			'ADD ?', array($constraint)
		);

		return $this;
	}

	/**
	 * Remove a column from the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @return  $this
	 */
	public function drop_column($name)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->parameters[':actions'][] = new SQL_Expression(
			'DROP COLUMN ?', array($name)
		);

		return $this;
	}

	/**
	 * Remove a constraint from the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   string                                      $type   CHECK, FOREIGN, PRIMARY or UNIQUE
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Identifier
	 * @return  $this
	 */
	public function drop_constraint($type, $name)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Identifier($name);
		}

		$this->parameters[':actions'][] = new SQL_Expression(
			'DROP CONSTRAINT ?', array($name)
		);

		return $this;
	}

	/**
	 * Remove the default value on a column.
	 *
	 * [!!] Not supported by SQLite or SQL Server
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @return  $this
	 */
	public function drop_default($name)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->parameters[':actions'][] = new SQL_Expression(
			'ALTER ? DROP DEFAULT', array($name)
		);

		return $this;
	}

	/**
	 * Set the name of the table to be altered.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $value  Converted to SQL_Table
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
	 * Rename the table. This cannot be combined with other actions.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @return  $this
	 */
	public function rename($name)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Table($name);
		}

		$this->parameters[':actions'] = array(
			new SQL_Expression('RENAME TO ?', array($name))
		);

		return $this;
	}

	/**
	 * Set the default value of a column.
	 *
	 * [!!] Not supported by SQLite or SQL Server
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @param   mixed                                       $value
	 * @return  $this
	 */
	public function set_default($name, $value)
	{
		if ( ! $name instanceof SQL_Expression
			AND ! $name instanceof SQL_Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->parameters[':actions'][] = new SQL_Expression(
			'ALTER ? SET DEFAULT ?', array($name, $value)
		);

		return $this;
	}
}
