
/**
 * Schema required for MySQL tests.
 *
 * Change this to include the table_prefix, if desired.
 *
 * @package      RealDatabase
 * @subpackage   MySQL
 */

CREATE TABLE kohana_test_table (
    id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
    value integer
)

-- Required for transaction support
ENGINE = InnoDB;
