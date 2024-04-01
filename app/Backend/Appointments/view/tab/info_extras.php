<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

$saveCustomerId = 0;

if ( empty( $parameters['extras'] ) )
{
	echo '<div class="text-secondary font-size-14 text-center">' . bkntc__( 'No extras found' ) . '</div>';
}
else
{
	foreach ( $parameters['extras'] AS $extra )
	{
        ?>

        <div class="form-row extra_row dashed-border" data-id="3" data-active="1">
            <div class="form-group col-md-4">
                <label class="text-primary"><?php echo bkntc__('Name:'); ?></label>
                <div class="form-control-plaintext" data-tag="name"><?php echo $extra['service_extras_name'] ?></div>
            </div>
            <div class="form-group col-md-3">
                <label><?php echo bkntc__('Duration:'); ?></label>
                <div class="form-control-plaintext" data-tag="duration"><?php echo empty($extra['duration'])?'-':Helper::secFormat( $extra['duration'] * 60 )?></div>
            </div>
            <div class="form-group col-md-2">
                <label><?php echo bkntc__('Price:'); ?></label>
                <div class="form-control-plaintext" data-tag="price"><?php echo Helper::price( $extra['price'] * $extra['quantity'] )?></div>
            </div>
            <div class="form-group col-md-3">
                <label><?php echo bkntc__('Quantity:'); ?></label>
                <div class="form-control-plaintext" data-tag="quantity"><?php echo $extra['quantity'] ?></div>
            </div>
        </div>

        <?php
    }
}
?>