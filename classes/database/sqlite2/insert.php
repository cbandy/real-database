<?php

/**
 * @package SQLite
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_insert.html
 */
class Database_SQLite2_Insert extends Database_Command_Insert_Identity
{
	/**
	 * @param   Database_SQLite2    $db
	 */
	public function compile($db)
	{
		if (empty($this->_parameters[':values'])
			OR ! is_array($this->_parameters[':values'])
			OR count($this->_parameters[':values']) === 1)
		{
			// Inserting defaults, expression, or single row
			return parent::compile($db);
		}

		// Build INSERT statement for each row
		$expression = new Database_Expression('INSERT INTO :table ', $this->_parameters[':values']);
		$expression->_parameters[':table'] = $this->_parameters[':table'];

		if ( ! empty($this->_parameters[':columns']))
		{
			$expression->_parameters[':columns'] = $this->_parameters[':columns'];
			$expression->_value .= '(:columns) ';
		}

		$expression->_value = str_repeat($expression->_value.'VALUES ?;', count($this->_parameters[':values']));

		return $expression->compile($db);
	}
}
