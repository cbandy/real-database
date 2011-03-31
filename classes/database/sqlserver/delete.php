<?php

/**
 * DELETE statement for SQL Server.
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/en-us/library/ms189835.aspx
 */
class Database_SQLServer_Delete extends Database_Delete
{
	public function __toString()
	{
		$value = 'DELETE';

		if (isset($this->parameters[':limit']))
		{
			$value .= ' TOP (:limit)';
		}

		$value .= ' FROM :table';

		if ( ! empty($this->parameters[':returning']))
		{
			$value .= ' OUTPUT :returning';
		}

		if ( ! empty($this->parameters[':using']))
		{
			$value .= ' FROM :using';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}
}
