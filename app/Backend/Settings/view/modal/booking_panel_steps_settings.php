<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\UI\SettingsMenuUI;
use BookneticApp\Providers\UI\TabUI;

/**
 * @var mixed $parameters
 */

$steps = [
	'location'			=>	[
		'title'		=>	bkntc__('Location'),
		'sortable'	=>	true,
		'can_hide'	=>	true,
		'hidden'    =>  Capabilities::tenantCan('locations') == false
	],
	'staff'				=>	[
		'title'		=>	bkntc__('Staff'),
		'sortable'	=>	true,
		'can_hide'	=>	true,
		'hidden'    =>  Capabilities::tenantCan('staff') == false
	],
	'service'			=>	[
		'title'		=>	bkntc__('Service'),
		'sortable'	=>	true,
		'can_hide'	=>	true,
		'hidden'    =>  Capabilities::tenantCan('services') == false
	],
	'service_extras'	=>	[
		'title'		=>	bkntc__('Service Extras'),
		'sortable'	=>	true,
		'can_hide'	=>	true,
		'hidden'    =>  Capabilities::tenantCan('services') == false
	],
	'information'		=>	[
		'title'		=>	bkntc__('Information'),
		'sortable'	=>	true,
		'can_hide'	=>	false,
		'hidden'    =>  false
	],
	'date_time'			=>	[
		'title'		=>	bkntc__('Date & Time'),
		'sortable'	=>	true,
		'can_hide'	=>	false,
		'hidden'    =>  false
	],
	'confirm_details'	=>	[
		'title'		=>	bkntc__('Confirmation'),
		'sortable'	=>	false,
		'can_hide'	=>	true,
		'hidden'    =>  false
	],
	'cart'	=>	[
		'title'		=>	bkntc__('Cart'),
		'sortable'	=>	false,
		'can_hide'	=>	true,
		'hidden'    =>  false
	],
	'finish'			=>	[
		'title'		=>	bkntc__('Finish'),
		'sortable'	=>	false,
		'can_hide'	=>	false,
		'hidden'    =>  false
	]
];
$steps_order = Helper::getBookingStepsOrder();
?>
<div id="booknetic_settings_area">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/booking_panel_steps_settings.css', 'Settings')?>">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/intlTelInput.min.css', 'front-end')?>">
	<script type="application/javascript" src="<?php echo Helper::assets('js/booking_panel_steps_settings.js', 'Settings')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/intlTelInput.min.js', 'front-end')?>"></script>

	<div class="actions_panel clearfix">
		<button type="button" class="btn btn-lg btn-success settings-save-btn float-right"><i class="fa fa-check pr-2"></i> <?php echo bkntc__('SAVE CHANGES')?></button>
	</div>

	<div class="settings-light-portlet">
		<div class="ms-title">
			<?php echo bkntc__('Front-end panels')?>
			<span class="ms-subtitle"><?php echo bkntc__('Steps')?></span>
		</div>
		<div class="ms-content">

			<div class="step_settings_container">
				<div class="step_elements_list">
					<?php
					foreach ( $steps_order AS $step_id )
					{
						if( !isset( $steps[$step_id] ) )
							continue;

						?>
						<div class="step_element<?php echo (!$steps[$step_id]['sortable'] ? ' no_drag_drop' : '') . ($steps[$step_id]['hidden'] ? ' hidden' : '')?>" data-step-id="<?php echo $step_id?>">
							<span class="drag_drop_helper"><img src="<?php echo Helper::icon('drag-default.svg')?>"></span>
							<span><?php echo $steps[$step_id]['title']?></span>
							<?php
							if( $steps[$step_id]['can_hide'] )
							{
								?>
								<div class="step_switch">
									<div class="fs_onoffswitch">
										<input type="checkbox" name="show_step_<?php echo $step_id?>" class="fs_onoffswitch-checkbox green_switch" id="show_step_<?php echo $step_id?>"<?php echo Helper::getOption('show_step_' . $step_id, 'on')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="show_step_<?php echo $step_id?>"></label>
									</div>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					}
					?>
				</div>
				<div class="step_elements_options dashed-border">
					<form id="booking_panel_settings_per_step" class="position-relative">

						<div class="hidden" data-step="location">
							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_hide_address_of_location"><?php echo bkntc__('Hide address of Location')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_address_of_location"<?php echo Helper::getOption('hide_address_of_location', 'off')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_hide_address_of_location"></label>
									</div>
								</div>
							</div>
						</div>

						<div class="hidden" data-step="service">

                            <div class="form-group col-md-12">
                                <div class="form-control-checkbox">
                                    <label for="input_hide_accordion_default"><?php echo bkntc__('Collapse Services under a Category')?>:</label>
                                    <div class="fs_onoffswitch">
                                        <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_accordion_default"<?php echo Helper::getOption('hide_accordion_default', 'off')=='on'?' checked':''?>>
                                        <label class="fs_onoffswitch-label" for="input_hide_accordion_default"></label>
                                    </div>
                                </div>
                            </div>
						</div>

						<div class="hidden" data-step="staff">

							<div class="form-group col-md-12">
								<label for="input_footer_text_staff"><?php echo bkntc__('Footer text per staff')?>:</label>
								<select class="form-control" id="input_footer_text_staff">
									<option value="1"<?php echo Helper::getOption('footer_text_staff', '1')=='1' ? ' selected':''?>><?php echo bkntc__('Show both phone number and emaill address')?></option>
									<option value="2"<?php echo Helper::getOption('footer_text_staff', '1')=='2' ? ' selected':''?>><?php echo bkntc__('Show only Staff email address')?></option>
									<option value="3"<?php echo Helper::getOption('footer_text_staff', '1')=='3' ? ' selected':''?>><?php echo bkntc__('Show only Staff phone number')?></option>
									<option value="4"<?php echo Helper::getOption('footer_text_staff', '1')=='4' ? ' selected':''?>><?php echo bkntc__('Don\'t show both phone number and emaill address')?></option>
								</select>
							</div>

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_any_staff"><?php echo bkntc__('Enable Any staff option')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_any_staff"<?php echo Helper::getOption('any_staff', 'off')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_any_staff"></label>
									</div>
								</div>
							</div>

							<div class="form-group col-md-12 hidden" id="any_staff_selecting_rule">
								<label for="input_any_staff_rule"><?php echo bkntc__('Auto assignment rule')?>:</label>
								<select class="form-control" id="input_any_staff_rule">
									<option value="least_assigned_by_day"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='least_assigned_by_day' ? ' selected':''?>><?php echo bkntc__('Least assigned by the day')?></option>
									<option value="most_assigned_by_day"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='most_assigned_by_day' ? ' selected':''?>><?php echo bkntc__('Most assigned by the day')?></option>
									<option value="least_assigned_by_week"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='least_assigned_by_week' ? ' selected':''?>><?php echo bkntc__('Least assigned by the week')?></option>
									<option value="most_assigned_by_week"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='most_assigned_by_week' ? ' selected':''?>><?php echo bkntc__('Most assigned by the week')?></option>
									<option value="least_assigned_by_month"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='least_assigned_by_month' ? ' selected':''?>><?php echo bkntc__('Least assigned by the month')?></option>
									<option value="most_assigned_by_month"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='most_assigned_by_month' ? ' selected':''?>><?php echo bkntc__('Most assigned by the month')?></option>
									<option value="most_expensive"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='most_expensive' ? ' selected':''?>><?php echo bkntc__('Most expensive')?></option>
									<option value="least_expensive"<?php echo Helper::getOption('any_staff_rule', 'least_assigned_by_day')=='least_expensive' ? ' selected':''?>><?php echo bkntc__('Least expensive')?></option>
								</select>
							</div>

						</div>

						<div class="hidden" data-step="service_extras">

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_skip_extras_step_if_need"><?php echo bkntc__('If a Service does not have an extra skip the step')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_skip_extras_step_if_need"<?php echo Helper::getOption('skip_extras_step_if_need', 'on')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_skip_extras_step_if_need"></label>
									</div>
								</div>
							</div>

                            <div class="form-group col-md-12">
                                <div class="form-control-checkbox">
                                    <label for="input_collapse_service_extras"><?php echo bkntc__('Collapse service extras under a category')?>:</label>
                                    <div class="fs_onoffswitch">
                                        <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_collapse_service_extras"<?php echo Helper::getOption('collapse_service_extras', 'off')=='on'?' checked':''?>>
                                        <label class="fs_onoffswitch-label" for="input_collapse_service_extras"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <div class="form-control-checkbox">
                                    <label for="input_show_all_service_extras"><?php echo bkntc__('Show all the service extras')?>:</label>
                                    <div class="fs_onoffswitch">
                                        <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_show_all_service_extras"<?php echo Helper::getOption('show_all_service_extras', 'off')=='on'?' checked':''?>>
                                        <label class="fs_onoffswitch-label" for="input_show_all_service_extras"></label>
                                    </div>
                                </div>
                            </div>

						</div>

						<div class="hidden" data-step="date_time">

							<div class="form-group col-md-12">
								<label for="input_time_view_type_in_front"><?php echo bkntc__('The time format for the booking form')?>:</label>
								<select class="form-control" id="input_time_view_type_in_front">
									<option value="1"<?php echo Helper::getOption('time_view_type_in_front', '1')=='1' ? ' selected':''?>><?php echo bkntc__('Show both Start and End time (e.g.: 10:00 - 11:00)')?></option>
									<option value="2"<?php echo Helper::getOption('time_view_type_in_front', '1')=='2' ? ' selected':''?>><?php echo bkntc__('Show only Start time (e.g.: 10:00)')?></option>
								</select>
							</div>

                            <div class="form-group col-md-12">
                                <label for="input_booking_panel_default_start_month"><?php echo bkntc__('Start the booking calendar from')?>:</label>
                                <select class="form-control" id="input_booking_panel_default_start_month">
                                    <option value=""<?php echo empty($parameters['default_start_month']) ? '' : ' selected'?>><?php echo bkntc__('Current month')?></option>
                                    <?php $item = 1; foreach ( $parameters['months'] as $month):  ?>
                                        <option value="<?php echo $item ?>"<?php echo $parameters['default_start_month']==$item ? ' selected':''?>><?php echo $month?></option>
                                    <?php $item++; endforeach; ?>
                                </select>
                            </div>

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_hide_available_slots"><?php echo bkntc__('Hide the number of available slots')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_available_slots"<?php echo Helper::getOption('hide_available_slots', 'on')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_hide_available_slots"></label>
									</div>
								</div>
							</div>

						</div>

						<div class="hidden" data-step="information">

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_separate_first_and_last_name"><?php echo bkntc__('Separate First and Last name inputs')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_separate_first_and_last_name"<?php echo Helper::getOption('separate_first_and_last_name', 'on')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_separate_first_and_last_name"></label>
									</div>
								</div>
							</div>

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_set_email_as_required"><?php echo bkntc__('Set Email as a required field')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_set_email_as_required"<?php echo Helper::getOption('set_email_as_required', 'on')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_set_email_as_required"></label>
									</div>
								</div>
							</div>

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_set_phone_as_required"><?php echo bkntc__('Set Phone number as a required field')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_set_phone_as_required"<?php echo Helper::getOption('set_phone_as_required', 'off')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_set_phone_as_required"></label>
									</div>
								</div>
							</div>

							<div class="form-group col-md-12">
								<label for="input_default_phone_country_code"><?php echo bkntc__('Default phone country code')?>:</label>
								<input type="text" id="input_default_phone_country_code" class="form-control" data-country-code="<?php echo Helper::getOption('default_phone_country_code', '')?>">
							</div>

						</div>

						<div class="hidden" data-step="confirm_details">

                            <?php

                                foreach ( TabUI::get('settings_booking_steps')->getSubItems() as $item )
                                {
                                    echo $item->getContent();
                                }
                            ?>

						</div>

						<div class="hidden" data-step="finish">

							<div class="form-group col-md-12">
								<label for="input_redirect_url_after_booking"><?php echo bkntc__('URL of "FINISH BOOKING" button')?>:</label>
								<input type="text" class="form-control" data-multilang="true" id="input_redirect_url_after_booking" value="<?php echo Helper::getOption('redirect_url_after_booking', '')?>" placeholder="<?php echo bkntc__('Default: Reload current page.')?>">
							</div>

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_hide_add_to_google_calendar_btn"><?php echo bkntc__('Hide the "ADD TO GOOGLE CALENDAR" button')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_add_to_google_calendar_btn"<?php echo Helper::getOption('hide_add_to_google_calendar_btn', 'off')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_hide_add_to_google_calendar_btn"></label>
									</div>
								</div>
							</div>

                            <div class="form-group col-md-12">
                                <div class="form-control-checkbox">
                                    <label for="input_hide_add_to_icalendar_btn"><?php echo bkntc__('Hide the "ADD TO iCAL CALENDAR" button')?>:</label>
                                    <div class="fs_onoffswitch">
                                        <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_add_to_icalendar_btn"<?php echo Helper::getOption('hide_add_to_icalendar_btn', 'off')=='on'?' checked':''?>>
                                        <label class="fs_onoffswitch-label" for="input_hide_add_to_icalendar_btn"></label>
                                    </div>
                                </div>
                            </div>

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_hide_start_new_booking_btn"><?php echo bkntc__('Hide the "START NEW BOOKING" button')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_start_new_booking_btn"<?php echo Helper::getOption('hide_start_new_booking_btn', 'off')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_hide_start_new_booking_btn"></label>
									</div>
								</div>
							</div>

							<div class="form-group col-md-12">
								<div class="form-control-checkbox">
									<label for="input_hide_confirmation_number"><?php echo bkntc__('Hide a confirmation number')?>:</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_confirmation_number"<?php echo Helper::getOption('hide_confirmation_number', 'off')=='on'?' checked':''?>>
										<label class="fs_onoffswitch-label" for="input_hide_confirmation_number"></label>
									</div>
								</div>
							</div>

							<?php if( ! Helper::isSaaSVersion() ):?>
								<div class="form-group col-md-12">
									<label for="input_confirmation_number"><?php echo bkntc__('Starting confirmation number')?>:</label>
									<input type="text" class="form-control" id="input_confirmation_number" value="<?php echo (int)$parameters['confirmation_number']?>">
								</div>
							<?php endif; ?>

						</div>

					</form>
				</div>
			</div>

		</div>
	</div>
</div>