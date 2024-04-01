<?php

namespace BookneticApp\Providers\DB;

use BookneticApp\Models\Data;
use BookneticApp\Providers\DB\Collection;
use BookneticApp\Providers\DB\Model;

trait DataService
{

	private static $data = [];

	private static function _getData ( $tableName, $id, $key, $default = null )
	{
		if ( ! isset( self::$data[ $tableName ][ $id ][ $key ] ) )
		{
			$data = Data::where( 'table_name', $tableName )
			            ->where( 'row_id', $id )
			            ->where( 'data_key', $key )
			            ->fetch();

			self::$data[ $tableName ][ $id ][ $key ] = ! is_null( $data ) && isset( $data[ 'data_value' ] ) ? $data[ 'data_value' ] : $default;

		}

		return self::$data[ $tableName ][ $id ][ $key ];
	}

	private static function _setData ( $tableName, $id, $key, $value, $updateIfExists = true  )
	{
		if ( ! is_null( self::_getData( $tableName, $id, $key ) ) && $updateIfExists === true )
		{
			$res = Data::where( 'table_name', $tableName )
			           ->where( 'row_id', $id )
			           ->where( 'data_key', $key )
			           ->update( [ 'data_value' => $value ] );
		}
		else
		{
			$res = Data::insert( [
				'table_name'    => $tableName,
				'row_id'        => $id,
				'data_key'      => $key,
				'data_value'    => $value
			] );
		}

		self::$data[ $tableName ][ $id ][ $key ] = $value;

		return $res;
	}

	private static function _deleteData ( $tableName, $id = null, $key = null, $value = null )
	{
		unset( self::$data[ $tableName ][ $id ][ $key ] );

        $data = Data::where( 'table_name', $tableName );

        if ( ! empty( $id ) )
        {
            $data->where( 'row_id', $id );
        }

        if ( ! empty( $key ) )
        {
            $data->where( 'data_key', $key );
        }

        if ( ! empty( $value ) )
        {
            $data->where( 'data_value', $value );
        }

		return $data->delete();
	}

}