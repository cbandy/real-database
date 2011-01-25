<?php

/**
 * @package     RealDatabase
 * @category    Driver Interfaces
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Database_iIntrospect
{
	/**
	 * Retrieve the tables of a schema in a format almost identical to that of
	 * the Tables table of the SQL-92 Information Schema. Only returns tables
	 * having the `table_prefix` and removes `table_prefix` from the table names.
	 *
	 * For example, this schema has one table and one view:
	 *
	 *     array(
	 *       'customers' => array(
	 *         'table_name' => 'customers',
	 *         'table_type' => 'BASE TABLE',
	 *       ),
	 *       'accounts' => array(
	 *         'table_name' => 'accounts',
	 *         'table_type' => 'VIEW',
	 *       ),
	 *     );
	 *
	 * @param   mixed   $schema Converted to SQL_Identifier
	 * @return  array
	 */
	public function schema_tables($schema = NULL);

	/**
	 * Retrieve the columns of a table in a format almost identical to that of
	 * the Columns table of the SQL-92 Information Schema.
	 *
	 * For example, this table
	 *
	 *     CREATE TABLE t1 (
	 *       id     integer         PRIMARY KEY,
	 *       name   varchar(50)     NOT NULL,
	 *       score  numeric(7,2)    DEFAULT 0
	 *     )
	 *
	 * returns this array
	 *
	 *     array(
	 *       'id' => array(
	 *         'column_name'        => 'id',
	 *         'ordinal_position'   => 1,
	 *         'is_nullable'        => 'NO',
	 *         'data_type'          => 'integer',
	 *       ),
	 *       'name' => array(
	 *         'column_name'        => 'name',
	 *         'ordinal_position'   => 2,
	 *         'is_nullable'        => 'NO',
	 *         'data_type'          => 'varchar',
	 *         'character_maximum_length'   => 50,
	 *       ),
	 *       'score' => array(
	 *         'column_name'        => 'score',
	 *         'ordinal_position'   => 3,
	 *         'column_default'     => 0,
	 *         'is_nullable'        => 'YES',
	 *         'data_type'          => 'numeric',
	 *         'numeric_precision'  => 7,
	 *         'numeric_scale'      => 2,
	 *       ),
	 *     );
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @return  array
	 */
	public function table_columns($table);
}
