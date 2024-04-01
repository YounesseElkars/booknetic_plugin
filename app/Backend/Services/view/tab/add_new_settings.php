<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Models\Service;

/**
 * @var array $parameters
 */

?>

<div>
	<div class="form-row">
		<div class="form-group col-md-12">
			<div class="form-control-checkbox">
				<label for="service_settings_custom_only_visible_to_staff"><?php echo bkntc__( 'Only visible to staff' ); ?>:</label>
				<div class="fs_onoffswitch">
					<input type="checkbox" class="fs_onoffswitch-checkbox" id="service_settings_custom_only_visible_to_staff" <?php echo $parameters[ 'only_visible_to_staff' ] ? 'checked' : ''; ?>>
					<label class="fs_onoffswitch-label" for="service_settings_custom_only_visible_to_staff"></label>
				</div>
			</div>
		</div>
	</div>

    <div class="form-row">
        <div class="form-group col-md-12">
            <div class="form-control-checkbox">
                <label for="service_settings_custom_payment_methods_enabled"><?php echo bkntc__( 'Set service specific payment methods' ); ?>:</label>
                <div class="fs_onoffswitch">
                    <input type="checkbox" class="fs_onoffswitch-checkbox" id="service_settings_custom_payment_methods_enabled" <?php echo $parameters[ 'custom_payment_methods_enabled' ] ? 'checked' : ''; ?>>
                    <label class="fs_onoffswitch-label" for="service_settings_custom_payment_methods_enabled"></label>
                </div>
            </div>
        </div>
    </div>

    <div id="serviceCustomPaymentMethodsContainer" class="form-row">
        <div class="form-group col-md-12">
            <label for="service_settings_custom_payment_methods">
                <?php echo bkntc__( 'Payment methods' ); ?>&nbsp;<span class="required-star">*</span>
            </label>
            <select id="service_settings_custom_payment_methods" class="form-control" multiple="multiple">
                <?php foreach ( PaymentGatewayService::getInstalledGatewayNames() as $paymentGateway ): ?>
                    <option value="<?php echo htmlspecialchars( PaymentGatewayService::find( $paymentGateway )->getSlug() ); ?>" <?php echo in_array( PaymentGatewayService::find( $paymentGateway )->getSlug(), $parameters[ 'custom_payment_methods' ] ) ? 'selected' : ''; ?>><?php echo htmlspecialchars( PaymentGatewayService::find( $paymentGateway )->getTitle() ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-12">
            <label for="input_min_time_req_prior_booking"><?php echo bkntc__('Minimum time requirement prior to booking')?>:</label>
            <select class="form-control" id="input_min_time_req_prior_booking">

                <?php
                    $minimumTimeRequiredPriorBooking        = Helper::getMinTimeRequiredPriorBooking( $parameters[ 'id' ] );
                    $defaultMinimumTimeRequiredPriorBooking = Helper::getMinTimeRequiredPriorBooking();
                ?>

                <option value="0" <?php echo $minimumTimeRequiredPriorBooking == '0' ? ' selected' :'' ?> ><?php echo bkntc__( 'Disabled' ); echo '0' == $defaultMinimumTimeRequiredPriorBooking ?  ' ( '. bkntc__( 'Default' ) . ' )' : ''; ?></option>
                <?php foreach ( Helper::timeslotsAsMinutes() as $minute ): ?>
                    <option value="<?php echo $minute ?>"<?php echo $minimumTimeRequiredPriorBooking == $minute ? ' selected':'' ?> ><?php echo Helper::secFormat($minute * 60 ); echo $minute == $defaultMinimumTimeRequiredPriorBooking ?  ' ( '. bkntc__( 'Default' ) . ' )' : ''; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-12">
            <label for="input_available_days_for_booking"><?php echo bkntc__('Limited booking days')?>:</label>
            <input type="number" class="form-control" id="input_available_days_for_booking" min="0" value="<?php echo ( Service::getData( $parameters[ 'id' ], 'available_days_for_booking' ) ) ?? 365 ?>">
        </div>
    </div>
</div>


