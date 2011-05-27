
# Query Building

There are five builder classes providing a [fluent interface][] for the four basic SQL DML
statements:

 SQL    | Class
 ---    | -----
 DELETE | [Database_Delete]
 INSERT | [Database_Insert]
 UPDATE | [Database_Update]
 SELECT | [Database_Select] <br /> [Database_Query_Set]

*[DML]: Data Manipulation Language
[Fluent Interface]: http://martinfowler.com/bliki/FluentInterface.html


## Conditions

[Database_Delete], [Database_Update] and [Database_Select] filter the rows they
affect by some criteria, and these criteria are built using the [SQL_Conditions]
class.

    // "id" = 10
    new SQL_Conditions(new SQL_Column('id'), '=', 10);

    // "id" = 20
    SQL::conditions()->column(NULL, 'id', '=', 20);

It is possible to nest criteria and force operator precedence using the parentheses methods.

    // "value" = 5
    //   AND (
    //     "name" = 'effect'
    //       OR (
    //         "name" IS NULL
    //           AND "updated" BETWEEN '2000-01-01' AND '2001-01-01'
    //       )
    //   )
    SQL::conditions()
        ->column(NULL, 'value', '=', 5)
        ->and_open_column('name', '=', 'effect')
            ->or_open($db->conditions()
                ->column(NULL, 'name', 'is', NULL)
                ->and_column('updated', 'between', array('2000-01-01', '2001-01-01')))
            ->close()
        ->close();


## Table References

[Database_Select] can return rows from multiple tables by combining them into what is called a
table reference. Similar to criteria, these tables can be joined in a myriad of ways using the
[SQL_Table_Reference] class.

    // Straightforward JOIN
    // "things" JOIN "sprockets" ON ("sprockets"."thing_id" = "things"."id")
    SQL::reference('things')
        ->join('sprockets')->on('sprockets.thing_id', '=', 'things.id');

    // Cartesian product
    // "things", "sprockets"
    SQL::reference('things')->add('sprockets');

    // Multiple JOINs
    SQL::reference('classes')
        ->join('enrollments')->on('enrollments.class_id', '=', 'classes.id')
        ->join('students')->on('student.id', '=', 'enrollments.student_id')
        ->left_join('grades')->on('grades.enrollment_id', '=', 'enrollments.id');


## Commands

[Database_Delete] and [Database_Update], respectively, remove and modify rows from a
table which match some criteria. When executed, these will return the number of rows they affected.

    $rows = $db->execute(
        $db->delete('things')->where('id', '=', 10)
    );

    $rows = $db->execute(
        $db->update('things')->value('name', 'effect')->where('id', '=', 20)
    );

[Database_Insert] adds one or more rows to a table. In addition to returning the number of
rows added, it can also return the primary key, or identity, of one of the rows.

    $rows = $db->execute(
        $db->insert('things')
            ->columns(array('name', 'value'))
            ->values(array('effect', 5))
    );

    list($rows, $id) = $db->execute(
        $db->insert('things')
            ->columns(array('name', 'value'))
            ->values(array('effect', 8))
            ->identity('id')
    );

[!!] For some systems, fetching the identity requires extra processing.


## Queries

SELECT queries are the most frequently used statements and the most complex. [Database_Select]
combines a table reference with search criteria while sorting and paging results. Basic searches are
straightforward:

    $db->select(array('id', 'name', 'value'))
        ->from('things')
        ->where('name', '=', 'effect');

More complex searches use the [SQL_Conditions] and [SQL_Table_Reference] classes:

    $db->select(array('things.name', 'things.value', 'sprockets.price'))
        ->from(
            SQL::reference('things')
            ->join('sprockets')->on('sprockets.thing_id', '=', 'things.id')
        )
        ->where(SQL::conditions()
            ->column(NULL, 'things.value', '=', 5)
            ->or_column('sprockets.price', 'between', array(15, 25))
        );

Sorting can be done with Expressions or Columns:

    // ORDER BY "name" DESC
    $select->order_by('name', 'DESC');

    // ORDER BY "value" ASC
    $select->order_by(new SQL_Column('value'), 'ASC');

    // ORDER BY RAND()
    $select->order_by(new SQL_Expression('RAND()'));

Paging is accomplished with `limit()` and `offset()`:

    // Retrieve 50 rows starting from the 100th
    $db->select(array('*'))
        ->from('things')
        ->offset(100)
        ->limit(50);

Queries can be combined using the `except()`, `intersect()` and `union()` methods of the
[Database_Query_Set] class:

    // SELECT * FROM "things" WHERE "name" = 'effect'
    //   UNION
    // SELECT * FROM "others" WHERE "name" = 'reversed'
    $db->query_set(
        $db->select(array('*'))->from('things')->where('name', '=', 'effect')
    )->union(
        $db->select(array('*'))->from('others')->where('name', '=', 'reversed')
    );

When executed, all queries will return a [Database_Result] object.
