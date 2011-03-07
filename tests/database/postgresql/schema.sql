
/**
 * Schema required for PostgreSQL tests.
 *
 * Change this to include the table_prefix, if desired.
 *
 * @package      RealDatabase
 * @subpackage   PostgreSQL
 */

CREATE TABLE kohana_test_table (
    id bigserial PRIMARY KEY,
    value integer
);
