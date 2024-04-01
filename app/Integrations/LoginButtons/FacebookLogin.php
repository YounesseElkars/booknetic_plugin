<?php

namespace BookneticApp\Integrations\LoginButtons;

use BookneticApp\Providers\Helpers\Curl;
use BookneticApp\Providers\Helpers\Helper;

class FacebookLogin
{

	public static function callbackURL()
	{
		return site_url() . '/?' . Helper::getSlugName() . '_action=facebook_login_callback';
	}

	public static function getLoginURL( )
	{
		$appId  = Helper::getOption('facebook_app_id', '', false);

		$callbackUrl = self::callbackUrl();

		return "https://www.facebook.com/dialog/oauth?redirect_uri={$callbackUrl}&scope=public_profile,email&response_type=code&client_id={$appId}";
	}

	public static function getUserData()
	{
		$code = Helper::_get( 'code', '', 'string' );

		if ( empty( $code ) )
			self::error( Helper::_get( 'error_message', '', 'string' ) );

		$appSecret = Helper::getOption('facebook_app_secret', '', false);
		$appId     = Helper::getOption('facebook_app_id', '', false);

		$response = Curl::getURL( "https://graph.facebook.com/oauth/access_token?client_id=" . $appId . "&redirect_uri=" . urlencode( self::callbackUrl() ) . "&client_secret=" . $appSecret . "&code=" . $code );
		$params = json_decode( $response, true );

		if ( isset( $params[ 'error' ][ 'message' ] ) )
			self::error( $params[ 'error' ][ 'message' ] );

		$url = 'https://graph.facebook.com/me?fields=first_name,last_name,email&access_token=' . urlencode( $params[ 'access_token' ] );

		$data   = Curl::getURL( $url );
		$me     = json_decode( $data, true );

		if( ! is_array( $me ) )
			$me = [];

		if ( ! isset( $me[ 'first_name' ] ) )
			$me[ 'first_name' ] = '';

		if ( ! isset( $me[ 'last_name' ] ) )
			$me[ 'last_name' ] = '';

		if ( ! isset( $me[ 'email' ] ) )
			$me[ 'email' ] = '';

		return [
			'first_name'    =>  $me['first_name'],
			'last_name'     =>  $me['last_name'],
			'email'         =>  $me['email']
		];
	}

	private static function error ( $message = '' )
	{
		if ( empty( $message ) )
		{
			$message = bkntc__( 'An error occurred while processing your request! Please close the window and try again!' );
		}

		echo '<div>' . htmlspecialchars($message) . '</div>';
		exit();
	}


}
