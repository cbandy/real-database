
/**
 * Schema required for SQL Server tests.
 *
 * Change this to include the table_prefix, if desired.
 *
 * @package      RealDatabase
 * @subpackage   Microsoft SQL Server
 */

CREATE TABLE kohana_test_table (
    id bigint IDENTITY PRIMARY KEY,
    value integer
);
