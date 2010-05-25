# Introduction

Library tries to make building SQL for one or more database systems as painless as possible for the
developer while still offering the flexibility to do very complex and powerful things.

Everything in PHP is a string and library uses context to interpret these strings as literal data,
identifiers and SQL. Developer can set this context explicitly by using objects.

Easy and contextual:

    join('table')->on('col1', '=', 'col2')

Explicit and powerful:

    join($query_object)->on($expression_object)


## Support

Each database system has different features of SQL, different data types and different supported
SQL syntaxes.

Four common systems targetted: MySQL, PostgreSQL, Microsoft SQL Server and SQLite. The generic
builders and driver interface support features implemented by two or more of these systems.

Library intends to support any system for which PHP has a driver.


## Identifiers

Identifiers are database things. Tables, Columns and Aliases. Specified as dot-delimited string or
array of parts.

    Database_Identifier('x.y.z') == Database_Identifier(array('x','y','z'))

When working with table aliases, you may want to create a Column that is not affected by the
`table_prefix`:

    Database_Column(array(Database_Identifier('t1'), 'column'))


## Expressions

Expressions are SQL. Can have a mix of positional and named parameters bound. Positional parameters
`?` and named parameters `:name`.

SQL queries are built by recursively nesting expressions, identifiers and literals.


## Execution

The most direct way to execute is to send raw SQL to [execute_command()][] and [execute_query()][].
Identifiers and literals can be quoted for raw SQL using [quote_identifier()][], [quote_table()][],
[quote_column()][] and [quote_literal()][].

Slightly more convenient is to use parameters with the [Command][] and [Query][] objects. These
have the added convenience of consistent caching and system-agnostic execution.

[execute_command()]:    api/Database#execute_command
[execute_query()]:      api/Database#execute_query

[quote_identifier()]:   api/Database#quote_identifier
[quote_table()]:        api/Database#quote_table
[quote_column()]:       api/Database#quote_column
[quote_literal()]:      api/Database#quote_literal

[Command]:  api/Database_Command
[Query]:    api/Database_Query


## Building

There are five builder classes for the four basic SQL DML statements:

 SQL    | Class
 ---    | -----
 DELETE | [Command_Delete][]
 INSERT | [Command_Insert][]
 UPDATE | [Command_Update][]
 SELECT | [Query_Select][] <br /> [Query_Set][]

*[DML]: Data Manipulation Language

[Command_Delete]:   api/Database_Command_Delete
[Command_Insert]:   api/Database_Command_Insert
[Command_Update]:   api/Database_Command_Update
[Query_Select]:     api/Database_Query_Select
[Query_Set]:        api/Database_Query_Set

These provide a convenient and powerful interface for building both simple and complex SQL queries.

    // Raw SQL and execute_query()
    $db->execute_query(
        'SELECT '.$db->quote_column('value')
        .' FROM '.$db->quote_table('things')
        .' WHERE '.$db->quote_column('name').' = '.$db->quote_literal('find'));

    // SELECT builder
    $db
        ->select(array('value'))
        ->from('things')
        ->where(new Database_Column('name'), '=', 'find')
        ->execute($db);
