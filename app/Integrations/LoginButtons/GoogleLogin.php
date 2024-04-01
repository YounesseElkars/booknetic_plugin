<?php

namespace BookneticApp\Integrations\LoginButtons;

use BookneticApp\Providers\Helpers\Curl;
use BookneticApp\Providers\Helpers\Helper;
use BookneticVendor\Google\Client;
use BookneticVendor\Google\Service\Oauth2;

class GoogleLogin
{

	private static $_google_client;

	public static function callbackURL ()
	{
		return site_url() . '/?' . Helper::getSlugName() . '_action=google_login_callback';
	}

	public static function getLoginURL ( )
	{
		return self::googleClient()->createAuthUrl();
	}

	public static function getUserData()
	{
		$code = Helper::_get( 'code', '', 'string' );

		if ( empty( $code ) )
			self::error( Helper::_get( 'error_message', '', 'string' ) );

		$token = self::googleClient()->fetchAccessTokenWithAuthCode( $code );
		if( !isset( $token['access_token'] ) )
			self::error( isset( $token['error_description'] ) ? $token['error_description'] : '' );

		self::googleClient()->setAccessToken( $token['access_token'] );

		$google_service = new Oauth2( self::googleClient() );
		$data = $google_service->userinfo->get();

		$me = [
			'first_name'    => '',
			'last_name'     => '',
			'email'         => ''
		];

		if( !empty( $data['given_name'] ) )
			$me['first_name'] = $data['given_name'];

		if(!empty($data['family_name']))
			$me['last_name'] = $data['family_name'];

		if(!empty($data['email']))
			$me['email'] = $data['email'];

		return $me;
	}

	private static function googleClient()
	{
		if( is_null( self::$_google_client ) )
		{
			$appId          = Helper::getOption('google_login_app_id', '', false);
			$appSecret      = Helper::getOption('google_login_app_secret', '', false);
			$callbackUrl    = self::callbackUrl();

			$gClient = new Client();
			$gClient->setClientId( $appId );
			$gClient->setClientSecret( $appSecret );
			$gClient->addScope('email');
			$gClient->addScope('profile');
			$gClient->setRedirectUri( $callbackUrl );

			self::$_google_client = $gClient;
		}

		return self::$_google_client;;
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
