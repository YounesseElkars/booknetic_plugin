<?php

namespace BookneticApp\Providers\Helpers;

use BookneticApp\Providers\Core\Permission;

class Session
{

	const SESSION_PREFIX = 'bkntc_';

	public static function set( $session_name, $session_value = null )
	{
		$userId = Permission::userId();

		self::delete( $session_name );

		add_user_meta( $userId, static::SESSION_PREFIX . $session_name, $session_value, true );
	}

	public static function get( $session_name, $default = null )
	{
		$userId = Permission::userId();

		$sess = get_user_meta( $userId, static::SESSION_PREFIX . $session_name, true );

		return empty( $sess ) ? $default : $sess;
	}

	public static function delete( $session_name )
	{
		$userId = Permission::userId();

		delete_user_meta( $userId, static::SESSION_PREFIX . $session_name );
	}


}