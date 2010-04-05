<?php

/**
 * @package PostgreSQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-insert.html
 */
class Database_PostgreSQL_Insert extends Database_Command_Insert_Identity
{
	protected $_as_object = FALSE;

	protected function _build()
	{
		$value = parent::_build();

		if ( ! empty($this->_parameters[':returning']))
		{
			$value .= ' RETURNING :returning';
		}

		return $value;
	}

	/**
	 * Return results as associative arrays when executed
	 *
	 * @return  $this
	 */
	public function as_assoc()
	{
		$this->_as_object = FALSE;

		return $this;
	}

	/**
	 * Return results as objects when executed
	 *
	 * @param   mixed   $class  Class to return or TRUE for stdClass
	 * @return  $this
	 */
	public function as_object($class = TRUE)
	{
		$this->_as_object = $class;

		return $this;
	}

	/**
	 * @param   Database_PostgreSQL $db
	 * @return  integer         Number of affected rows
	 * @return  array           List including number of affected rows and identity of first row
	 * @return  Database_Result Result set
	 */
	public function execute($db)
	{
		if (empty($this->_parameters[':returning']))
			return parent::execute($db);

		$result = $db->execute_query($this->compile($db), $this->_as_object);

		if (empty($this->_return))
			return $result;

		$rows = $result->count();

		if ($this->_return instanceof Database_Identifier)
		{
			$result = $result->get($this->_return->name);
		}
		else
		{
			// Guess that reset() will give us the singular value
			$result = $result->current();
			$result = reset($result);
		}

		return array($rows, $result);
	}

	/**
	 * @param   mixed   $column Converted to Database_Column
	 * @return  $this
	 */
	public function identity($column)
	{
		parent::identity($column);

		if (empty($this->_return))
		{
			unset($this->_parameters[':returning']);
		}
		else
		{
			$this->param(':returning', $this->_return);
		}

		return $this;
	}

	/**
	 * Return values when executed
	 *
	 * @param   mixed   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function returning($columns)
	{
		$this->_return = NULL;

		if ($columns === NULL)
		{
			unset($this->_parameters[':returning']);
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

				$this->_parameters[':returning'][] = $column;
			}
		}
		else
		{
			$this->param(':returning', $columns);
		}

		return $this;
	}
}
