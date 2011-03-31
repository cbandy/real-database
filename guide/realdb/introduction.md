
# Introduction

The Database library tries to make building SQL for one or more database systems as painless as
possible for the developer while still offering the flexibility to do very complex and powerful
things.

The library uses context to conveniently interpret strings as either identifiers, raw SQL or
literal data, though the developer can be explicit by using objects.

Easy and contextual:

    join('table')->on('col1', '=', 'col2')

Explicit and powerful:

    join($query_object)->on($expression_object)


## Support

Each database system has different features, different data types and different supported SQL
syntaxes.

Four common systems targeted: MySQL, PostgreSQL, Microsoft SQL Server and SQLite. The [generic
builders](realdb.building) and driver interface support features implemented by two or more of
these systems.

The Database library intends to support any SQL system for which PHP has a driver.


## Identifiers

Identifiers are the unique names which refer to tables, columns, indexes, aliases, etc. Each can be
specified as a dot-delimited string or an array of parts.

    new SQL_Identifier('x.y.z') == new SQL_Identifier(array('x','y','z'))

When working with table aliases, you may want to create a Column that is not affected by the
`table_prefix`:

    new SQL_Column(array(new SQL_Identifier('t1'), 'column'))


## Expressions

Expressions are portions of a SQL statement that are sent to the database without being modified.
Every expression can have parameters which will be quoted during execution.

Positional parameters are marked with a `?` while named parameters begin with a colon, e.g. `:name`.
One expression can have a mix of both positional and named parameters.

SQL queries are built by recursively nesting expressions, identifiers and literals.


## Execution

The most direct way to execute is to send raw SQL to [Database::execute_command] and
[Database::execute_query]. Identifiers and literals can be quoted for raw SQL using
[Database::quote_identifier], [Database::quote_table], [Database::quote_column] and
[Database::quote_literal].

    // Raw SQL and execute_query()
    $db->execute_query(
        'SELECT '.$db->quote_column('value')
        .' FROM '.$db->quote_table('things')
        .' WHERE '.$db->quote_column('name').' = '.$db->quote_literal('find'));

Slightly more convenient is to use parameters with the [SQL_Expression] and [Database_Query]
objects. These have the added convenience of consistent caching and system-agnostic execution.

    // SQL with parameters
    $db->execute($db->query(
        'SELECT ? FROM ? WHERE ? = ?',
        array(
            $db->column('value'),
            $db->table('things'),
            $db->column('name'),
            'find',
        )
    ));


## Results

[Database_Result] provides a consistent interface to read and iterate over fresh and cached rows.
The simplest and most typical use is in a `foreach`:

    foreach ($query->execute($db) as $row)
    {
        $library->do_something($row['id'], $row['name']);
    }

The `get()` method provides an optimized way to fetch a single column.

    $count = $db->execute_query(
        'SELECT COUNT(*) FROM '.$db->quote_table('things')
    )->get();
