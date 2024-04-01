<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

$weekStartsOn = Helper::getOption('week_starts_on', 'sunday') == 'monday' ? 'monday' : 'sunday';
?>

<div class="booknetic_recurring_div">

	<div class="booknetic_recurring_div_title"><?php echo bkntc__('Days of week')?></div>
	<div class="booknetic_dashed_border booknetic_recurring_div_c">
		<div class="booknetic_clearfix">
			<?php
			if( $weekStartsOn == 'sunday' )
			{
				?>
				<div class="booknetic_day_of_week_box">
					<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_7">
					<label for="booknetic_day_of_week_checkbox_7"><?php echo bkntc__('Sun')?></label>
				</div>
				<?php
			}
			?>
			<div class="booknetic_day_of_week_box">
				<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_1"/>
				<label for="booknetic_day_of_week_checkbox_1"><?php echo bkntc__('Mon')?></label>
			</div>

			<div class="booknetic_day_of_week_box">
				<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_2">
				<label for="booknetic_day_of_week_checkbox_2"><?php echo bkntc__('Tue')?></label>
			</div>

			<div class="booknetic_day_of_week_box">
				<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_3">
				<label for="booknetic_day_of_week_checkbox_3"><?php echo bkntc__('Wed')?></label>
			</div>

			<div class="booknetic_day_of_week_box">
				<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_4">
				<label for="booknetic_day_of_week_checkbox_4"><?php echo bkntc__('Thu')?></label>
			</div>

			<div class="booknetic_day_of_week_box">
				<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_5">
				<label for="booknetic_day_of_week_checkbox_5"><?php echo bkntc__('Fri')?></label>
			</div>

			<div class="booknetic_day_of_week_box">
				<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_6">
				<label for="booknetic_day_of_week_checkbox_6"><?php echo bkntc__('Sat')?></label>
			</div>

			<?php
			if( $weekStartsOn == 'monday' )
			{
				?>
				<div class="booknetic_day_of_week_box">
					<input type="checkbox" class="booknetic_day_of_week_checkbox" id="booknetic_day_of_week_checkbox_7">
					<label for="booknetic_day_of_week_checkbox_7"><?php echo bkntc__('Sun')?></label>
				</div>
				<?php
			}
			?>

		</div>
		<div class="booknetic_times_days_of_week_area">

			<div class="form-row booknetic_hidden" data-day="1">
				<div class="form-group col-md-3">
					<div class="form-control-plaintext"><?php echo bkntc__('Monday')?></div>
				</div>
				<div class="form-group col-md-4">
					<div class="booknetic_inner_addon booknetic_left_addon">
						<img src="<?php echo Helper::icon('time.svg')?>"/>
						<select class="form-control booknetic_wd_input_time" id="booknetic_time_wd_1"></select>
					</div>
				</div>
				<div class="col-md-2 booknetic_copy_time_to_all">
					<img src="<?php echo Helper::icon('copy-to-all.svg', 'front-end')?>">
				</div>
			</div>

			<div class="form-row booknetic_hidden" data-day="2">
				<div class="form-group col-md-3">
					<div class="form-control-plaintext"><?php echo bkntc__('Tuesday')?></div>
				</div>
				<div class="form-group col-md-4">
					<div class="booknetic_inner_addon booknetic_left_addon">
						<img src="<?php echo Helper::icon('time.svg')?>"/>
						<select class="form-control booknetic_wd_input_time" id="booknetic_time_wd_2"></select>
					</div>
				</div>
				<div class="col-md-2 booknetic_copy_time_to_all">
					<img src="<?php echo Helper::icon('copy-to-all.svg', 'front-end')?>">
				</div>
			</div>

			<div class="form-row booknetic_hidden" data-day="3">
				<div class="form-group col-md-3">
					<div class="form-control-plaintext"><?php echo bkntc__('Wednesday')?></div>
				</div>
				<div class="form-group col-md-4">
					<div class="booknetic_inner_addon booknetic_left_addon">
						<img src="<?php echo Helper::icon('time.svg')?>"/>
						<select class="form-control booknetic_wd_input_time" id="booknetic_time_wd_3"></select>
					</div>
				</div>
				<div class="col-md-2 booknetic_copy_time_to_all">
					<img src="<?php echo Helper::icon('copy-to-all.svg', 'front-end')?>">
				</div>
			</div>

			<div class="form-row booknetic_hidden" data-day="4">
				<div class="form-group col-md-3">
					<div class="form-control-plaintext"><?php echo bkntc__('Thursday')?></div>
				</div>
				<div class="form-group col-md-4">
					<div class="booknetic_inner_addon booknetic_left_addon">
						<img src="<?php echo Helper::icon('time.svg')?>"/>
						<select class="form-control booknetic_wd_input_time" id="booknetic_time_wd_4"></select>
					</div>
				</div>
				<div class="col-md-2 booknetic_copy_time_to_all">
					<img src="<?php echo Helper::icon('copy-to-all.svg', 'front-end')?>">
				</div>
			</div>

			<div class="form-row booknetic_hidden" data-day="5">
				<div class="form-group col-md-3">
					<div class="form-control-plaintext"><?php echo bkntc__('Friday')?></div>
				</div>
				<div class="form-group col-md-4">
					<div class="booknetic_inner_addon booknetic_left_addon">
						<img src="<?php echo Helper::icon('time.svg')?>"/>
						<select class="form-control booknetic_wd_input_time" id="booknetic_time_wd_5"></select>
					</div>
				</div>
				<div class="col-md-2 booknetic_copy_time_to_all">
					<img src="<?php echo Helper::icon('copy-to-all.svg', 'front-end')?>">
				</div>
			</div>

			<div class="form-row booknetic_hidden" data-day="6">
				<div class="form-group col-md-3">
					<div class="form-control-plaintext"><?php echo bkntc__('Saturday')?></div>
				</div>
				<div class="form-group col-md-4">
					<div class="booknetic_inner_addon booknetic_left_addon">
						<img src="<?php echo Helper::icon('time.svg')?>"/>
						<select class="form-control booknetic_wd_input_time" id="booknetic_time_wd_6"></select>
					</div>
				</div>
				<div class="col-md-2 booknetic_copy_time_to_all">
					<img src="<?php echo Helper::icon('copy-to-all.svg', 'front-end')?>">
				</div>
			</div>

			<div class="form-row booknetic_hidden" data-day="7">
				<div class="form-group col-md-3">
					<div class="form-control-plaintext"><?php echo bkntc__('Sunday')?></div>
				</div>
				<div class="form-group col-md-4">
					<div class="booknetic_inner_addon booknetic_left_addon">
						<img src="<?php echo Helper::icon('time.svg')?>"/>
						<select class="form-control booknetic_wd_input_time" id="booknetic_time_wd_7"></select>
					</div>
				</div>
				<div class="col-md-2 booknetic_copy_time_to_all">
					<img src="<?php echo Helper::icon('copy-to-all.svg', 'front-end')?>">
				</div>
			</div>

		</div>
	</div>

	<div class="form-row">
		<div class="form-group col-md-4">
			<label for="booknetic_recurring_start"><?php echo bkntc__('Start date')?></label>
			<div class="booknetic_inner_addon booknetic_left_addon">
				<img src="<?php echo Helper::icon('calendar.svg')?>"/>
				<input type="text" class="form-control" data-apply-min="true" id="booknetic_recurring_start" value="<?php echo $parameters[ 'recurring_start_date' ] ?>">
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
