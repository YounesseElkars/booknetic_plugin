<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */


$canChooseMultipleAppointmentsPerMonth = $parameters[ 'service_info' ][ 'repeat_frequency' ] > 1;

?>

<div class="booknetic_recurring_div">
	<div class="booknetic_recurring_div_title"><?php echo bkntc__('Days of week')?></div>
	<div class="booknetic_dashed_border booknetic_recurring_div_c booknetic_recurring_div_padding">

		<div class="form-row">
			<div class="form-group col-md-4">
				<label for="booknetic_monthly_recurring_type"><?php echo bkntc__('On')?></label>
				<select class="form-control" id="booknetic_monthly_recurring_type" <?php echo $canChooseMultipleAppointmentsPerMonth ? 'disabled' : '' ?> >
					<option value="specific_day"><?php echo bkntc__('Specific day')?></option>

                    <?php if ( ! $canChooseMultipleAppointmentsPerMonth ): ?>
                        <option value="1"><?php echo bkntc__('First')?></option>
                        <option value="2"><?php echo bkntc__('Second')?></option>
                        <option value="3"><?php echo bkntc__('Third')?></option>
                        <option value="4"><?php echo bkntc__('Fourth')?></option>
                        <option value="last"><?php echo bkntc__('Last')?></option>
                    <?php endif; ?>

				</select>
			</div>
			<div class="form-group col-md-4">
				<label for="booknetic_monthly_recurring_day_of_week">&nbsp;</label>
				<select class="form-control" id="booknetic_monthly_recurring_day_of_week">
					<option value="1">1. <?php echo bkntc__('Monday')?></option>
					<option value="2">2. <?php echo bkntc__('Tuesday')?></option>
					<option value="3">3. <?php echo bkntc__('Wednesday')?></option>
					<option value="4">4. <?php echo bkntc__('Thursday')?></option>
					<option value="5">5. <?php echo bkntc__('Friday')?></option>
					<option value="6">6. <?php echo bkntc__('Saturday')?></option>
					<option value="7">7. <?php echo bkntc__('Sunday')?></option>
				</select>
				<select class="form-control" id="booknetic_monthly_recurring_day_of_month" multiple>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
					<option value="24">24</option>
					<option value="25">25</option>
					<option value="26">26</option>
					<option value="27">27</option>
					<option value="28">28</option>
					<option value="29">29</option>
					<option value="30">30</option>
					<option value="31">31</option>
				</select>
			</div>
			<div class="form-group col-md-4<?php echo $parameters['date_based'] ? ' booknetic_hidden' : '' ?>">
				<label for="booknetic_monthly_time"><?php echo bkntc__('Time')?></label>
				<div class="booknetic_inner_addon booknetic_left_addon">
					<img src="<?php echo Helper::icon('time.svg')?>"/>
					<select class="form-control" id="booknetic_monthly_time">
						<?php
						if( $parameters['date_based'] )
						{
							echo '<option selected>00:00</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>

	</div>

	<div class="form-row">
		<div class="form-group col-md-4">
			<label for="booknetic_recurring_start"><?php echo bkntc__('Start date')?></label>
			<div class="booknetic_inner_addon booknetic_left_addon">
				<img src="<?php echo Helper::icon('calendar.svg')?>"/>
				<input type="text" class="form-control" data-apply-min="true" id="booknetic_recurring_start" value="<?php echo  Date::datee("+" . Helper::getMinTimeRequiredPriorBooking( $parameters[ 'sid' ] ) . ' minutes')?>">
			</div>
		</div>
		<div class="form-group col-md-4">
			<label for="booknetic_recurring_end"><?php echo bkntc__('End date')?></label>
			<div class="booknetic_inner_addon booknetic_left_addon">
				<img src="<?php echo Helper::icon('calendar.svg')?>"/>
				<input type="text" class="form-control" id="booknetic_recurring_end">
			</div>
		</div>
		<div class="form-group col-md-4">
			<label for="booknetic_recurring_times"><?php echo bkntc__('Times')?></label>
			<input type="text" class="form-control" id="booknetic_recurring_times">
		</div>
	</div>

</div>
