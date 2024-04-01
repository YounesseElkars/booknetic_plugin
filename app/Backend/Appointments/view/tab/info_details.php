<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var array $parameters
 */
?>

<div class="form-row">
    <div class="form-group col-md-4">
        <label><?php echo bkntc__('Location')?></label>
        <div class="form-control-plaintext"><?php echo htmlspecialchars( $parameters['info']['location_name'] )?></div>
    </div>
    <div class="form-group col-md-4">
        <label><?php echo bkntc__('Service')?></label>
        <div class="form-control-plaintext"><?php echo htmlspecialchars( $parameters['info']['service_name'] )?></div>
    </div>
    <div class="form-group col-md-4">
        <label><?php echo bkntc__('Date, time')?></label>
        <div class="form-control-plaintext"><?php echo ($parameters['info']['ends_at'] - $parameters['info']['starts_at']) >= 24*60*60 ? Date::datee( $parameters['info']['starts_at'] ) : (Date::dateTime( $parameters['info']['starts_at'] ) . ' - ' . Date::time(  $parameters['info']['ends_at'])  )?></div>
    </div>
</div>
<div class="form-row">
	<div class="form-group col-md-12">
		<label><?php echo bkntc__('Note')?> </label>
		<div class="form-control-plaintext">
			<?php echo empty($parameters['info']->note) ? '-' : htmlspecialchars($parameters['info']->note)?>
		</div>
	</div>
</div>

<hr/>

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="text-primary"><?php echo bkntc__('Staff')?></label>
        <div class="form-control-plaintext"><?php echo Helper::profileCard($parameters['info']['staff_name'] , $parameters['info']['staff_profile_image'], $parameters['info']['staff_email'], 'Staff')?></div>
    </div>

    <div class="form-group col-md-6">
        <label class="text-success"><?php echo bkntc__('Customer')?></label>
        <div class="form-control-plaintext">
            <div class="fs_data_table_wrapper">
                <?php
                $statuses = Helper::getAppointmentStatuses();
                    $info = $parameters['info'];
                    $status = $statuses[$info['status']];
                    echo '<div class="per-customer-div cursor-pointer" data-load-modal="customers.info" data-parameter-id="'.(int)$info['customer_id'].'">';
                    echo Helper::profileCard($info['customer_first_name'] . ' ' . $info['customer_last_name'], $info['customer_profile_image'], $info['customer_email'], 'Customers');
                    echo '<div class="appointment-status-icon ml-3" style="background-color: ' . htmlspecialchars( $status[ 'color' ] ) . '2b">
                        <i style="color: ' . htmlspecialchars( $status[ 'color' ] ) . '" class="' . htmlspecialchars( $status[ 'icon' ] ) .  '"></i>
                    </div>';
                    echo '<span class="num_of_customers_span"><i class="fa fa-user"></i> ' . (int)$info['weight'] . '</span>';

                    //doit: echo '<span>' . $customer['billing_full_name'] . (empty($customer['billing_phone']) ? '' : ' ('.$customer['billing_phone'].')') . '</span>';
                    echo '</div>';
                ?>
            </div>
        </div>
    </div>
</div>

<?php if( !empty($parameters['paymentGateways'])): ?>
<div class="form-row">
    <div class="form-group col-md-12">
        <label><?php echo bkntc__('Create Payment Link')?> </label>

        <div class="">
            <div class="form-row ">
                <div class="col-md-6">
                    <div class="input-group">
                        <select class="form-control" id="appointment_info_payment_gateway">
                            <?php foreach ($parameters['paymentGateways'] as $paymentGateway): ?>
                                <option value="<?php echo $paymentGateway ?>"><?php echo PaymentGatewayService::find($paymentGateway)->getTitle() ?></option>
                            <?php endforeach; ?>
                        </select>

                    </div>
                </div>
                <div class="col-md-6 d-flex">
                <span>
                    <button data-appointment-id="<?php echo $parameters['info']['id'] ?>" id="bkntc_create_payment_link" class="btn btn-lg btn-primary"  type="button" >
                        <?php echo bkntc__('Create Link') ?>
                    </button>
                </span>
                </div>
            </div>

        </div>
    </div>
</div>
<div style="width: 100%; display: none" class="bkntc_payment_link_container" >
    <div class="payment_link" style="padding:10px;overflow-wrap: anywhere;background-color: #f3f3f3">
    </div>
    <button class="btn btn-primary copy_url_payment_link" type="button" style=""><?php echo bkntc__('COPY URL') ?></button>
</div>
<?php endif; ?>