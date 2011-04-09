<?php

/**
 * [SQL Server](http://www.microsoft.com/sqlserver/) connection and expression
 * factory using PDO.
 *
 * *[PDO]: PHP Data Objects
 *
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://sqlsrvphp.codeplex.com/ Microsoft SQL Server Driver for PHP
 * @link http://msdn.microsoft.com/en-us/library/ff928321.aspx SQL Server Driver for PHP Documentation
 */
class Database_PDO_SQLServer extends Database_PDO
{
	/**
	 * Create a column expression.
	 *
	 * @param   mixed   $name   Converted to SQL_Column
	 * @param   mixed   $type   Converted to SQL_Expression
	 * @return  Database_SQLServer_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_SQLServer_DDL_Column($name, $type);
	}

	/**
	 * Create a DELETE statement.
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @return  Database_SQLServer_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_SQLServer_Delete($table, $alias);
	}

	/**
	 * Create an INSERT statement.
	 *
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $columns    Each element converted to SQL_Column
	 * @return  Database_SQLServer_Insert
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_SQLServer_Insert($table, $columns);
	}

	/**
	 * Create a SELECT statement.
	 *
	 * @param   mixed   $columns    Hash of (alias => column) pairs
	 * @return  Database_SQLServer_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_SQLServer_Select($columns);
	}

	/**
	 * Create an UPDATE statement.
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @param   string  $alias  Table alias
	 * @param   array   $values Hash of (column => value) assignments
	 * @return  Database_SQLServer_Update
	 */
	public static function update($table = NULL, $alias = NULL, $values = NULL)
	{
		return new Database_SQLServer_Update($table, $alias, $values);
	}

	protected $_quote_left = '[';

	protected $_quote_right = ']';

	/**
	 * Create a PDO connection for SQL Server
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | integer | [Encoding Constant](http://msdn.microsoft.com/en-US/library/cc296183.aspx)
	 *  profiling             | boolean | Enable execution profiling
	 *  table_prefix          | string  | Table prefix
	 *  connection.dsn        | string  | Full DSN or a predefined DSN name
	 *  connection.options    | array   | PDO options
	 *  connection.password   | string  |
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.uri        | string  | URI to a file containing the DSN
	 *  connection.username   | string  |
	 *
	 * *[DSN]: Data Source Name
	 * *[URI]: Uniform Resource Identifier
	 *
	 * @link http://msdn.microsoft.com/en-US/library/ff628159.aspx PDO connection parameters
	 *
	 * @param   string  $name   Connection name
	 * @param   array   $config Configuration
	 */
	public function __construct($name, $config)
	{
		parent::__construct($name, $config);

		// Execute queries directly by default
		$this->_config['connection']['options'][PDO::SQLSRV_ATTR_DIRECT_QUERY]
			= TRUE;

		if ( ! empty($this->_config['charset']))
		{
			// Set the configured encoding
			$this->_config['connection']['options'][PDO::SQLSRV_ATTR_ENCODING]
				= $this->_config['charset'];
		}
	}

	/**
	 * Set the connection encoding.
	 *
	 * @link http://msdn.microsoft.com/en-US/library/cc296183.aspx
	 *
	 * @throws  Database_Exception
	 * @param   integer $encoding   Encoding constant
	 * @return  void
	 */
	public function charset($encoding)
	{
		$this->_connection or $this->connect();

		$this->_connection->setAttribute(PDO::SQLSRV_ATTR_ENCODING, $encoding);
	}

	public function connect()
	{
		try
		{
			$this->_connection = new PDO(
				$this->_config['connection']['dsn'],
				$this->_config['connection']['username'],
				$this->_config['connection']['password'],
				$this->_config['connection']['options']
			);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(
				':error',
				array(':error' => $e->getMessage()),
				$e->getCode()
			);
		}
	}

	public function datatype($type, $attribute = NULL)
	{
		static $types = array
		(
			// SQL Server >= 2005
			'bit'               => array('type' => 'boolean'),
			'datetime'          => array('type' => 'datetime'),
			'image'             => array('type' => 'binary'),
			'money'             => array('type' => 'float', 'exact' => TRUE, 'min' => '-922337203685477.5808', 'max' => '922337203685477.5807'),
			'ntext'             => array('type' => 'string'),
			'nvarchar'          => array('type' => 'string'),
			'smalldatetime'     => array('type' => 'datetime'),
			'smallmoney'        => array('type' => 'float', 'exact' => TRUE, 'min' => '-214748.3648', 'max' => '214748.3647'),
			'sql_variant'       => array('type' => 'mixed'),
			'text'              => array('type' => 'string'),
			'timestamp'         => array('type' => 'integer'),
			'tinyint'           => array('type' => 'integer', 'min' => '0', 'max' => '255'),
			'uniqueidentifier'  => array('type' => 'string'),
			'xml'               => array('type' => 'string'),

			// SQL Server >= 2008
			'datetime2'         => array('type' => 'datetime'),
			'datetimeoffset'    => array('type' => 'datetime'),
			'geography'         => array('type' => 'string'),
			'geometry'          => array('type' => 'string'),
			'hierarchyid'       => array('type' => 'string'),
			'rowversion'        => array('type' => 'integer'),
		);

		if ( ! isset($types[$type]))
			return parent::datatype($type, $attribute);

		if ($attribute !== NULL)
			return @$types[$type][$attribute];

		return $types[$type];
	}

	public function prepare($statement, $options = array())
	{
		// Disable direct execution while respecting all options
		$options = array(PDO::SQLSRV_ATTR_DIRECT_QUERY => FALSE) + $options;

		return parent::prepare($statement, $options);
	}
}
