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
class Database_Query_Select extends Database_Query_Having
{
	public function __construct($columns = NULL)
	{
		parent::__construct('');

		$this->select($columns);
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

	public function compile(Database $db)
	{
		$this->_value = 'SELECT';

		if ( ! empty($this->_parameters[':distinct']))
		{
			$this->_value .= ' :distinct';
		}

		$this->_value .= ' :columns';

		if ( ! empty($this->_parameters[':from']))
		{
			$this->_value .= ' FROM :from';
		}

		if ( ! empty($this->_parameters[':where']))
		{
			$this->_value .= ' WHERE :where';
		}

		if ( ! empty($this->_parameters[':groupby']))
		{
			$this->_value .= ' GROUP BY :groupby';
		}

		if ( ! empty($this->_parameters[':having']))
		{
			$this->_value .= ' HAVING :having';
		}

		if ( ! empty($this->_parameters[':orderby']))
		{
			$this->_value .= ' ORDER BY :orderby';
		}

		if (isset($this->_parameters[':limit']))
		{
			$this->_value .= ' LIMIT :limit';
		}

		if ( ! empty($this->_parameters[':offset']))
		{
			$this->_value .= ' OFFSET :offset';
		}

		return parent::compile($db);
	}
}
