<?php

namespace BookneticApp\Providers\DB;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\DB\QueryBuilder;

class DB
{

	const PLUGIN_DB_PREFIX = 'bkntc_';

	public static function DB()
	{
		global $wpdb;

		return $wpdb;
	}

	public static function table( $table )
	{
        $table = is_subclass_of( $table, Model::class ) ? $table::getTableName() : $table;

		return self::DB()->base_prefix . self::PLUGIN_DB_PREFIX . $table;
	}

	public static function selectQuery( $table , $where = null, $orderBy = null, $columns = [], $offset = null, $limit = null, $groupBy = null, $joins = [] )
	{
		$whereStatement = '';
		$joinStatement = '';

		if( !empty($where) && is_array($where) )
		{
			$whereStatement = ' WHERE ' . DB::where( $where, ( empty( $joins ) ? '' : self::table( $table ) ) );
		}

		if( !empty( $joins ) && is_array( $joins ) )
		{
			foreach ( $joins AS $joinElement )
			{
				$joinTable = $joinElement[0];
				$joinConditions = $joinElement[1];
				$joinType = strtoupper( $joinElement[2] );

				$joinStatement .= " {$joinType} JOIN `".DB::table( $joinTable )."` ON " . DB::on( $joinConditions );
			}
		}

		$orderByQuery = '';
		if( !empty( $orderBy ) && is_array( $orderBy ) )
		{
			$orderByQuery = ' ORDER BY ' . implode( ', ', $orderBy );
		}

		$groupByQuery = '';
		if( !empty( $groupBy ) && is_array( $groupBy ) )
		{
			$groupByQuery = ' GROUP BY ' . implode( ', ', $groupBy );
		}

		$columns = empty($columns) ? '*' : implode(',', $columns);

		$limitOffset = '';
		if( !is_null( $limit ) && $limit > 0 )
		{
			$offset = is_null($offset) ? 0 : (int)$offset;
			$limit = (int)$limit;

			$limitOffset = " LIMIT {$offset}, {$limit}";
		}

		$queryString = "SELECT {$columns} FROM " . self::table($table) . $joinStatement . $whereStatement . $groupByQuery . $orderByQuery . $limitOffset;

		return self::raw( $queryString );
	}

	public static function delete( $table , $where = null )
	{
		$whereStatement = '';
		if( !empty( $where ) && is_array( $where ) )
		{
			$whereStatement = ' WHERE ' . DB::where( $where );
		}

		$queryString = "DELETE FROM " . self::table($table) . $whereStatement;

		return self::DB()->query( self::raw( $queryString ) );
	}

	public static function update( $table , $data = [], $where = null )
	{
		$whereStatement = '';
		if( !empty( $where ) && is_array( $where ) )
		{
			$whereStatement = ' WHERE ' . DB::where( $where );
		}

		$queryString = "UPDATE " . self::table($table) . ' SET ' . self::setData( $data ) . $whereStatement;

		return self::DB()->query( self::raw( $queryString ) );
	}

	public static function fetch()
	{
		return self::DB()->get_row( call_user_func_array( [static::class, 'selectQuery'], func_get_args() ),ARRAY_A );
	}

	public static function fetchAll()
	{
		return self::DB()->get_results( call_user_func_array( [static::class, 'selectQuery'], func_get_args() ),ARRAY_A );
	}

	public static function raw( $raw_query, $args = [] )
	{
		if( empty( $args ) )
			return $raw_query;

		return DB::DB()->prepare( $raw_query, $args );
	}

	public static function tenantFilter( $and_or = 'AND', $field_name = '`tenant_id`' )
	{
		$tenantId = (int)Permission::tenantId();

		if( $tenantId > 0 )
		{
			return " {$and_or} {$field_name}='{$tenantId}' ";
		}

		return '';
	}

	public static function lastInsertedId()
	{
		return DB::DB()->insert_id;
	}

