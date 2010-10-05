<?php

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLite_Insert extends Database_Command_Insert_Identity
{
	/**
	 * @var integer Number of rows to be inserted
	 */
	protected $_values = 0;

	public function __toString()
	{
		if ( ! $this->_values OR ! empty($this->parameters[':values']))
			return parent::__toString();

		$value = 'INSERT INTO :table ';

		if ( ! empty($this->parameters[':columns']))
		{
			$value .= '(:columns) ';
		}

		$value .= 'VALUES ?;';

		return str_repeat($value, $this->_values);
	}

	public function values($values)
	{
		if (is_array($values))
		{
			unset($this->parameters[':values']);

			foreach (func_get_args() as $row)
			{
				// Wrap each row in parentheses
				$this->parameters[$this->_values++] = new Database_Expression('(?)', array($row));
			}
		}
		elseif ($values === NULL)
		{
			unset($this->parameters[':values']);

			while ($this->_values)
			{
				unset($this->parameters[--$this->_values]);
			}
		}
		else
		{
			$this->parameters[':values'] = $values;
		}

		return $this;
	}
}
