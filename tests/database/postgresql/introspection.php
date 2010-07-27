<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 * @group   database.postgresql.introspection
 */
class Database_PostgresSQL_Introspection_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	protected $_information_schema_defaults = array
	(
		'column_name'       => NULL,
		'ordinal_position'  => NULL,
		'column_default'    => NULL,
		'is_nullable'       => NULL,
		'data_type'         => NULL,
		'character_maximum_length'  => NULL,
		'numeric_precision' => NULL,
		'numeric_scale'     => NULL,
		'datetime_precision' => NULL,
	);

	public function setUp()
	{
		$config = Kohana::config('database')->testing;

		if ($config['type'] !== 'PostgreSQL')
			$this->markTestSkipped('Database not configured for PostgreSQL');

		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');
	}

	public function tearDown()
	{
		$this->_db->execute_command('DROP TABLE IF EXISTS '.$this->_table);

		$this->_db->disconnect();
	}

	public function provider_table_column_type()
	{
		return array
		(
			// Binary

			array('bytea', array(
				'data_type' => 'bytea',
			)),

			// Bit

			array('bit(20)', array(
				'data_type' => 'bit',
				'character_maximum_length' => 20,
			)),
			array('varbit(10)', array(
				'data_type' => 'bit varying',
				'character_maximum_length' => 10,
			)),
			array('varbit', array(
				'data_type' => 'bit varying',
			)),

			// Boolean

			array('boolean', array(
				'data_type' => 'boolean',
			)),

			// Character

			array('char(30)', array(
				'data_type' => 'character',
				'character_maximum_length' => 30,
			)),
			array('varchar(40)', array(
				'data_type' => 'character varying',
				'character_maximum_length' => 40,
			)),
			array('varchar', array(
				'data_type' => 'character varying',
			)),

			array('text', array(
				'data_type' => 'text',
			)),

			// Date and Time

			array('date', array(
				'data_type' => 'date',
				'datetime_precision' => 0,
			)),
			array('interval(5)', array(
				'data_type' => 'interval',
				'datetime_precision' => 5,
			)),
			array('interval', array(
				'data_type' => 'interval',
				'datetime_precision' => 6,
			)),
			array('time(3)', array(
				'data_type' => 'time without time zone',
				'datetime_precision' => 3,
			)),
			array('time', array(
				'data_type' => 'time without time zone',
				'datetime_precision' => 6,
			)),
			array('time with time zone', array(
				'data_type' => 'time with time zone',
				'datetime_precision' => 6,
			)),
			array('timestamp(2)', array(
				'data_type' => 'timestamp without time zone',
				'datetime_precision' => 2,
			)),
			array('timestamp', array(
				'data_type' => 'timestamp without time zone',
				'datetime_precision' => 6,
			)),
			array('timestamp with time zone', array(
				'data_type' => 'timestamp with time zone',
				'datetime_precision' => 6,
			)),

			// Floating Point

			array('double precision', array(
				'data_type' => 'double precision',
				'numeric_precision' => 53,
			)),
			array('real', array(
				'data_type' => 'real',
				'numeric_precision' => 24,
			)),

			// Geometry

			array('box', array(
				'data_type' => 'box',
			)),
			array('circle', array(
				'data_type' => 'circle',
			)),
			array('line', array(
				'data_type' => 'line',
			)),
			array('lseg', array(
				'data_type' => 'lseg',
			)),
			array('path', array(
				'data_type' => 'path',
			)),
			array('point', array(
				'data_type' => 'point',
			)),
			array('polygon', array(
				'data_type' => 'polygon',
			)),

			// Integer

			array('integer', array(
				'data_type' => 'integer',
				'numeric_precision' => 32,
				'numeric_scale' => 0,
			)),
			array('smallint', array(
				'data_type' => 'smallint',
				'numeric_precision' => 16,
				'numeric_scale' => 0,
			)),
			array('bigint', array(
				'data_type' => 'bigint',
				'numeric_precision' => 64,
				'numeric_scale' => 0,
			)),

			// Network

			array('cidr', array(
				'data_type' => 'cidr',
			)),
			array('inet', array(
				'data_type' => 'inet',
			)),
			array('macaddr', array(
				'data_type' => 'macaddr',
			)),

			// Numeric

			array('numeric(13,7)', array(
				'data_type' => 'numeric',
				'numeric_precision' => 13,
				'numeric_scale' => 7,
			)),
			array('numeric(5)', array(
				'data_type' => 'numeric',
				'numeric_precision' => 5,
				'numeric_scale' => 0,
			)),
			array('numeric', array(
				'data_type' => 'numeric',
			)),

			// Text Search

			array('tsquery', array(
				'data_type' => 'tsquery',
			)),
			array('tsvector', array(
				'data_type' => 'tsvector',
			)),

			// Miscellaneous

			array('money', array(
				'data_type' => 'money',
			)),
			array('txid_snapshot', array(
				'data_type' => 'txid_snapshot',
			)),
			array('uuid', array(
				'data_type' => 'uuid',
			)),
			array('xml', array(
				'data_type' => 'xml',
			)),
		);
	}

	/**
	 * @dataProvider provider_table_column_type
	 */
	public function test_table_column_type($column, $expected)
	{
		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'ordinal_position' => 1,
			'is_nullable' => 'YES',
		), $expected);

		$this->_db->execute_command('CREATE TABLE '.$this->_table." ( field $column )");

		$result = $this->_db->table_columns('temp_test_table');

		$this->assertEquals($expected, $result['field']);
	}
}
