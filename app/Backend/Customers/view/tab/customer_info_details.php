<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\UI\TabUI;

/**
 * @var mixed $parameters
 * @var mixed $customer
 */

$customer = $parameters['customer'];
?>
<div class="modal_payment">
    <div class="modal_payment-header d-flex justify-content-between align-items-center pb-4">
        <div class="modal_payment-profile d-flex align-items-center">
            <img src="<?php echo Helper::profileImage($customer['profile_image'], 'Customers'); ?>" alt="">
            <span><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></span>
        </div>

        <?php if ( ! empty( $parameters['customer_billing_datas'] ) ): ?>
            <button class="modal_payment-btn" data-toggle="dropdown">
                <img src="<?php echo Helper::icon('payment-more.svg')?>">
            </button>

            <div class="dropdown-menu billing_names-popover">
                <h6><?php echo bkntc__( 'Billing infos' ) ?></h6>
                <div class="billing_names-popover--cards">
                    <?php foreach($parameters['customer_billing_datas'] as $billing_data) { $billing_data = json_decode($billing_data['data_data_value'], true); ?>
                        <div>
                            <h6><?php echo $billing_data['customer_first_name'] . ' ' . $billing_data['customer_last_name']; ?></h6>
                            <span><?php echo $billing_data['customer_phone'] ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="row mt-3">
        <div class="col-lg-6">
            <div class="modal_payment-card">
                <h6><?php echo bkntc__('Email') ?></h6>
                <span class="text-break"><?php echo $customer['email'] ?? bkntc__('N/A' ); ?></span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modal_payment-card">
                <h6><?php echo bkntc__('Phone') ?></h6>
                <span><?php echo $customer['phone_number'] ?? bkntc__('N/A' ); ?></span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modal_payment-card">
                <h6><?php echo bkntc__('Gender') ?></h6>
                <span><?php echo isset( $customer[ 'gender' ] ) ? bkntc__( ucfirst( $customer['gender'] ) ) : bkntc__('N/A' ); ?></span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modal_payment-card">
                <h6><?php echo bkntc__('Birthdate') ?></h6>
                <span><?php echo $customer['birthdate'] ?? bkntc__('N/A' ); ?></span>
            </div>
        </div>

        <div class="col-lg-12 mt-3">
            <div class="modal_payment-card">
                <h6><?php echo bkntc__('Note') ?></h6>
                <span><?php echo $customer['notes'] ?? bkntc__('N/A' ); ?></span>
            </div>
        </div>
    </div>
</div>