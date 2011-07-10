<?php

/**
 * UPDATE statement for SQL Server.
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/library/ms177523.aspx
 */
class Database_SQLServer_Update extends Database_Update
{
	public function __toString()
	{
		$value = 'UPDATE';

		if (isset($this->parameters[':limit']))
		{
			$value .= ' TOP (:limit)';
		}

		$value .= ' :table SET :values';

		if ( ! empty($this->parameters[':returning']))
		{
			$value .= ' OUTPUT :returning';
		}

		if ( ! empty($this->parameters[':from']))
		{
			$value .= ' FROM :from';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}
}
