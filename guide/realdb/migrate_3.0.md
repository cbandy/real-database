
# Migrating from Kohana 3.0


## Quoting

The `Database::quote` and `Database::quote_identifier` methods have been split into the more
specific methods [Database::quote_literal], [Database::quote_column] and
[Database::quote_identifier]. `quote_column` adds the table_prefix when necessary, while
`quote_identifier` does not.

`Database::escape` has become a method of [Database_iEscape] named
`escape_literal`.


## Execution

The `Database::query` method has been split into three methods, [Database::execute_command],
[Database::execute_insert] and [Database::execute_query]. Use `execute_query` to retrieve a result
set, from a SELECT statement for example, and use `execute_command` for other statements.

To retrieve the last inserted ID, pass the name of the column containing the ID
to [Database::execute_insert] or build an INSERT statement and set the name of
the column containing the ID:

    // Execute directly
    list($count, $id) = $db->execute_insert(
        'INSERT INTO '.$db->quote_table('things')
        .' ("name", "value")'
        ." VALUES ('a', 'b')",
        'id'
    );

    // Build an INSERT statement
    list($count, $id) = $db->execute(
        $db->insert()
            ->into('things')
            ->columns(array('name', 'value'))
            ->values(array('a', 'b'))
            ->identity('id')
    );


## Query Caching

`Database_Result_Cached` has been renamed to [Database_Result_Array].
`Database_Result::cached()` has been renamed to [Database_Result::serializable].

Use [Database_Query_Cached] to execute a query and get/set its result set in a
[Cache].


## Introspection

The `Database::list_columns` and `Database::list_tables` methods have become methods of
[Database_iIntrospect] named `table_columns` and `schema_tables`, respectively. They now return
data closely resembling that of the standardized INFORMATION_SCHEMA.

To get information about a column's corresponding PHP type or system limits, use
[Database::datatype]:

    $columns = $db->table_columns('things');

    foreach ($columns as & $column)
    {
        // Identical to Kohana 3.0.x
        $column = array_merge($db->datatype($column['data_type']), $column);
    }


## Counting

The `Database::count_records` and `Database::count_last_query` methods have been removed.

Here are two simple ways to retrieve the number of rows in a table:

    // Execute directly
    $rows = $db->execute_query(
        'SELECT COUNT(*) FROM '.$db->quote_table($table)
    )->get();

    // Build a SELECT query
    $rows = $db->execute(
        $db->select(SQL::expression('COUNT(*)'))->from($table)
    )->get();

To retrieve the number of rows a query would return without paging applied, reset the `limit` and
`offset` parameters of a SELECT query:

    $query = $db
        ->select()
        ->from($table)
        ->where($conditions)
        ->limit($number)
        ->offset($number * $page);

    $results = $db->execute($query);

    $total_rows = $db->execute(
        $query
            ->select(SQL::expression('COUNT(*)'))
            ->limit(NULL)
            ->offset(NULL)
    )->get();
