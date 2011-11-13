<?php

/**
 * An INSERT statement which can return the identity of an inserted row when
 * executed. Alternatively, columns from the inserted rows can be returned as a
 * result set.
 *
 * @package     RealDatabase
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_DML_Insert extends SQL_DML_Insert
	implements Database_iInsert, Database_iReturning
{
	/**
	 * @var array   Arguments to pass to the class constructor
	 */
	public $arguments;

	/**
	 * @var string|boolean  Class as which to return row results, TRUE for stdClass or FALSE for associative array
	 */
	public $as_object = FALSE;

	/**
	 * @var SQL_Expression|SQL_Identifier   Column to return when executed
	 */
	public $identity;

	/**
	 * @var array   Columns to return from the inserted rows when executed
	 */
	public $returning;

	public function as_assoc()
	{
		return $this->as_object(FALSE);
	}

	public function as_object($class = TRUE, $arguments = array())
	{
		$this->as_object = $class;
		$this->arguments = $arguments;

		return $this;
	}

	public function identity($column)
	{
		if ($column)
		{
			$this->returning = NULL;

			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}
		}

		$this->identity = $column;

		return $this;
	}

	public function returning($columns)
	{
		parent::returning($columns);

		if ($columns === NULL)
		{
			$this->returning = NULL;
		}
		else
		{
			$this->identity = NULL;
			$this->returning = $this->parameters[':returning'];
		}

		return $this;
	}
}
