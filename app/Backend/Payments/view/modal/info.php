<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var $parameters AppointmentSmartObject[]
 * @var mixed $_mn
 */

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/info.css', 'Payments')?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/info.js', 'Payments')?>" id="info_modal_JS" data-mn="<?php echo $_mn?>" data-payment-id="<?php echo (int)$parameters['info']->getId()?>"></script>

<div class="fs-modal-title">
	<div class="title-icon badge-lg badge-purple"><i class="fa fa-credit-card "></i></div>
	<div class="title-text"><?php echo bkntc__('Payment info')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">

		<div class="bordered-light-portlet">
			<div class="form-row">
				<div class="col-md-3">
					<label><?php echo bkntc__('Staff:')?></label>
					<div class="form-control-plaintext text-primary">
						<?php echo htmlspecialchars( $parameters['info']->getStaffInf()->name )?>
					</div>
				</div>
				<div class="col-md-3">
					<label><?php echo bkntc__('Location:')?></label>
					<div class="form-control-plaintext">
						<?php echo htmlspecialchars( $parameters['info']->getLocationInf()->name )?>
					</div>
				</div>
				<div class="col-md-3">
					<label><?php echo bkntc__('Service:')?></label>
					<div class="form-control-plaintext">
						<?php echo htmlspecialchars( $parameters['info']->getServiceInf()->name )?>
					</div>
				</div>
				<div class="col-md-3">
					<label><?php echo bkntc__('Date, time:')?></label>
					<div class="form-control-plaintext">
                        <?php echo Date::dateTime( $parameters['info']->getAppointmentInfo()->starts_at ); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="form-row mt-4">
			<div class="form-group col-md-12">
				<div class="fs_data_table_wrapper">
					<table class="table-gray-2 dashed-border">
						<thead>
						<tr>
							<th><?php echo bkntc__('CUSTOMER')?></th>
							<th><?php echo bkntc__('METHOD')?></th>
							<th><?php echo bkntc__('STATUS')?></th>
						</tr>
						</thead>
						<tbody>
						<?php

						$status = htmlspecialchars( $parameters['info']->getInfo()->payment_status );

						echo '<tr data-customer-id="' . (int)$parameters['info']->getInfo()->customer_id . '" data-id="' . (int)$parameters['info']->getId() . '">';
						echo '<td>' . Helper::profileCard($parameters['info']->getCustomerInf()->full_name, $parameters['info']->getCustomerInf()->profile_image, $parameters['info']->getCustomerInf()->email, 'Customers') . '</td>';
						echo '<td>' . Helper::paymentMethod( $parameters['info']->getInfo()->payment_method ) . '</td>';
						echo '<td><span class="payment-status-' . $status . '"></span></td>';
						echo '</tr>';
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="checkout-details">
			<h6><?php echo bkntc__('Payment details')?></h6>
			<div class="checkout-details--items">
				<?php foreach ( $parameters['info']->getPrices() AS $price ):?>
					<div>
						<span><?php echo htmlspecialchars($price->name)?></span>
						<span><?php echo Helper::price( $price->price )?></span>
					</div>
				<?php endforeach;?>
			</div>
			<div class="checkout-details--info">
				<div>
					<span><?php echo bkntc__('Total')?></span>
					<span><?php echo Helper::price( $parameters['info']->getTotalAmount() )?></span>
				</div>
				<div class="checkout-details--info-paid">
					<span><?php echo bkntc__('Paid')?></span>
					<span><?php echo Helper::price( $parameters['info']->getRealPaidAmount() )?></span>
				</div>
				<div class="checkout-details--info-due">
					<span><?php echo bkntc__('Due')?></span>
					<span><?php echo Helper::price( $parameters['info']->getDueAmount() )?></span>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="fs-modal-footer">

	<?php
	if( $parameters['info']->getDueAmount() > 0 )
	{
		?>
		<button type="button" class="btn btn-lg btn-success complete-payment"><?php echo bkntc__('COMPLETE PAYMENT')?></button>
		<?php
	}
	?>

	<button type="button" class="btn btn-lg btn-primary edit-btn" data-load-modal="payments.edit_payment" data-parameter-payment="<?php echo (int)$parameters['info']->getId()?>" data-parameter-mn2="<?php echo $_mn?>"><?php echo bkntc__('EDIT')?></button>
	<button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
</div>
