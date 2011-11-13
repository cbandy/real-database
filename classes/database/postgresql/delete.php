<?php

/**
 * DELETE statement for PostgreSQL.
 *
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-delete.html
 */
class Database_PostgreSQL_Delete extends Database_DML_Delete
{
	public function __toString()
	{
		if ( ! isset($this->parameters[':limit']))
			return parent::__toString();

		$value = 'DELETE FROM :table WHERE ctid IN (SELECT ctid FROM :table';

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		$value .= ' LIMIT :limit)';

		if ( ! empty($this->parameters[':returning']))
		{
			$value .= ' RETURNING :returning';
		}

		return $value;
	}

	public function limit($count)
	{
		if ($count !== NULL and ! empty($this->parameters[':using']))
			throw new Kohana_Exception('PostgreSQL DELETE does not support LIMIT with USING');

		return parent::limit($count);
	}

	public function using($reference, $table_alias = NULL)
	{
		if ($reference AND isset($this->parameters[':limit']))
			throw new Kohana_Exception('PostgreSQL DELETE does not support LIMIT with USING');

		return parent::using($reference, $table_alias);
	}
}
