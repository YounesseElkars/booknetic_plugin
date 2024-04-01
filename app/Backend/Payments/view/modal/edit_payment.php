<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Math;

/**
 * @var mixed $parameters
 * @var mixed $_mn
 */

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/edit_payment.css', 'Payments')?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/edit_payment.js', 'Payments')?>" id="add_new_JS_payment1" data-mn="<?php echo $_mn?>" data-mn2="<?php echo $parameters['mn2']?>" data-payment-id="<?php echo $parameters['payment']->getId()?>"></script>

<div class="fs-modal-title">
	<div class="back-icon" data-dismiss="modal"><i class="fa fa-angle-left"></i></div>
	<div class="title-icon"><img src="<?php echo Helper::icon('payment-appointment.svg')?>"></div>
	<div class="title-text"><?php echo bkntc__('Payment')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
		<form>

			<div class="form-row prices-section">
				<?php foreach ( $parameters['payment']->getPrices() AS $price ):?>
				<div class="form-group col-md-12">
					<label for="input_price_<?php echo (int)$price->id?>"><?php echo htmlspecialchars($price->name)?> <span class="required-star">*</span></label>
					<input class="form-control" id="input_price_<?php echo (int)$price->id?>" data-price-id="<?php echo htmlspecialchars($price->unique_key)?>" value="<?php echo Math::floor( $price->price )?>" placeholder="0">
				</div>
				<?php endforeach;?>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="input_paid_amount"><?php echo bkntc__('Paid amount')?> <span class="required-star">*</span></label>
					<input class="form-control" id="input_paid_amount" value="<?php echo Math::floor( $parameters['payment']->getPaidAmount() )?>" placeholder="0">
				</div>

				<div class="form-group col-md-6">
					<label for="input_payment_status"><?php echo bkntc__('Payment status')?> <span class="required-star">*</span></label>
					<select class="form-control" id="input_payment_status">
						<option value="pending"><?php echo bkntc__('Pending')?></option>
						<option value="paid"<?php echo ( $parameters['payment']->getInfo()->payment_status == 'paid' ? ' selected' : '' )?>><?php echo bkntc__('Paid')?></option>
                        <option value="canceled"<?php echo ( $parameters['payment']->getInfo()->payment_status == 'canceled' ? ' selected' : '' )?>><?php echo bkntc__('Canceled')?></option>
                        <option value="not_paid"<?php echo ( $parameters['payment']->getInfo()->payment_status == 'not_paid' ? ' selected' : '' )?>><?php echo bkntc__('Not paid')?></option>
					</select>
				</div>
			</div>

		</form>
	</div>
</div>

<div class="fs-modal-footer">
	<button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('BACK')?></button>
	<button type="button" class="btn btn-lg btn-primary" id="addPaymentButton"><?php echo bkntc__('SAVE')?></button>
</div>
