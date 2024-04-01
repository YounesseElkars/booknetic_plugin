<?php

namespace BookneticApp\Backend\Boostore\Helpers;

use BookneticApp\Models\Cart;
use BookneticVendor\GuzzleHttp\Client;
use Exception;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Bootstrap;
use BookneticApp\Providers\Core\PluginInstaller;

class BoostoreHelper
{
    private static $baseURL = 'https://api.fs-code.com/store/booknetic';

    private static $version = null;

    public static function get ( $slug, $data = [], $default = [] )
    {
        try
        {
            $client = new Client( [
                'verify'  => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . Helper::getOption( 'access_token', '', false ),
                    'Product'       => 'Booknetic ' . Helper::getInstalledVersion(),
                    'Content-Type'  => 'application/json',
                ],
            ] );

            $response = $client->post( static::$baseURL . '/' . $slug, [ 'body' => json_encode( $data ) ] );

            $apiRes = json_decode( $response->getBody(), true );

            if ( ! empty( $apiRes ) && $response->getStatusCode() === 200 )
            {
                if ( $apiRes[ 'status' ] === 200 )
                {
                    return $apiRes[ 'body' ];
                }
                else
                {
                    throw new Exception();
                }
            }
            else
            {
                throw new Exception();
            }
        }
        catch ( Exception $e )
        {
            return $default;
        }
    }

    public static function getAddonSlug ( $slug )
    {
        $plugins = get_plugins();

        foreach ( $plugins as $pluginKey => $pluginInfo )
        {
            if ( explode( '/', $pluginKey )[ 0 ] === $slug )
            {
                return $pluginKey;
            }
        }

        return '';
    }

    public static function installAddon ( $slug, $downloadURL )
    {
        ignore_user_abort( true );
        set_time_limit( 0 );

        $addonInstaller = new PluginInstaller( $downloadURL, $slug );

        if ( $addonInstaller->install() )
        {
            return activate_plugin( BoostoreHelper::getAddonSlug( $slug ) ) === null;
        }

        return false;
    }

    public static function uninstallAddon ( $slug )
    {
        if ( ! empty( BoostoreHelper::getAddonSlug( $slug ) ) && file_exists( realpath( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . BoostoreHelper::getAddonSlug( $slug ) ) ) )
        {
            unset( Bootstrap::$addons[ $slug ] );

            return delete_plugins( [ self::getAddonSlug( $slug ) ] ) === true;
        }

        return false;
    }

    public static function getAllAddons()
    {
        return BoostoreHelper::get( 'addons', [ 'list_all_addons' => true ], [ 'items' => [] ] );
    }

    public static function isInstalled( $slug )
    {
        return ! empty( BoostoreHelper::getAddonSlug( $slug ) ) &&
            file_exists(
                realpath(
                    WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . BoostoreHelper::getAddonSlug( $slug )
                ));
    }

    public static function recalculatePrices()
    {
        $cart = Cart::select( 'slug' )->where( 'active', 1 )->fetchAll();

        if ( empty( $cart ) )
            return [];

        $cartPrices = [];
        $totalPrice = 0;

        $cart = array_map( fn( $addon ) => $addon->slug, $cart );

        foreach( static::getAllAddons()[ 'items' ] as $addon )
        {
            if ( in_array( $addon['slug'], $cart ) )
            {
                $cartPrices[] = [ 'slug' => $addon['slug'], 'price' => $addon['price']['current'] ];
                $totalPrice += $addon['price']['current'];
            }
        }

        $cartPrices[ 'total_price' ] = $totalPrice;

        return $cartPrices;

    }

    public static function whichVersion()
    {
        if ( empty( self::$version ) )
        {
            self::$version = self::get('get_boostore_version', [], ['version' => 1])['version'];
        }

        return self::$version;
    }

    public static function checkAllAddonsInCart(): bool
    {
        return Helper::getOption( 'total_addons_count' )
            == Cart::where( 'active', 1 )->count();
    }

    public static function checkAllAddonsUnowned(): bool
    {
        $addons = BoostoreHelper::getAllAddons();

        return count(
                array_filter( $addons[ 'items' ], fn( $a ) => $a[ 'purchase_status' ] == 'unowned' )
            ) == $addons[ 'total' ] - 1;
    }

    public static function checkAllAddonsBought( int $total ): bool
    {
        return $total == Cart::where( 'active', 1 )->count();
    }

    public static function applyCoupon( string $cart, string $coupon )
    {
        return BoostoreHelper::get( 'apply_discount', [
            'cart'        => $cart,
            'coupon'      => $coupon,
        ] );
    }

    public static function addAllToCart( array $addons )
    {
        $now = (new \DateTime())->getTimestamp();

        foreach ( $addons as $a )
        {
            Cart::insert( [
                'slug'       => $a[ 'slug' ],
                'active'     => 1,
                'created_at' => $now
            ] );
        }
    }

    public static function filterAllAddons( array $addons )
    {
        $filteredAddons = self::getUnownendAddons( $addons );

        return self::getUnaddedAddons( $filteredAddons );
    }

    public static function getUnownendAddons( array $addons ): array
    {
        return array_filter( $addons, fn( $a ) => $a[ 'purchase_status' ] == 'unowned' );
    }

    public static function getUnaddedAddons( array $addons )
    {
        $cart = Cart::where( 'active', 1 )->fetchAll();
        $slugsOfAddonsInCart = array_map( fn( $a ) => $a[ 'slug' ], $cart );

        return array_filter( $addons, fn( $a ) => ! in_array( $a[ 'slug' ], $slugsOfAddonsInCart ) );
    }
}
