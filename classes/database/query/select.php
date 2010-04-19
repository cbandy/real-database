<?php

/**
 * @package RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/select.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-select.html PostgreSQL
 * @link http://www.sqlite.org/lang_select.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms189499.aspx Transact-SQL
 */
class Database_Query_Select extends Database_Query
{
	public function __construct($columns = NULL)
	{
		parent::__construct('');

		$this->select($columns);
	}

	protected function _build()
	{
		$value = 'SELECT';

		if ( ! empty($this->_parameters[':distinct']))
		{
			$value .= ' :distinct';
		}

		$value .= ' :columns';

		if ( ! empty($this->_parameters[':from']))
		{
			$value .= ' FROM :from';
		}

		if ( ! empty($this->_parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		if ( ! empty($this->_parameters[':groupby']))
		{
			$value .= ' GROUP BY :groupby';
		}

		if ( ! empty($this->_parameters[':having']))
		{
			$value .= ' HAVING :having';
		}

		if ( ! empty($this->_parameters[':orderby']))
		{
			$value .= ' ORDER BY :orderby';
		}

		if (isset($this->_parameters[':limit']))
		{
			// Not allowed in MSSQL
			$value .= ' LIMIT :limit';
		}

		if ( ! empty($this->_parameters[':offset']))
		{
			// LIMIT required by MySQL and SQLite
			// Not allowed in MSSQL
			$value .= ' OFFSET :offset';
		}

		return $value;
	}

	public function compile($db)
	{
		$this->_value = $this->_build();

		return parent::compile($db);
	}

	/**
	 * @param   mixed   Converted to Database_Column
	 * @param   string  Column alias
	 * @return  $this
	 */
	public function column($column, $alias = NULL)
	{
		if ( ! $column instanceof Database_Expression
			AND ! $column instanceof Database_Identifier)
		{
			$column = new Database_Column($column);
		}

		if ($alias)
		{
			$column = new Database_Expression('? AS ?', array($column, new Database_Identifier($alias)));
		}

		$this->_parameters[':columns'][] = $column;

		return $this;
	}

	/**
	 * @param   boolean
	 * @return  $this
	 */
	public function distinct($value = TRUE)
	{
		return $this->param(':distinct', $value ? new Database_Expression('DISTINCT') : FALSE);
	}

	/**
	 * @param   mixed   $reference      Database_From or converted to Database_Table
	 * @param   string  $table_alias    Table alias when converting to Database_Table
	 * @return  $this
	 */
	public function from($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof Database_From)
		{
			$reference = new Database_From($reference, $table_alias);
		}

		return $this->param(':from', $reference);
	}

	/**
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function group_by(array $columns)
	{
		foreach ($columns as &$column)
		{
			if ( ! $column instanceof Database_Expression
				AND ! $column instanceof Database_Identifier)
			{
				$column = new Database_Column($column);
			}
		}

		return $this->param(':groupby', $columns);
	}

	/**
	 * @param   Database_Conditions $conditions
	 * @return  $this
	 */
	public function having($conditions)
	{
		return $this->param(':having', $conditions);
	}

	/**
	 * Set the maximum number of rows
	 *
	 * @param   integer $count  Number of rows
	 * @return  $this
	 */
	public function limit($count)
	{
		return $this->param(':limit', $count);
	}

	/**
	 * Set the number of rows to skip
	 *
	 * @param   integer $start  Number of rows
	 * @return  $this
	 */
	public function offset($start)
	{
		return $this->param(':offset', $start);
	}

	/**
	 * @param   mixed   Converted to Database_Column
	 * @param   mixed
	 * @return  $this
	 */
	public function order_by($column, $direction = NULL)
	{
		if ( ! $column instanceof Database_Expression
			AND ! $column instanceof Database_Identifier)
		{
			$column = new Database_Column($column);
		}

		if ($direction)
		{
			if ( ! $direction instanceof Database_Expression)
			{
				$direction = new Database_Expression(strtoupper($direction));
			}

			$column = new Database_Expression('? ?', array($column, $direction));
		}

		$this->_parameters[':orderby'][] = $column;

		return $this;
	}

	/**
	 * @param   mixed
	 * @return  $this
	 */
	public function select($columns)
	{
		if ($columns === NULL)
		{
			$this->param(':columns', array());
		}
		elseif (is_array($columns))
		{
			foreach ($columns as $alias => $column)
			{
				if ( ! $column instanceof Database_Expression
					AND ! $column instanceof Database_Identifier)
				{
					$column = new Database_Column($column);
				}

				if (is_string($alias) AND $alias !== '')
				{
					$column = new Database_Expression('? AS ?', array($column, new Database_Identifier($alias)));
				}

				$this->_parameters[':columns'][] = $column;
			}
		}
		else
		{
			$this->param(':columns', $columns);
		}

		return $this;
	}

	/**
	 * @param   Database_Conditions $conditions
	 * @return  $this
	 */
	public function where($conditions)
	{
		return $this->param(':where', $conditions);
	}
}
