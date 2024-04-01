<?php

namespace BookneticApp\Providers\DB;

use BookneticApp\Models\Data;
use BookneticApp\Providers\DB\Collection;
use BookneticApp\Providers\DB\DataService;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\DB\QueryBuilder;

/**
 * Class Model
 * @package BookneticApp\Providers
 * @method Collection|static get( $id = null )
 * @method Collection insert( $data )
 * @method Collection update( $data )
 * @method Collection delete()
 * @method self|static where( $field, $valueOrSymbol = false, $value2 = false, $combinator = 'AND' )
 * @method self|static orWhere( $field, $valueOrSymbol = false, $value2 = false )
 * @method self|static whereId( $value )
 * @method self|static whereIsNull( $field )
 * @method self|static whereFindInSet( $field, $value, $combinator = 'AND' )
 * @method self|static orWhereFindInSet( $field, $value )
 * @method self|static like( $field, $value )
 * @method int count()
 * @method int sum( $column )
 * @method self|static orderBy( $arr )
 * @method self|static groupBy( $arr )
 * @method self|static limit( $limit )
 * @method self|static offset( $offset )
 * @method self|static select( $arr, $unselect_old_fields = false )
 * @method self|static selectSubQuery( QueryBuilder $subQuery, $alias )
 * @method Collection|static withoutGlobalScope( $scopeName )
 * @method Collection|static fetch()
 * @method Collection[]|static[] fetchAll()
 * @method string toSql()
 * @method self|static leftJoin( $joinTo, $select_fields, $field1 = null, $field2 = null, $unselect_fields = false )
 * @method self|static rightJoin( $joinTo, $select_fields, $field1 = null, $field2 = null, $unselect_fields = false )
 * @method self|static innerJoin( $joinTo, $select_fields, $field1 = null, $field2 = null, $unselect_fields = false )
 * @method self|static noTenant()
 * @method self|static withTranslations
 */
class Model
{

	use DataService;

	/**
	 * Table ID field name
	 *
	 * @var string
	 */
	protected static $idField = 'id';

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected static $tableName;

	/**
	 * Models' relationsips...
	 * @var array
	 */
	public static $relations = [];

	public static $scopes = [];

	private static $alreadyBooted = [];

	private static $triggers = [];

	private $QBInstance;

	/**
	 * Create QueryBuilder isntance...
	 *
	 * @param $name
	 * @param $arguments
	 * @return QueryBuilder|mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		if( $name === 'booted' )
			return;

		$qb = new QueryBuilder( static::class );

		if( is_callable( [ $qb, $name ] ) )
		{
			return call_user_func_array( [$qb, $name], $arguments );
		}

		return $qb;
	}

	/**
	 * Create QueryBuilder isntance...
	 *
	 * @param $name
	 * @param $arguments
	 * @return QueryBuilder|mixed
	 */
	public function __call($name, $arguments)
	{
		$qb = $this->getQBInstance();

		if( method_exists( $qb, $name ) )
		{
			return call_user_func_array( [$qb, $name], $arguments );
		}

		return $qb;
	}

	private function getQBInstance()
	{
		if( is_null( $this->QBInstance ) )
		{
			$this->QBInstance = new QueryBuilder( static::class );
		}

		return $this->QBInstance;
	}

	public static function boot( $builder )
	{
		$model = static::class;

		if( ! in_array( $model, self::$alreadyBooted ) && is_callable( [ $model, 'booted' ] ) )
		{
			self::$alreadyBooted[] = $model;

			call_user_func( [ $model, 'booted' ], $builder );
		}
	}

	public static function addGlobalScope( $scope, $closure )
	{
		if( is_callable( $closure ) )
		{
			self::$scopes[ static::class ][ $scope ] = $closure;
		}
	}

	public static function getGlobalScopes()
	{
		return isset( self::$scopes[ static::class ] ) ? self::$scopes[ static::class ] : [];
	}

	public static function onRetrieving( $closure )
	{
		self::$triggers[ static::class ][ 'retrieving' ][] = $closure;
	}

	public static function onRetrieved( $closure )
	{
		self::$triggers[ static::class ][ 'retrieved' ][] = $closure;
	}

	public static function onDeleting( $closure )
	{
		self::$triggers[ static::class ][ 'deleting' ][] = $closure;
	}

	public static function onDeleted( $closure )
	{
		self::$triggers[ static::class ][ 'deleted' ][] = $closure;
	}

	public static function onUpdating( $closure )
	{
		self::$triggers[ static::class ][ 'updating' ][] = $closure;
	}

	public static function onUpdated( $closure )
	{
		self::$triggers[ static::class ][ 'updated' ][] = $closure;
	}

	public static function onCreating( $closure )
	{
		self::$triggers[ static::class ][ 'creating' ][] = $closure;
	}

	public static function onCreated( $closure )
	{
		self::$triggers[ static::class ][ 'created' ][] = $closure;
	}

	public static function trigger()
	{
		$arguments  = func_get_args();
		$on         = array_shift( $arguments );
		$model      = static::class;
		$result     = true;

		if( isset( self::$triggers[ $model ][ $on ] ) )
		{
			foreach ( self::$triggers[ $model ][ $on ] AS $closure )
			{
				if( call_user_func_array( $closure, $arguments ) === false )
				{
					$result = false;
				}
			}
		}

		return $result;
	}

	/**
	 * Get table name from Model name
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		if( !is_null( static::$tableName ) )
			return static::$tableName;

		$modelName = basename( str_replace('\\', '/', get_called_class()) );

		$tableName = strtolower( preg_replace('/([A-Z])/', '_$1', $modelName) ) . 's';
		return ltrim($tableName, '_');
	}

	public static function lastId()
	{
		return DB::lastInsertedId();
	}

	/**
	 * Get ID field name
	 *
	 * @return string
	 */
	public static function getIdField()
	{
		return static::$idField;
	}

    public static function getField ( $fieldName )
    {
        return DB::table( self::getTableName() ) . '.' . $fieldName;
    }

	public static function getData ( $id, $key, $default = NULL )
	{
		return self::_getData( self::getTableName(), $id, $key, $default );
	}

	public static function setData ( $id, $key, $value, $updateIfExists = true )
	{
		return self::_setData( self::getTableName(), $id, $key, $value, $updateIfExists );
	}

	public static function deleteData ( $id = null, $key = null, $value = null )
	{
		return self::_deleteData( self::getTableName(), $id, $key, $value );
	}

}
