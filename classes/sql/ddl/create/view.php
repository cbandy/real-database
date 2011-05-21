<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-view.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createview.html PostgreSQL
 * @link http://www.sqlite.org/lang_createview.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms187956.aspx Transact-SQL
 */
class SQL_DDL_Create_View extends SQL_Expression
{
	/**
	 * @var boolean Whether or not an existing view should be replaced
	 */
	protected $_replace;

	/**
	 * @var boolean Whether or not the view should be dropped at the end of the session
	 */
	protected $_temporary;

	/**
	 * @uses SQL_DDL_Create_View::name()
	 * @uses SQL_DDL_Create_View::query()
	 *
	 * @param   mixed           $name   Converted to SQL_Table
	 * @param   SQL_Expression  $query
	 */
	public function __construct($name = NULL, $query = NULL)
	{
		parent::__construct('');

		$this->name($name);
		$this->query($query);
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->_replace)
		{
			// Not allowed in MSSQL
			// Not allowed in SQLite
			$value .= ' OR REPLACE';
		}

		if ($this->_temporary)
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			$value .= ' TEMPORARY';
		}

		$value .= ' VIEW :name';

		if ( ! empty($this->parameters[':columns']))
		{
			// Not allowed in SQLite
			$value .= ' (:columns)';
		}

		$value .= ' AS :query';

		return $value;
	}

	/**
	 * Append one column or expression to be included in the view.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $column Converted to SQL_Column or NULL to reset
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
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}

			$this->parameters[':columns'][] = $column;
		}

		return $this;
	}

	/**
	 * Append columns and/or expressions to be included in the view.
	 *
	 * @param   array|SQL_Expression    $columns    List of columns converted to SQL_Column or NULL to reset
	 * @return  $this
	 */
	public function columns($columns)
	{
		if (is_array($columns))
		{
			foreach ($columns as $column)
			{
				if ( ! $column instanceof SQL_Expression
					AND ! $column instanceof SQL_Identifier)
				{
					$column = new SQL_Column($column);
				}

				$this->parameters[':columns'][] = $column;
			}
		}
		elseif ($columns === NULL)
		{
			$this->parameters[':columns'] = array();
		}
		else
		{
			$this->parameters[':columns'] = $columns;
		}

		return $this;
	}

	/**
	 * Set the name of the view
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
	 * Set the query which will provide the columns and rows of the view
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
	 * Set whether or not an existing view should be replaced
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function replace($value = TRUE)
	{
		$this->_replace = $value;

		return $this;
	}

	/**
	 * Set whether or not the view should be dropped at the end of the session
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