	private static function where( $where, $table = '' )
	{
		if( empty( $where ) || !is_array( $where ) )
			return '';

		$whereQuery =  '';
		$argss = [];

		foreach( $where AS $whereInf )
		{
			$field  = $whereInf[0];
			$symbol = strtoupper( $whereInf[1] );
			$value  = $whereInf[2];
            $combinator = !empty($whereInf[3]) ? $whereInf[3] : 'AND';

			if( !empty( $table ) && is_string( $field ) && strpos( $field, '.' ) === false )
			{
				$field = $table . '.' . $field;
			}

			if( $field instanceof QueryBuilder )
			{
				$field = '(' . $field->toSql() . ')';
			}

			if( is_callable( $field ) && is_object( $field ) )
			{
				$newWhereGroup = new Model();
				$field( $newWhereGroup );


				$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . '(' . self::where( $newWhereGroup->getWhereArr() ) . ')';
			}
			else if( is_array( $value ) )
			{
				$percentS = '';

				foreach( $value AS $valueIn )
				{
					$percentS .= ( empty( $percentS ) ? '' : ',' ) . '%s';
					$argss[] = $valueIn;
				}

				$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . $field . ( $symbol == 'NOT IN' ? ' NOT IN ' : ' IN ' ) . ' (' . $percentS . ')';
			}
			else if( $value instanceof QueryBuilder )
			{
				$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . $field . ( $symbol == 'NOT IN' ? ' NOT IN ' : ' IN ' ) . ' (' . $value->toSql() . ')';
			}
			else if( in_array( $symbol, ['=', '<>', '!=', '>', '<' , '>=', '<=', 'LIKE', 'NOT LIKE', 'BETWEEN'] ) )
			{
				if( $value instanceof \stdClass)
				{
					$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . $field . ' ' . $symbol . ' ' . $value->field;
				}
				else
				{
					$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . $field . ' ' . $symbol . ' ' . '%s';
					$argss[] = (string)$value;
				}
			}
			else if( $symbol == 'FIND_IN_SET' )
			{
				$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . ' FIND_IN_SET( %s, ' . $field . ' ) ';
				$argss[] = (string)$value;
			}
			else if( in_array( $symbol, ['IS', 'IS NOT'] ) && is_null( $value ) )
			{
				$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . $field . ' ' . $symbol . ' NULL';
			}
			else
			{

				// doit: else? hansi halda?
				$whereQuery .= ($whereQuery == '' ? '' : " $combinator ") . $field . $symbol . '%s';
				$argss[] = (string)$value;
			}
		}

		return self::raw( $whereQuery, $argss );
	}

	private static function on( $where )
	{
		if( empty( $where ) || !is_array( $where ) )
			return '';

		$whereQuery =  '';

		foreach($where AS $value)
		{
			$field  = $value[0];
			$symbol = $value[1];
			$value  = $value[2];

			$whereQuery .= ($whereQuery == '' ? '' : ' AND ') . $field . ' ' . $symbol . ' ' . $value;
		}

		return self::raw( $whereQuery );
	}

	private static function setData( $data )
	{
		$setDataStatement = '';
		$setDataArguments = [];
		foreach ( $data AS $field => $value )
		{
			$setDataStatement .= empty( $setDataStatement ) ? '' : ', ';
			if( $value instanceof QueryBuilder )
			{
				$setDataStatement .= $field . '=( ' . $value->toSql() . ' )';
			}
			else if( $value instanceof \stdClass )
			{
				$setDataStatement .= $field . '=' . $value->field;
			}
			else if ( ! is_null( $value ) )
            {
                /* eger $value null olarsa o zaman %s stringe cevirir onu, yeni ''-e. mysql type date olanda ise '' cevrilir 0000-00-00
                ve bu halda empty( $date ) false verir */

                $setDataStatement .= $field . '=%s';
                $setDataArguments[] = $value;
            }
            else
            {
                $setDataStatement .= $field . '=NULL';
            }
		}

		return self::raw( $setDataStatement, $setDataArguments );
	}

	public static function field( $fieldName, $table = null )
	{
		$obj = new \stdClass();
		$obj->field = ( is_null( $table ) ? '' : DB::table( $table ) . '.' ) . $fieldName;

		return $obj;
	}

}
