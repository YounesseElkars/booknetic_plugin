<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\UI\TabUI;

/**
 * @var mixed $parameters
 */

?>
<div id="booknetic_settings_area">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/payment_settings.css', 'Settings')?>">
	<script type="application/javascript" src="<?php echo Helper::assets('js/payment_settings.js', 'Settings')?>"></script>

	<div class="actions_panel clearfix">
		<button type="button" class="btn btn-lg btn-success settings-save-btn float-right"><i class="fa fa-check pr-2"></i> <?php echo bkntc__('SAVE CHANGES')?></button>
	</div>

	<div class="settings-light-portlet">
		<div class="ms-title">
			<?php echo bkntc__('Payments')?>
		</div>
		<div class="ms-content">

			<form class="position-relative">

				<div class="form-row">
					<div class="form-group col-md-3">
						<label for="input_currency"><?php echo bkntc__('Currency')?>:</label>
						<select class="form-control" id="input_currency">
							<?php
							foreach ( $parameters['currencies'] AS $key => $currency )
							{
								echo '<option data-symbol="' . htmlspecialchars($currency['symbol']) . '" value="' . htmlspecialchars($key) . '"' . ( $key == Helper::getOption('currency', 'USD') ? ' selected' : '' ) . '>' . htmlspecialchars($currency['name'] . ' ( '. $currency['symbol'] . ' )') . '</option>';
							}
							?>
						</select>
					</div>
					<div class="form-group col-md-3">
						<label for="input_currency_symbol"><?php echo bkntc__('Currency symbol')?>:</label>
						<input class="form-control" id="input_currency_symbol" value="<?php echo Helper::getOption('currency_symbol', Helper::currencySymbol())?>" maxlength="20">
					</div>
					<div class="form-group col-md-6">
						<label for="input_currency_format"><?php echo bkntc__('Currency format')?>:</label>
						<select class="form-control" id="input_currency_format">
							<option value="1"<?php echo Helper::getOption('currency_format', '1')=='1' ? ' selected':''?>><?php echo $parameters['currency']?>100</option>
							<option value="2"<?php echo Helper::getOption('currency_format', '1')=='2' ? ' selected':''?>><?php echo $parameters['currency']?> 100</option>
							<option value="3"<?php echo Helper::getOption('currency_format', '1')=='3' ? ' selected':''?>>100<?php echo $parameters['currency']?></option>
							<option value="4"<?php echo Helper::getOption('currency_format', '1')=='4' ? ' selected':''?>>100 <?php echo $parameters['currency']?></option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="input_price_number_format"><?php echo bkntc__('Price number format')?>:</label>
						<select class="form-control" id="input_price_number_format">
							<option value="1"<?php echo Helper::getOption('price_number_format', '1')=='1' ? ' selected':''?>>45 000.00</option>
							<option value="2"<?php echo Helper::getOption('price_number_format', '1')=='2' ? ' selected':''?>>45,000.00</option>
							<option value="3"<?php echo Helper::getOption('price_number_format', '1')=='3' ? ' selected':''?>>45 000,00</option>
							<option value="4"<?php echo Helper::getOption('price_number_format', '1')=='4' ? ' selected':''?>>45.000,00</option>
							<option value="5"<?php echo Helper::getOption('price_number_format', '1')=='5' ? ' selected':''?>>45’000.00</option>
						</select>
					</div>
					<div class="form-group col-md-6">
						<label for="input_price_number_of_decimals"><?php echo bkntc__('Price number of decimals')?>:</label>
						<select class="form-control" id="input_price_number_of_decimals">
							<option value="0"<?php echo Helper::getOption('price_number_of_decimals', '2')=='0' ? ' selected':''?>>100</option>
							<option value="1"<?php echo Helper::getOption('price_number_of_decimals', '2')=='1' ? ' selected':''?>>100.0</option>
							<option value="2"<?php echo Helper::getOption('price_number_of_decimals', '2')=='2' ? ' selected':''?>>100.00</option>
							<option value="3"<?php echo Helper::getOption('price_number_of_decimals', '2')=='3' ? ' selected':''?>>100.000</option>
							<option value="4"<?php echo Helper::getOption('price_number_of_decimals', '2')=='4' ? ' selected':''?>>100.0000</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="input_max_time_limit_for_payment"><?php echo bkntc__('How long to wait for payment')?>: <i class="far fa-question-circle do_tooltip" data-content="<?php echo bkntc__('Newly booked appointment default status will be "Waiting for payment" in the defined timeframe.')?>"></i></label>
						<select class="form-control" id="input_max_time_limit_for_payment">
							<?php
							foreach ( [10,30,60,1440,10080,43200] AS $minute )
							{
								?>
								<option value="<?php echo $minute?>"<?php echo Helper::getOption('max_time_limit_for_payment', '10')==$minute ? ' selected':''?>><?php echo Helper::secFormat($minute*60)?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="form-group col-md-6">
						<label>&nbsp;</label>
						<div class="form-control-checkbox">
							<label for="input_deposit_can_pay_full_amount"><?php echo bkntc__('Customer can pay full amount')?>:</label>
							<div class="fs_onoffswitch">
								<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_deposit_can_pay_full_amount"<?php echo Helper::getOption('deposit_can_pay_full_amount', 'on')=='on'?' checked':''?>>
								<label class="fs_onoffswitch-label" for="input_deposit_can_pay_full_amount"></label>
							</div>
						</div>
					</div>
				</div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="input_successful_payment_status"><?php echo bkntc__('Successful payment booking status')?>:</label>
                        <select class="form-control" id="input_successful_payment_status">
                            <option value=""></option>
                            <?php foreach (Helper::getAppointmentStatuses() as $k => $status) {
                                echo '<option value="' . $k . '"' . ($k == Helper::getOption('successful_payment_status') ? ' selected' : '') . '>' . $status['title'] . '</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="input_failed_payment_status"><?php echo bkntc__('Failed payment booking status')?>:</label>
                        <select class="form-control" id="input_failed_payment_status">
                            <option value=""></option>
                            <?php foreach (Helper::getAppointmentStatuses() as $k => $status) {
                                echo '<option value="' . $k . '"' . ($k == Helper::getOption('failed_payment_status') ? ' selected' : '') . '>' . $status['title'] . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>

                <?php
                foreach ( TabUI::get('payment_settings')->getSubItems() as $item )
                {
                    echo $item->getContent();
                }
                ?>

			</form>

		</div>
	</div>
</div>