<?php

/**
 * INSERT statement for SQL Server.
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/library/ms174335.aspx
 */
class Database_SQLServer_Insert extends Database_Insert
{
	public function __toString()
	{
		$value = 'INSERT INTO :table ';

		if ( ! empty($this->parameters[':columns']))
		{
			$value .= '(:columns) ';
		}

		if ( ! empty($this->parameters[':returning']))
		{
			$value .= 'OUTPUT :returning ';
		}

		if (empty($this->parameters[':values']))
		{
			$value .= 'DEFAULT VALUES';
		}
		elseif (is_array($this->parameters[':values']))
		{
			$value .= 'VALUES :values';
		}
		else
		{
			$value .= ':values';
		}

		return $value;
	}
}
