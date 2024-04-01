<?php

use BookneticApp\Backend\Appointments\Helpers\AppointmentRequestData;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Common\PaymentGatewayService;

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

?>
<div class="booknetic_confirm_container">

    <?php if ( $parameters[ 'has_duplicate_booking' ] ): ?>
    <div class="booknetic_confirm_date_time booknetic_portlet booknetic_customer_has_same_timeslot_portlet">
        <span>
            <?php echo bkntc__( 'This booking will be duplicate as you already have an appointment for the same timeslot' ) ?>
        </span>
    </div>
    <?php endif; ?>

    <?php if(Helper::getOption('show_step_cart', 'off') == 'off'): ?>
    <div class="booknetic_confirm_date_time booknetic_portlet <?php echo $parameters['hide_confirm_step'] ? 'booknetic_hidden' : ''?>">

        <div>
            <span class="booknetic_text_primary"><?php echo bkntc__('Date & Time')?>:</span>
            <span><?php echo $parameters['appointmentData']->getDateTimeView()?></span>
        </div>

        <?php if( Helper::getOption('show_step_staff', 'on') != 'off' ): ?>
            <div>
                <span class="booknetic_text_primary"><?php echo bkntc__('Staff')?>:</span>
                <span><?php echo htmlspecialchars($parameters['appointmentData']->staffInf->name)?></span>
            </div>
        <?php endif; ?>

        <?php if( Helper::getOption('show_step_location', 'on') != 'off' ): ?>
            <div>
                <span class="booknetic_text_primary"><?php echo bkntc__('Location')?>:</span>
                <span><?php echo htmlspecialchars($parameters['appointmentData']->locationInf->name)?></span>
            </div>
        <?php endif; ?>

        <?php if( Helper::getOption('show_step_service', 'on') != 'off' ): ?>
            <div>
                <span class="booknetic_text_primary"><?php echo bkntc__('Service')?>:</span>
                <span><?php echo htmlspecialchars($parameters['appointmentData']->serviceInf->name)?></span>
            </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>
    <div class="booknetic_confirm_step_body <?php echo $parameters['hide_confirm_step'] ? 'booknetic_hidden' : ''?>">

	<div class="booknetic_confirm_sum_body<?php echo ($parameters['hide_payments'] && !$parameters['hide_price_section'] ? ' booknetic_confirm_sum_body_full_width' : '') . ($parameters['hide_price_section'] ? ' booknetic_hidden' : '');?>">
		<div class="booknetic_portlet booknetic_portlet_cols">
			<div class="booknetic_portlet_content">
				<div class="booknetic_prices_box">
					<?php echo $parameters['appointment_requests']->getPricesHTML(true);?>
				</div>

				<div class="booknetic_show_balance"></div>
			</div>

			<div class="booknetic_panel_footer">

			</div>

			<div class="booknetic_confirm_sum_price">
				<div><?php echo bkntc__('Total price')?></div>
				<div class="booknetic_sum_price"><?php echo Helper::price( $parameters['appointment_requests']->getSubTotal( true ) )?></div>
			</div>
		</div>
	</div>

	<div class="booknetic_confirm_deposit_body<?php echo ($parameters['hide_price_section'] && !$parameters['hide_payments'] ? ' booknetic_confirm_deposit_body_full_width' : '') . ($parameters['hide_payments'] ? ' booknetic_hidden' : '');?>">

		<div class="booknetic_portlet booknetic_payment_methods_container">
			<div class="booknetic_payment_methods">
                <?php
                $first_is_local_method = false;
                $serviceCustomPaymentMethods = $parameters['custom_payment_methods'];

                if ( $parameters[ 'appointment_requests' ]->getSubTotal( true ) <= 0 )
                {
                    $serviceCustomPaymentMethods = [ 'local' ];
                }

                $gateways_order = Helper::getOption('payment_gateways_order', 'local');
                $gateways_order = explode(',', $gateways_order);
                $orderedPaymentGateways = [];
                $unOrderedPaymentGateways = [];

                foreach ($serviceCustomPaymentMethods as $gateway)
                {
                    if( ( $index = array_search( $gateway,$gateways_order ) ) || ( $index !== false ) )
                    {
                        $orderedPaymentGateways[$index] = $gateway;
                    }else{
                        $unOrderedPaymentGateways[] = $gateway;
                    }
                }
                ksort($orderedPaymentGateways);
                $orderedPaymentGateways = array_merge($orderedPaymentGateways,$unOrderedPaymentGateways);
                $i = 0;

                foreach ( $orderedPaymentGateways as $slug )
                {
                    $gateway = PaymentGatewayService::find( $slug );

                    if ( ! empty( $gateway ) )
                    {
                        ?>
                        <div class="booknetic_payment_method <?php echo $i === 0 ? 'booknetic_payment_method_selected' : ''; ?>" data-payment-type="<?php echo $slug; ?>">
                            <img src="<?php echo $gateway->getIcon(); ?>" alt="<?php echo $gateway->getTitle(); ?>">
                            <span>
                                <?php echo $gateway->getTitle(); ?>
                            </span>
                        </div>
                        <?php
                        if ( $i === 0 && $slug === 'local' )
                        {
                            $first_is_local_method = true;
                        }

                        $i++;
                    }
                }
                ?>
			</div>

            <div class="booknetic_payment_methods_footer">
                <?php if( $parameters['has_deposit'] ): ?>
                    <div class="booknetic_deposit_price booknetic_hide_on_local <?php echo $first_is_local_method ? 'booknetic_hidden' : '';?>">
                        <div><?php echo bkntc__('Deposit')?>:</div>
                        <div class="booknetic_deposit_amount_txt"><?php echo Helper::price( $parameters['deposit_price'] )?></div>
                    </div>
                <?php endif; ?>

			<?php if( Helper::getOption('deposit_can_pay_full_amount', 'on') == 'on' && $parameters['has_deposit'] ): ?>
				<div class="booknetic_deposit_radios booknetic_hide_on_local <?php echo $first_is_local_method ? 'booknetic_hidden' : '';?>">
					<div><input type="radio" id="input_deposit_2" name="input_deposit" value="1" checked><label for="input_deposit_2"><?php echo bkntc__('Deposit')?></label></div>
					<div><input type="radio" id="input_deposit_1" name="input_deposit" value="0"><label for="input_deposit_1"><?php echo bkntc__('Full amount')?></label></div>
				</div>
			<?php endif; ?>
				</div>

		</div>

	</div>

</div>
</div>