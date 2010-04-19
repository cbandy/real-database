<?php

/**
 * @package PDO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_Command extends Database_Prepared_Command
{
	/**
	 * @param   Database_PDO    $db
	 * @param   PDOStatement    $statement
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

		return $this;
	}

	public function execute()
	{
		try
		{
			$this->_value->execute();
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		return $this->_value->rowCount();
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

		return $this;
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
