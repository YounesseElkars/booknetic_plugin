<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

?>
<div class="booknetic_recurring_div">

	<div class="booknetic_recurring_div_title"><?php echo bkntc__('Daily')?></div>
	<div class="booknetic_dashed_border booknetic_recurring_div_c booknetic_recurring_div_padding">
		<div class="form-row">
			<div class="form-group col-md-6">
				<label for="booknetic_daily_recurring_frequency"><?php echo bkntc__('Every')?></label>
				<div class="booknetic_inner_addon booknetic_left_addon booknetic_right_addon">
					<img src="<?php echo Helper::icon('calendar.svg')?>"/>
					<input type="text" class="form-control" id="booknetic_daily_recurring_frequency" value="1">
					<i class="booknetic_days_txt"><?php echo bkntc__('DAYS')?></i>
				</div>
			</div>
			<div class="form-group col-md-6<?php echo $parameters['date_based'] ? ' booknetic_hidden' : '' ?>">
				<label for="booknetic_daily_time"><?php echo bkntc__('Time')?></label>
				<div class="booknetic_inner_addon booknetic_left_addon">
					<img src="<?php echo Helper::icon('time.svg')?>"/>
					<select class="form-control" id="booknetic_daily_time">
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
				<input type="text" class="form-control" id="booknetic_recurring_start" data-apply-min="true" value="<?php echo  Date::datee("+" . Helper::getMinTimeRequiredPriorBooking( $parameters[ 'sid' ] ) . ' minutes')?>">
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


