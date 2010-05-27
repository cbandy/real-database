<?php

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-update.html
 */
class Database_PostgreSQL_Update extends Database_Command_Update
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
	 * Execute the UPDATE on a Database. Returns a result set when returning()
	 * is set.
	 *
	 * @param   Database_PostgreSQL $db Connection on which to execute
	 * @return  integer                     Number of affected rows
	 * @return  Database_PostgreSQL_Result  Result set
	 */
	public function execute($db)
	{
		if (empty($this->parameters[':returning']))
			return parent::execute($db);

		return $db->execute_query($db->quote($this), $this->_as_object);
	}

	/**
	 * Append values to return when executed
	 *
	 * @param   mixed   $columns    Each element converted to Database_Column
	 * @return  $this
	 */
	public function returning($columns)
	{
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
