<?php

namespace BookneticApp\Providers\Helpers;

class Curl
{

	public static function getURL( $url , $proxy = '' )
	{
		$headers = array
		(
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: en-US,fr;q=0.8;q=0.6,en;q=0.4,ar;q=0.2',
			'Accept-Charset: utf-8;q=0.7,*;q=0.7',
			'Accept-Encoding: gzip,deflate'
		);

		$ch = curl_init( $url );

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "GET");
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		if( !empty( $proxy ) )
		{
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}

		$result = curl_exec( $ch );

		$cError = curl_error( $ch );

		if( $cError )
		{
			return json_encode( [
				'error' => [
					'message' => htmlspecialchars( $cError )
				]
			] );
		}

		curl_close( $ch );

		return $result;
	}
}
