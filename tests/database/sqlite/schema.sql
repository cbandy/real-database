
/**
 * Schema required for SQLite tests.
 *
 * Change this to include the table_prefix, if desired.
 *
 * @package      RealDatabase
 * @subpackage   SQLite
 */

CREATE TABLE kohana_test_table (
    id integer NOT NULL PRIMARY KEY,
    value integer
);
