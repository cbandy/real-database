<?php

/**
 * UPDATE statement for PostgreSQL.
 *
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
class Database_PostgreSQL_DML_Update extends Database_DML_Update
{
	public function __toString()
	{
		if ( ! isset($this->parameters[':limit']))
			return parent::__toString();

		$value = 'UPDATE :table SET :values WHERE ctid IN (SELECT ctid FROM :table';

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

	public function from($reference, $table_alias = NULL)
	{
		if ($reference AND isset($this->parameters[':limit']))
			throw new Kohana_Exception(
				'PostgreSQL UPDATE does not support LIMIT with FROM'
			);

		return parent::from($reference, $table_alias);
	}

	public function limit($count)
	{
		if ($count !== NULL AND ! empty($this->parameters[':from']))
			throw new Kohana_Exception(
				'PostgreSQL UPDATE does not support LIMIT with FROM'
			);

		return parent::limit($count);
	}
}
