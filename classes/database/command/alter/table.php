<?php

/**
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
 * @link http://msdn.microsoft.com/en-us/library/ms190273.aspx Transact-SQL
 */
class Database_Command_Alter_Table extends Database_Command
{
	/**
	 * @uses Database_Command_Alter_Table::name()
	 *
	 * @param   mixed   $name   Converted to Database_Table
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
	 * @param   Database_DDL_Column $column
	 * @return  $this
	 */
	public function add_column($column)
	{
		$this->parameters[':actions'][] = new Database_Expression('ADD ?', array($column));

		return $this;
	}

	/**
	 * Add a constraint to the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   Database_DDL_Constraint $constraint
	 * @return  $this
	 */
	public function add_constraint($constraint)
	{
		$this->parameters[':actions'][] = new Database_Expression('ADD ?', array($constraint));

		return $this;
	}

	/**
	 * Remove a column from the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   mixed   $name   Converted to Database_Column
	 * @return  $this
	 */
	public function drop_column($name)
	{
		if ( ! $name instanceof Database_Expression
			AND ! $name instanceof Database_Identifier)
		{
			$name = new Database_Column($name);
		}

		$this->parameters[':actions'][] = new Database_Expression('DROP COLUMN ?', array($name));

		return $this;
	}

	/**
	 * Remove a constraint from the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   string  $type   CHECK, FOREIGN, PRIMARY or UNIQUE
	 * @param   mixed   $name   Converted to Database_Identifier
	 * @return  $this
	 */
	public function drop_constraint($type, $name)
	{
		if ( ! $name instanceof Database_Expression
			AND ! $name instanceof Database_Identifier)
		{
			$name = new Database_Identifier($name);
		}

		$this->parameters[':actions'][] = new Database_Expression('DROP CONSTRAINT ?', array($name));

		return $this;
	}

	/**
	 * Remove the default value on a column.
	 *
	 * [!!] Not supported by SQLite or SQL Server
	 *
	 * @param   mixed   $name   Converted to Database_Column
	 * @return  $this
	 */
	public function drop_default($name)
	{
		if ( ! $name instanceof Database_Expression
			AND ! $name instanceof Database_Identifier)
		{
			$name = new Database_Column($name);
		}

		$this->parameters[':actions'][] = new Database_Expression('ALTER ? DROP DEFAULT', array($name));

		return $this;
	}

	/**
	 * Set the name of the table to be altered.
	 *
	 * @param   mixed   $value  Converted to Database_Table
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof Database_Expression
			AND ! $value instanceof Database_Identifier)
		{
			$value = new Database_Table($value);
		}

		$this->parameters[':name'] = $value;

		return $this;
	}

	/**
	 * Rename the table. This cannot be combined with other actions.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   mixed   $name   Converted to Database_Table
	 * @return  $this
	 */
	public function rename($name)
	{
		if ( ! $name instanceof Database_Expression
			AND ! $name instanceof Database_Identifier)
		{
			$name = new Database_Table($name);
		}

		$this->parameters[':actions'] = array(new Database_Expression('RENAME TO ?', array($name)));

		return $this;
	}

	/**
	 * Set the default value of a column.
	 *
	 * [!!] Not supported by SQLite or SQL Server
	 *
	 * @param   mixed   $name   Converted to Database_Column
	 * @param   mixed   $value
	 * @return  $this
	 */
	public function set_default($name, $value)
	{
		if ( ! $name instanceof Database_Expression
			AND ! $name instanceof Database_Identifier)
		{
			$name = new Database_Column($name);
		}

		$this->parameters[':actions'][] = new Database_Expression('ALTER ? SET DEFAULT ?', array($name, $value));

		return $this;
	}
}
