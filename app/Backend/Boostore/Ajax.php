<?php

namespace BookneticApp\Backend\Boostore;

use BookneticApp\Models\Cart;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Backend\Boostore\Helpers\BoostoreHelper;

class Ajax extends \BookneticApp\Providers\Core\Controller
{
    public function get_addons ()
    {
        $reqBody = [
            'category_ids' => Helper::_post( 'category_ids', null, 'string' ),
            'search'       => Helper::_post( 'search', null, 'string' ),
            'order_by'     => Helper::_post( 'order_by', null, 'string' ),
            'order_type'   => Helper::_post( 'order_type', null, 'string' ),
            'page'         => Helper::_post( 'page', null, 'int' ),
        ];

        $data = BoostoreHelper::get( 'addons', $reqBody, [
            'items' => [],
        ] );

        Helper::setOption( 'total_addons_count', $data[ 'total' ] - 1 ); // -1 for email addon

        $cartItems = Cart::select( 'slug' )->where('active', 1)->fetchAll();

        $cartItems = array_column( $cartItems, 'slug' );


        foreach ( $data[ 'items' ] as $i => $addon )
        {
            $data[ 'items' ][ $i ][ 'is_installed' ] = ! empty( BoostoreHelper::getAddonSlug( $addon[ 'slug' ] ) ) && file_exists( realpath( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . BoostoreHelper::getAddonSlug( $addon[ 'slug' ] ) ) );

            $data[ 'items' ][ $i ][ 'in_cart' ] = in_array( $addon[ 'slug' ], $cartItems ) && ! $data[ 'items' ][ $i ][ 'is_installed' ];
        }

        $viewFile = 'addons_v' . BoostoreHelper::whichVersion();

        return $this->modalView( $viewFile, [
            'data'              => $data,
            'is_search'         => $reqBody[ 'search' ],
        ], [ 'version' => BoostoreHelper::whichVersion() ] );
    }

