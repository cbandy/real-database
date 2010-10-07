<?php

/**
 * INSERT statement for PostgreSQL. Allows a result set from the inserted rows to be returned.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-insert.html
 */
class Database_PostgreSQL_Insert extends Database_Command_Insert_Identity
{
	/**
	 * @var mixed   Type as which to return results
	 */
	protected $_as_object = FALSE;

	public function __toString()
	{
		$value = parent::__toString();

		if ( ! empty($this->parameters[':returning']))
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
	 * Execute the INSERT on a Database. Returns an array when identity() is
	 * set. Returns a result set when returning() is set.
	 *
	 * @throws  Database_Exception
	 * @param   Database_PostgreSQL $db Connection on which to execute
	 * @return  integer                     Number of affected rows
	 * @return  array                       List including number of affected rows and identity of first row
	 * @return  Database_PostgreSQL_Result  Result set
	 */
	public function execute($db)
	{
		if (empty($this->parameters[':returning']))
			return parent::execute($db);

		$result = $db->execute_query($db->quote($this), $this->_as_object);

		if (empty($this->_return))
			return $result;

		$rows = $result->count();
		$result = $result->get(($this->_return instanceof Database_Identifier) ? $this->_return->name : NULL);

		return array($rows, $result);
	}

	/**
	 * Set the name of the column to return from the first row when executed
	 *
	 * @param   mixed   $column Converted to Database_Column
	 * @return  $this
	 */
	public function identity($column)
	{
		parent::identity($column);

		if (empty($this->_return))
		{
			unset($this->parameters[':returning']);
		}
		else
		{
			$this->parameters[':returning'] = $this->_return;
		}

		return $this;
	}

	/**
	 * Append values to return when executed
	 *
	 * @param   mixed   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function returning($columns)
	{
		$this->_return = NULL;

		if (is_array($columns))
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

				$this->parameters[':returning'][] = $column;
			}
		}
		elseif ($columns === NULL)
		{
			unset($this->parameters[':returning']);
		}
		else
		{
			$this->parameters[':returning'] = $columns;
		}

		return $this;
	}
}
