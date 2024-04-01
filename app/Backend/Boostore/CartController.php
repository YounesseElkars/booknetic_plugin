<?php

namespace BookneticApp\Backend\Boostore;


use BookneticApp\Backend\Boostore\Helpers\BoostoreHelper;
use BookneticApp\Models\Cart;

class CartController extends \BookneticApp\Providers\Core\Controller
{
    public function index()
    {
        $cartItems = Cart::select( 'slug' )->where('active', 1)->fetchAll();

        if ( Cart::where( 'active',0 )->count() > 100 )
        {
            //todo: add analytics before deleting
            Cart::where( 'active', 0 )->delete();
        }

        $cartItems = array_column( $cartItems, 'slug' );

        $cartAddonData = [];

        foreach ( BoostoreHelper::getAllAddons()[ 'items' ] AS $addon )
        {
            if ( in_array( $addon[ 'slug' ], $cartItems ) && ! BoostoreHelper::isInstalled( $addon[ 'slug' ] ) && $addon[ 'purchase_status' ] !== 'owned' )
            {
                $cartAddonData[] = $addon;
            }
            else if ( in_array( $addon[ 'slug' ], $cartItems ) && $addon[ 'purchase_status' ] === 'owned' )
            {
                Cart::where([ 'slug' => $addon[ 'slug' ], 'active' => 1 ])->delete();
            }
        }

        $this->view(  'cart/index', [
            'items' => $cartAddonData
        ] );
    }
}