    public function purchase ()
    {
        $addonSlug = Helper::_post( 'addon_slug', null, 'string' );

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false, 'You can\'t purchase add-on on Demo version!' );
        }

        if ( empty( $addonSlug ) )
        {
            return $this->response( false, bkntc__( 'An error occurred, please try again later' ) );
        }

        $data = BoostoreHelper::get( 'generate_purchase_url/' . $addonSlug, [
            'domain'       => site_url(),
            'redirect_url' => admin_url( 'admin.php?page=' . Helper::getBackendSlug() . '&module=boostore&action=purchased' ),
        ] );

        if ( ! empty( $data[ 'purchase_url' ] ) )
        {
            return $this->response( true, [ 'purchase_url' => $data[ 'purchase_url' ] ] );
        }
        else if ( ! empty( $data[ 'error_message' ] ) )
        {
            return $this->response( false, htmlspecialchars( $data[ 'error_message' ] ) );
        }

        return $this->response( false, bkntc__( 'An error occurred, please try again later!' ) );
    }

    public function purchase_cart ()
    {
        $cart = json_decode( Helper::_post( 'cart', '', 'string' ), true );
        $coupon = BoostoreHelper::checkAllAddonsInCart()
            ? 'buyallcoupon1520231122'
            : Helper::_post( 'coupon', '', 'string' );

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false, 'You can\'t purchase add-on on Demo version!' );
        }

        if ( empty( $cart ) && ! is_array( $cart ) )
        {
            return $this->response( false, bkntc__( 'An error occurred, please try again later' ) );
        }

        $data = BoostoreHelper::get( 'generate_cart_purchase_url', [
            'domain'       => site_url(),
            'redirect_url' => admin_url( 'admin.php?page=' . Helper::getBackendSlug() . '&module=boostore&action=purchased' ),
            'cart'         => $cart,
            'coupon'       => $coupon,
        ] );

        if ( ! empty( $data[ 'purchase_url' ] ) )
        {
            return $this->response( true, [ 'purchase_url' => $data[ 'purchase_url' ] ] );
        }
        else if ( ! empty( $data[ 'error_message' ] ) )
        {
            return $this->response( false, htmlspecialchars( $data[ 'error_message' ] ) );
        }

        return $this->response( false, bkntc__( 'An error occurred, please try again later!' ) );
    }

    public function apply_discount()
    {
        $cart = Helper::_post( 'cart', '', 'string' );
        $coupon = Helper::_post( 'coupon', '', 'string' );

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false, 'You can\'t use coupons on Demo version!' );
        }

        if ( empty( $coupon ) && ! is_string( $coupon ) )
        {
            return $this->response( false, bkntc__( 'Coupon cannot be empty!' ) );
        }

        if ( empty( $cart ) && ! is_array( $cart ) )
        {
            return $this->response( false, bkntc__( 'An error occurred, please try again later' ) );
        }

        $data = BoostoreHelper::applyCoupon( $cart, $coupon );

        if ( ! empty( $data[ 'discounted_addons' ] ) && ! empty( $data[ 'total_price' ] ) )
        {
            return $this->response( true, [ 'discounted_addons' => $data[ 'discounted_addons' ], 'total_price' => $data[ 'total_price' ] ] );
        }
        else if ( ! empty( $data[ 'error_message' ] ) )
        {
            return $this->response( false, htmlspecialchars( $data[ 'error_message' ] ) );
        }

        return $this->response( false, bkntc__( 'An error occurred, please try again later!' ) );
    }

    public function add_to_cart()
    {
        $addonSlug = Helper::_post( 'addon', '', 'string' );

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false, 'You can\'t purchase add-on on Demo version!' );
        }

        if ( empty( $addonSlug ) )
        {
            return $this->response( false, bkntc__( 'An error occurred, please try again later' ) );
        }

        if ( ! empty( Cart::where([ 'slug' =>  $addonSlug, 'active' => 1 ])->fetch() ) )
        {
            return $this->response( false, bkntc__( 'Addon already exists in your cart' ) );
        }

        $sqlData = [
            'slug'     => $addonSlug,
            'active'    => 1,
            'created_at'=> (new \DateTime())->getTimestamp(),
        ];

        Cart::insert( $sqlData );

        return $this->response( true, [ 'message' => bkntc__( 'Added to cart' ) ] );
    }

    public function remove_from_cart()
    {
        $addonSlug = Helper::_post( 'addon', '', 'string' );

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false, 'You can\'t purchase add-on on Demo version!' );
        }

        if ( empty( $addonSlug ) )
        {
            return $this->response( false, bkntc__( 'An error occurred, please try again later' ) );
        }


        Cart::where([ 'slug' => $addonSlug, 'active' => 1 ])->update([
            'active' => 0,
            'removed_at' => (new \DateTime())->getTimestamp(),
        ]);

        return $this->response( true, [
            'message' => bkntc__( 'Removed from cart.' ),
            'prices'  => BoostoreHelper::recalculatePrices(),
        ] );
    }

    public function install ()
    {
        $addonSlug = Helper::_post( 'addon_slug', null, 'string' );

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false, 'You can\'t install add-on on Demo version!' );
        }

        if ( empty( $addonSlug ) )
        {
            return $this->response( false, bkntc__( 'An error occurred, please try again later' ) );
        }

        $data = BoostoreHelper::get( 'generate_download_url/' . $addonSlug, [
            'domain' => site_url(),
        ] );

        if ( ! empty( $data[ 'download_url' ] ) && BoostoreHelper::installAddon( $addonSlug, $data[ 'download_url' ] ) )
        {
            return $this->response( true, [ 'message' => bkntc__( 'Installed successfully!' ) ] );
        }
        else if ( ! empty( $data[ 'error_message' ] ) )
        {
            return $this->response( false, htmlspecialchars( $data[ 'error_message' ] ) );
        }

        return $this->response( false, bkntc__( 'An error occurred, please try again later!' ) );
    }

    public function install_finished ()
    {
        if ( Permission::isDemoVersion() )
        {
            return $this->response( false );
        }

        Helper::deleteOption( 'migration_v3', false );

        return $this->response( true );
    }

    public function uninstall ()
    {
        $addon = Helper::_post( 'addon', false, 'string' );

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false, 'You can\'t uninstall add-on on Demo version!' );
        }

        if ( empty( $addon ) )
        {
            return $this->response( false, bkntc__( 'Addon not found!' ) );
        }

        if ( BoostoreHelper::uninstallAddon( $addon ) )
        {
            return $this->response( true, [ 'message' => bkntc__( 'Addon uninstalled successfully!' ) ] );
        }

        return $this->response( false, bkntc__( 'Addon couldn\'t be uninstalled!' ) );
    }

    public function clear_cart()
    {

        if ( Permission::isDemoVersion() )
        {
            return $this->response( false );
        }

        $cartItems = Cart::select( 'slug' )->where('active', 1)->fetchAll();

        if (  empty ( $cartItems ) )
        {
            return $this->response( false, bkntc__( 'Your cart is already empty.' ) );
        }

        Cart::where( 'active', 1 )->update([
            'active' => 0
        ]);

        return $this->response( true, [ 'message' => bkntc__( 'Cart cleared!' ) ] );
    }

    public function buy_all()
    {
        if ( BoostoreHelper::checkAllAddonsInCart() )
            return $this->response( false );

        BoostoreHelper::addAllToCart( BoostoreHelper::filterAllAddons( BoostoreHelper::getAllAddons()[ 'items' ] ) );

        return $this->response( true, [ 'message' => bkntc__( 'All addons added to cart!' ) ] );
    }

    public function apply_buy_all_discount()
    {
        $data = BoostoreHelper::applyCoupon( Helper::_post( 'cart', '', 'string' ), 'buyallcoupon1520231122' );

        if ( ! empty( $data[ 'discounted_addons' ] ) && ! empty( $data[ 'total_price' ] ) )
        {
            return $this->response( true, [ 'discounted_addons' => $data[ 'discounted_addons' ], 'total_price' => $data[ 'total_price' ] ] );
        }
        else if ( ! empty( $data[ 'error_message' ] ) )
        {
            return $this->response( false, htmlspecialchars( $data[ 'error_message' ] ) );
        }

        return $this->response( false, bkntc__( 'An error occurred, please try again later!' ) );
    }
}
