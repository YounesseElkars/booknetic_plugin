<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Backend\Boostore\Helpers\BoostoreHelper;

/**
 * @var mixed $parameters
 */

$totalPrice = 0;
$allAddonsInCart = BoostoreHelper::checkAllAddonsInCart();
?>

<link rel="stylesheet" href="<?php echo Helper::assets( 'css/shared.css', 'Boostore' ) ?>" type='text/css'>
<link rel="stylesheet" href="<?php echo Helper::assets( 'css/boostore.css', 'Boostore' ) ?>" type='text/css'>
<link rel="stylesheet" href="<?php echo Helper::assets( 'css/cart.css', 'Boostore' ) ?>" type='text/css'>


<div class="boostore">
    <!-- Page header -->
    <div class="m_header clearfix">
        <div class="m_head_title float-left">
            <?php echo bkntc__( 'Cart' ); ?>
        </div>
        <div class="m_head_actions float-right">
            <a class="btn btn-lg btn-primary float-right ml-1" href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=boostore&action=my_purchases"><?php echo bkntc__( 'MY PURCHASES' ); ?></a>
            <?php if ( ! empty( $parameters[ 'items' ] ) ): ?>
                <a id="clearCart" class="btn btn-lg btn-secondary float-right ml-1" href="#"><?php echo bkntc__( 'CLEAR CART' ); ?></a>
            <?php endif; ?>
        </div>
    </div>

    <hr/>

    <?php if ( ! empty( $parameters[ 'items' ] ) ): ?>
        <section class="addons_content">
            <div class="row addons_card_wrapper">
                <div class="col-lg-8 mb-4 fs_data_table_wrapper">

                    <table class="fs_data_table elegant_table">
                        <thead>
                        <tr>
                            <th></th>
                            <th style="text-align: left; width: 50%;"><?php echo bkntc__( 'Addon') ?></th>
                            <th><?php echo bkntc__('Price') ?></th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ( $parameters[ 'items' ] AS $addon ):?>
                            <tr data-addon="<?php echo $addon[ 'slug' ] ?>">
                                <td></td>
                                <td style="width: 50%; text-align: left;"><?php echo $addon[ 'name' ] ?></td>
                                <td>
                                    <?php if ( $addon[ 'price' ][ 'current' ] < $addon[ 'price' ][ 'old' ] ): ?>
                                        <span class="cart-addon-old-price">$<?php echo round( $addon[ 'price' ][ 'old' ], 1 ); ?></span>
                                    <?php endif; ?>
                                    <span class="cart-addon-current-price">$<?php echo round( $addon[ 'price' ][ 'current' ], 1 ); ?></span></td>
                                <td class="d-flex justify-content-end align-items-center">
                                    <button class="btn btn-danger remove-cart-item"><?php echo bkntc__('Remove') ?></button>
                                </td>
                            </tr>
                        <?php endforeach;?>
                        </tbody>

                    </table>

                </div>
                <div class="col-lg-4">
                    <div class="checkout_wrapper">
                        <div>
                            <p><?php echo bkntc__('Cart details') ?>:</p>
                        </div>
                        <div class="checkout_wrapper_prices">
                            <?php foreach ( $parameters[ 'items' ] AS $addon ): ?>
                                <?php $totalPrice += round( $addon[ 'price' ][ 'current' ], 1 );?>
                                <div class="checkout_price_item" data-addon="<?php echo $addon[ 'slug' ] ?>" data-price="<?php echo $addon[ 'price' ][ 'current' ] ?>">
                                    <p class="checkout_price_item__title"><?php echo $addon[ 'name' ] ?> </p>
                                    <p class="checkout_price_item__price">$<?php echo round( $addon[ 'price' ][ 'current' ], 1 )?> </p>
                                </div>
                            <?php endforeach; ?>

                            <div class="checkout_price_item_total" >
                                <?php if( $allAddonsInCart ): ?>
                                    <div id="buy_all_discount" class="checkout_price_item"><p class="checkout_price_item__total__title"><?php echo bkntc__( 'Discount' ) ?></p> <p class="checkout_price_item__price">15%</p></div>
                                <?php endif; ?>
                                <div class="checkout_price_item">
                                    <p class="checkout_price_item__total__title"> <?php echo bkntc__('Total'); ?> </p>
                                    <p id="checkout_total_price" class="checkout_price_item__price checkout_price_item_total_price" data-total-price="<?php echo $totalPrice ?>">$<?php echo $totalPrice ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if( ! $allAddonsInCart ) :?>
                            <div id="coupon_wrapper" class="input-group my-2 mb-4">
                                <input id="coupon" class="form-control" placeholder="Enter coupon code">
                                <div class="input-group-append">
                                    <button id="apply_discount" class="btn h-100 btn-primary" type="button"><?php echo bkntc__('Apply') ?></button>
                                </div>
                            </div>

                            <div id="coupon_applied_wrapper" class="input-group my-2 mb-4 justify-content-between" style="display:none; border-top: 1px solid #d7d9dc; padding-top: 10px;">
                                <label style="font-weight: 500; font-size: 14px; margin: 0;" id="coupon_label">
                                    <?php echo bkntc__( 'Coupon applied' ) ?>:<span id="applied_coupon" style="font-weight: normal; margin-left: 8px;"></span>
                                </label>
                                <div id="remove_discount" class="close-btn" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; text-align: center; border-radius: 50%; background: #721c24; cursor: pointer;"><i class="fa fa-times" style="color: #ffffff; line-height: normal;"></i></div>
                            </div>
                        <?php endif;?>

                        <button id="purchaseCart" class="btn w-100 btn-lg btn-success d-flex justify-content-center"><?php echo bkntc__('Proceed to checkout') ?></button>
                    </div>
                </div>
            </div>
        </section>
        <!-- Filter panel -->
    <?php else: ?>
        <h1 class="display-1 d-flex justify-content-center"><?php echo bkntc__('Your Cart is empty') ?></h1>
    <?php endif; ?>


</div>

<script src="<?php echo Helper::assets( 'js/shared.js', 'Boostore' ) ?>"></script>