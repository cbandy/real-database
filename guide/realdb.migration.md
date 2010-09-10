
# Migration


## Quoting

The `Database::quote()` and `Database::quote_identifier()` methods have been split into the more
specific methods `quote_literal()`, `quote_column()` and `quote_identifier()`.
[Database::quote_column] adds the table_prefix when necessary, while [Database::quote_identifier]
does not.


## Execution

The `Database::query()` method has been split into two methods, `execute_command` and
`execute_query`. Use [Database::execute_query] to retrieve a result set, from a SELECT statement for
example, and use [Database::execute_command] for other statements.

To retrieve the last inserted ID, build an INSERT statement and set the name of the column
containing the ID:

    list($count, $id) = $db
        ->insert()
        ->into('things')
        ->columns(array('name', 'value'))
        ->values(array('a', 'b'))
        ->identity('id')
        ->execute($db);


## Introspection

The `Database::list_columns()` method has become an optional method (of [Database_iIntrospect])
named `table_columns()`. It now returns data closely resembling that of the standardized
INFORMATION_SCHEMA.

To get information about a column's corresponding PHP type or system limits, use
[Database::datatype]:

    $columns = $db->table_columns('things');

    foreach ($columns as & $column)
    {
        // Identical to Kohana 3.0.x
        $column = array_merge($db->datatype($column['data_type']), $column);
    }
