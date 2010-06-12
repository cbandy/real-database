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
	 * Execute the INSERT using a syntax compatible with PostgreSQL versions
	 * prior to 8.2
	 *
	 * Pre-fetches the identity of the first row when possible. Reads the value
	 * of the identity sequence after execution as a fallback.
	 *
	 * @link http://www.postgresql.org/docs/8.1/static/sql-insert.html
	 *
	 * @throws  Database_Exception
	 * @param   Database_PostgreSQL $db Connection on which to execute
	 * @return  integer                     Number of affected rows
	 * @return  array                       List including number of affected rows and an identity value
	 * @return  Database_PostgreSQL_Result  Result set
	 */
	protected function _execute_81($db)
	{
		if (empty($this->_return))
		{
			if (empty($this->parameters[':values'])
				OR ! is_array($this->parameters[':values'])
				OR count($this->parameters[':values']) === 1)
			{
				// Default values, an expression or a single row
				return $db->execute_command($db->quote_expression($this));
			}

			// Build an INSERT statement for each row
			$expression = new Database_Expression('INSERT INTO :table', $this->parameters[':values']);
			$expression->parameters[':table'] = $this->parameters[':table'];

			if ( ! empty($this->parameters[':columns']))
			{
				$expression->_value .= ' (:columns)';
				$expression->parameters[':columns'] = $this->parameters[':columns'];
			}

			$expression->_value .= ' VALUES ?';

			if ( ! empty($this->parameters[':returning']))
			{
				// Not supported prior to PostgreSQL 8.2
				$expression->_value .= ' RETURNING :returning';
				$expression->parameters[':returning'] = $this->parameters[':returning'];
			}

			$expression->_value = str_repeat($expression->_value.';', count($this->parameters[':values']));

			return $db->execute_command($db->quote_expression($expression));
		}

		if ($this->_return instanceof Database_Expression)
			throw new Database_Exception('PostgreSQL versions prior to 8.2 do not support retrieving an identity Expression');

		if (empty($this->parameters[':columns']))
			throw new Database_Exception('PostgreSQL versions prior to 8.2 cannot return a reliable identifier without an explicit column list');

		if ( ! $this->parameters[':table'] instanceof Database_Identifier OR ! $this->_return instanceof Database_Identifier)
			throw new Database_Exception('PostgreSQL versions prior to 8.2 cannot return a reliable identifier without a clear table and identity column');

		if (($index = array_search($this->_return, $this->parameters[':columns'])) === FALSE)
		{
			// Identity not assigned in values

			if (empty($this->parameters[':values'])
				OR ! is_array($this->parameters[':values']))
			{
				// Default values or an expression

				// Execute the INSERT without a RETURNING clause
				$rows = $db->execute_command($db->quote_expression(new Database_Expression(parent::__toString(), $this->parameters)));

				// Retrieve the last ID
				$result = $this->_sequence_value($db, 'currval');
			}
			else
			{
				// Retrieve the next ID
				$result = $this->_sequence_value($db, 'nextval');

				// Set the ID of the first row
				$row = reset($this->parameters[':values']);
				$row = $row->parameters;
				$row[] = $result;
				$values[] = new Database_Expression('(?, ?)', $row);

				// Generate the remaining IDs on the server
				while ($row = next($this->parameters[':values']))
				{
					$values[] = new Database_Expression('(?, DEFAULT)', $row->parameters);
				}

				// Build an INSERT statement for each row
				$expression = new Database_Expression(str_repeat('INSERT INTO :table (:columns) VALUES ?;', count($values)), $values);
				$expression->parameters[':table'] = $this->parameters[':table'];
				$expression->parameters[':columns'] = $this->parameters[':columns'];

				// Append the identity column
				$expression->parameters[':columns'][] = $this->_return;

				$rows = $db->execute_command($db->quote_expression($expression));
			}
		}
		else
		{
			// Values of the first row
			$row = reset($this->parameters[':values']);
			$row = reset($row->parameters);

			if ( ! $row[$index] instanceof Database_Expression OR $row[$index]->_value !== 'DEFAULT')
			{
				// Convert the assigned identity value
				$result = $db->execute_query('SELECT '.$db->quote($row[$index]))->get();
			}
			else
			{
				// Retrieve the next ID
				$result = $this->_sequence_value($db, 'nextval');

				$assign = TRUE;
			}
		}

		if ( ! isset($rows))
		{
			// Build an INSERT statement for each row
			$expression = new Database_Expression(str_repeat('INSERT INTO :table (:columns) VALUES ?;', count($this->parameters[':values'])), $this->parameters[':values']);
			$expression->parameters[':table'] = $this->parameters[':table'];
			$expression->parameters[':columns'] = $this->parameters[':columns'];

			if (isset($assign))
			{
				// Set the ID of the first row
				$row = reset($expression->parameters);
				$expression->parameters[key($expression->parameters)]->parameters[key($row->parameters)][$index] = $result;
			}

			$rows = $db->execute_command($db->quote_expression($expression));
		}

		return array($rows, $result);
	}

	/**
	 * Fetch the current or next value of the identity sequence
	 *
	 * @throws  Database_Exception
	 * @param   Database_PostgreSQL $db     Connection on which to execute
	 * @param   string              $method 'currval' or 'nextval'
	 * @return  integer
	 */
	protected function _sequence_value($db, $method)
	{
		$sequence = new Database_Identifier(array($db->table_prefix().$this->parameters[':table']->name.'_'.$this->_return->name.'_seq'));
		$sequence->namespace = $this->parameters[':table']->namespace;

		return (int) $db->execute_query("SELECT $method(".$db->quote_literal($db->quote_identifier($sequence)).')')->get();
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
		if ($db->version() < '8.2')
			return $this->_execute_81($db);

		if (empty($this->parameters[':returning']))
			return parent::execute($db);

		$result = $db->execute_query($db->quote($this), $this->_as_object);

		if (empty($this->_return))
			return $result;

		$rows = $result->count();
		$result = $result->get($this->_return instanceof Database_Identifier ? $this->_return->name : NULL);

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
