
# Migration


## Introspection

The `Database::list_fields()` method has become an optional method (of [Database_iIntrospect]) named
`table_columns()`. It now returns data closely resembling that of the standardized
INFORMATION_SCHEMA.

To get information about a column's corresponding PHP type or system limits, use
[Database::datatype]:

    $columns = $db->table_columns('things');

    foreach ($columns as & $column)
    {
        // Identical to Kohana 3.0.x
        $column = array_merge($db->datatype($column['data_type']), $column);
    }
