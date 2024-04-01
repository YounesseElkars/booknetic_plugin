<?php

/**
 * @var $parameters
 */

use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Providers\Helpers\Helper;

?>

<div class="booknetic-cart-holder">
    <div class="booknetic-cart">
        <?php $i = 0; foreach ( AppointmentRequests::appointments() as $key => $appointment ): ?>
            <div class="booknetic-cart-col" data-index="<?php echo $i; ?>"  >
                <div class="booknetic-cart-item <?php echo $i==$parameters['current_index'] ? 'active' : ''; ?>">
                    <div class="booknetic-cart-item-header">
                        <span><?php echo empty($appointment->serviceInf->name) ? '-' : htmlspecialchars( $appointment->serviceInf->name ); ?></span>
                        <button class="booknetic-cart-item-more">
                            <img src="<?php echo Helper::icon('more-vertical.svg','front-end') ?>" alt="">
                        </button>
                        <div class="booknetic-cart-item-btns ">
                             <?php if($i!=$parameters['current_index']): ?>
                            <button class="booknetic-cart-item-edit">
                                <img src="<?php echo Helper::icon('edit-2.svg','front-end') ?>" >
                                <span><?php echo bkntc__( 'Edit' ); ?></span>
                            </button>
                             <?php endif; ?>
                            <button class="booknetic-cart-item-remove">
                                <img src="<?php echo Helper::icon('trash-2.svg','front-end') ?>" >
                                <span><?php echo bkntc__( 'Remove' ); ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="booknetic-cart-item-body">
                        <?php if( Helper::getOption('show_step_staff','on') === 'on'): ?>
                        <div class="booknetic-cart-item-body-row">
                            <span class="booknetic-cart-item-body-cell"><?php echo bkntc__( 'Staff' ); ?>:</span>
                            <span class="booknetic-cart-item-body-cell"><?php echo empty($appointment->staffId) || $appointment->staffId < 0 ? bkntc__('Any') : htmlspecialchars( $appointment->staffInf->name ); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if( Helper::getOption('show_step_location','on') === 'on'): ?>
                        <div class="booknetic-cart-item-body-row">
                            <span class="booknetic-cart-item-body-cell"><?php echo bkntc__( 'Location' ); ?>:</span>
                            <span class="booknetic-cart-item-body-cell"><?php echo empty($appointment->locationId) ? '-' : htmlspecialchars( $appointment->locationInf->name ); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="booknetic-cart-item-body-row">
                            <span class="booknetic-cart-item-body-cell"><?php echo bkntc__( 'Date & Time' ); ?>:</span>
                            <span class="booknetic-cart-item-body-cell"><?php echo $appointment->getDateTimeView(); ?></span>
                        </div>
                        <?php if( $appointment->serviceInf && $appointment->serviceInf->hide_price  != '1' ): ?>
                        <div class="booknetic-cart-item-body-row">
                            <span class="booknetic-cart-item-body-cell"><?php echo bkntc__('Amount') ?>:</span>
                            <span class="booknetic-cart-item-body-cell amount"><?php echo Helper::price( $appointment->getSubTotal( true ) ); ?></span>
                            <span class="booknetic-cart-item-body-cell">
                                <button class="booknetic-cart-item-info">
                                    <img src="<?php echo Helper::icon('info.svg' ,'front-end') ?>" alt="">
                                    <div class="booknetic-cart-item-info-details-arrow"></div>
                                </button>
                                <div class="booknetic-cart-item-info-details">
                                    <?php foreach ($appointment->getPrices() as $price):
                                        if( $price->getPrice() == 0 )
                                            continue;
                                        ?>
                                    <div class="booknetic-cart-item-info-details-row">
                                        <div class="booknetic-cart-item-info-details-cell"><?php echo $price->getLabel(); ?></div>
                                        <div class="booknetic-cart-item-info-details-cell"><?php echo $price->getPriceView(true); ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="booknetic-cart-item-error ">
                            <div class="booknetic-cart-item-error-header">
                                <div>
                                    <img src="<?php echo Helper::icon('alert-triangle.svg','front-end')?>" alt="">
                                    <span><?php echo bkntc__( 'Error' ) ?></span>
                                </div>
                            </div>
                            <div class="booknetic-cart-item-error-body"></div>
                        </div>

                    </div>
                </div>
            </div>
        <?php $i++; endforeach; ?>
    </div>
    <button class="bkntc_again_booking">
        <img src="<?php echo Helper::icon('plus-2.svg' ,'front-end')?>" alt="">
        <span><?php echo bkntc__('Add new Booking') ?></span>
    </button>
</div>

