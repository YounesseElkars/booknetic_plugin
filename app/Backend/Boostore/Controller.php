<?php

namespace BookneticApp\Backend\Boostore;

use BookneticApp\Models\Cart;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Backend\Boostore\Helpers\BoostoreHelper;

class Controller extends \BookneticApp\Providers\Core\Controller
{
    public function index ()
    {

        $this->view(  'index', [
            'categories' => BoostoreHelper::get( 'categories', [], [] ),
            'cart_items_count' => Cart::where( 'active', 1 )->count(),
            'version'    => BoostoreHelper::whichVersion()
        ] );
    }

    public function details ()
    {
        $addonSlug = Helper::_get( 'slug', '', 'string' );

        $addon = BoostoreHelper::get( 'addons/' . $addonSlug, [], [] );

        if ( empty( $addon ) || !isset($addon['slug']) )
        {
            $this->view('modal/addons');
        }

        $cartItems = Cart::select( 'slug' )->where('active', 1)->fetchAll();

        $cartItems = array_column( $cartItems, 'slug' );

        $addon[ 'is_installed' ] = ! empty( BoostoreHelper::getAddonSlug( $addon[ 'slug' ] ) ) && file_exists( realpath( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . BoostoreHelper::getAddonSlug( $addon[ 'slug' ] ) ) );

        $addon[ 'in_cart' ] = in_array( $addon[ 'slug' ], $cartItems ) && ! $addon[ 'is_installed' ];


        $this->view(  'details', [
            'addon'     => $addon,
            'version'   => BoostoreHelper::whichVersion()
        ] );
    }

    public function purchased ()
    {
        $this->view( 'purchased', [], false );
    }

    public function my_purchases ()
    {
        $myPurchases = BoostoreHelper::get( 'my_purchases', [], [
            'items' => [],
        ] );

        foreach ( $myPurchases[ 'items' ] as $i => $addon )
        {
            $myPurchases[ 'items' ][ $i ][ 'is_installed' ] = BoostoreHelper::isInstalled( $addon[ 'slug' ] );
        }

        $this->view( 'my_purchases', [
            'items' => $myPurchases[ 'items' ],
            'is_migration' => ! empty( Helper::getOption( 'migration_v3', false, false ) ),
            'cart_items_count' => Cart::where( 'active', 1 )->count(),
            'version' => BoostoreHelper::whichVersion()
        ] );
    }
}
