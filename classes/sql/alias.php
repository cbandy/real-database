<?php

/**
 * Expression for appending an alias to any value.
 *
 * @package     RealDatabase
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class SQL_Alias extends SQL_Expression
{
	/**
	 * @param   mixed                                       $value
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 */
	public function __construct($value, $alias)
	{
		if ( ! $alias instanceof SQL_Expression
			AND ! $alias instanceof SQL_Identifier)
		{
			$alias = new SQL_Identifier($alias);
		}

		parent::__construct('? AS ?', array($value, $alias));
	}
}
