<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Providers\Helpers\Helper;
use BookneticVendor\GuzzleHttp\Client;
use BookneticVendor\GuzzleHttp\Exception\GuzzleException;

class FSCodeAPI
{
    /**
     * @var string
     */
    private static $baseUri = 'https://api.fs-code.com/store/booknetic/';

    /**
     * @var Client $client
    */
    private $client = null;

    public function __construct()
    {
        $this->setClient();
    }

    /**
     * @param string $path
     * @param array $body
     * @return array
     */
    public function get( $path, $body = [] )
    {
        return $this->request( 'get', $path, $body );
    }

    /**
     * @param string $path
     * @param array $body
     * @return array
     */
    public function post( $path, $body = [] )
    {
        return $this->request( 'post', $path, $body );
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $body
     * @return array
     */
    public function request( $method, $path, $body = [] )
    {
        try {
            $response = $this->client()->request( $method, $path, [ 'body' => json_encode( $body ) ] );
        } catch ( GuzzleException $e ) {
            return [];
        }

        if ( $response->getStatusCode() !== 200 )
            return [];

        $body = $response->getBody()->getContents();

        if ( ! $body )
            return [];

        return json_decode( $body, true ) ?: [];
    }

    /**
     * @return void
     */
    private function setClient()
    {
        $this->client = new Client( [
            'verify'   => false,
            'base_uri' => self::$baseUri,
            'headers'  => [
                'Authorization' => 'Bearer ' . Helper::getOption( 'access_token', '', false ),
                'Product'       => 'Booknetic ' . Helper::getInstalledVersion(),
                'Content-Type'  => 'application/json',
            ],
        ] );
    }

    /**
     * @return Client
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @param string $name
     * @param string $dst
     * @return void
     */
    public static function uploadFileFromName( $name, $dst )
    {
        $url = sprintf( '%s/%s', self::$baseUri, $name );

        self::uploadFileFromUrl( $url, $dst );
    }

    /**
     * @param string $src
     * @param string $dst
     * @return void
     */
    public static function uploadFileFromUrl( $src, $dst )
    {
        $img = file_get_contents( $src );

        file_put_contents( $dst, $img );
    }
}