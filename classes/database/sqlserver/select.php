<?php

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/en-us/library/ms189499.aspx
 */
class Database_SQLServer_Select extends Database_Query_Select
{
	public function __toString()
	{
		$value = 'SELECT';

		if ( ! empty($this->parameters[':distinct']))
		{
			$value .= ' :distinct';
		}

		if (isset($this->parameters[':limit']))
		{
			$value .= ' TOP (:limit)';
		}

		$value .= ' :columns';

		if ( ! empty($this->parameters[':from']))
		{
			$value .= ' FROM :from';
		}

		if ( ! empty($this->parameters[':where']))
		{
			$value .= ' WHERE :where';
		}

		if ( ! empty($this->parameters[':groupby']))
		{
			$value .= ' GROUP BY :groupby';
		}

		if ( ! empty($this->parameters[':having']))
		{
			$value .= ' HAVING :having';
		}

		if ( ! empty($this->parameters[':orderby']))
		{
			$value .= ' ORDER BY :orderby';
		}

		return $value;
	}

	/**
	 * No-op because SQL Server does not support OFFSET
	 *
	 * @todo Possible using window functions, but requires unique row identifier
	 *
	 * @throws  Kohana_Exception
	 */
	public function offset($start)
	{
		throw new Kohana_Exception('SQL Server does not support OFFSET');
	}
}
