<?php

/**
 * Prepared query for [Database_PDO].
 *
 * @package     RealDatabase
 * @subpackage  PDO
 * @category    Prepared Statements
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_Query extends Database_Prepared_Query
{
	/**
	 * @uses Database_PDO_Query::parameters()
	 *
	 * @param   Database_PDO    $db
	 * @param   PDOStatement    $statement  Prepared statement
	 * @param   array           $parameters Unquoted parameters
	 */
	public function __construct($db, $statement, $parameters = array())
	{
		parent::__construct($db, $statement, array());

		$this->parameters($parameters);
	}

	public function __toString()
	{
		return $this->_value->queryString;
	}

	public function bind($param, & $var)
	{
		if (is_string($var))
		{
			$this->_value->bindParam($param, $var, PDO::PARAM_STR);
		}
		elseif (is_int($var))
		{
			$this->_value->bindParam($param, $var, PDO::PARAM_INT);
		}
		elseif (is_bool($var))
		{
			$this->_value->bindParam($param, $var, PDO::PARAM_BOOL);
		}
		else
		{
			$this->_value->bindParam($param, $var);
		}

		return parent::bind($param, $var);
	}

	public function execute()
	{
		if (empty($this->_value->queryString))
			return NULL;

		if ($this->_db->profiling())
		{
			$benchmark = Profiler::start("Database ($this->_db)", 'Prepared: '.$this->_value->queryString);
		}

		try
		{
			$this->_value->execute();
		}
		catch (PDOException $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		if ($this->_value->columnCount() === 0)
			return NULL;

		return new Database_PDO_Result($this->_value, $this->as_object);
	}

	public function param($param, $value)
	{
		if (is_string($value))
		{
			$this->_value->bindValue($param, $value, PDO::PARAM_STR);
		}
		elseif (is_int($value))
		{
			$this->_value->bindValue($param, $value, PDO::PARAM_INT);
		}
		elseif (is_bool($value))
		{
			$this->_value->bindValue($param, $value, PDO::PARAM_BOOL);
		}
		else
		{
			$this->_value->bindValue($param, $value);
		}

		return parent::param($param, $value);
	}

	public function parameters(array $params)
	{
		foreach ($params as $param => $value)
		{
			$this->param($param, $value);
		}

		return $this;
	}
}
