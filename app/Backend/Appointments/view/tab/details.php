<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;

function customerTpl( $display = false )
{
    $statuses = Helper::getAppointmentStatuses();
    $defaultStatus = Helper::getDefaultAppointmentStatus();
    $defaultStatus = array_key_exists($defaultStatus, $statuses) ? $defaultStatus : array_keys($statuses)[0];

    ?>
    <div class="form-row customer-tpl<?php echo ($display?'':' hidden')?>">
        <div class="col-md-6">
            <div class="input-group">
                <select class="form-control input_customer"></select>
                <div class="input-group-prepend">
                    <button class="btn btn-outline-secondary btn-lg" type="button" data-load-modal="customers.add_new"><i class="fa fa-plus"></i></button>
                </div>
            </div>
        </div>
        <div class="col-md-6 d-flex">
			<span class="customer-status-btn">
				<button class="btn btn-lg btn-outline-secondary" data-status="<?php echo $defaultStatus?>" type="button" data-toggle="dropdown"><i class="<?php echo $statuses[$defaultStatus]['icon']?>" style="color:<?php echo $statuses[$defaultStatus]['color']?>"></i> <span class="c_status"><?php echo $statuses[$defaultStatus]['title']?></span> <img src="<?php echo Helper::icon('arrow-down-xs.svg')?>"></button>
				<div class="dropdown-menu customer-status-panel">
					<?php
                    foreach ( $statuses AS $stName => $status )
                    {
                        echo '<a class="dropdown-item" href="#" data-status="' . $stName . '"><i class="' . $status['icon'] . '" style="color: ' . $status['color'] . ';"></i> ' . $status['title'] . '</a>';
                    }
                    ?>
				</div>
			</span>

            <div class="number_of_group_customers_span">
                <button class="btn btn-lg btn-outline-secondary number_of_group_customers" type="button" data-toggle="dropdown" disabled>
                    <i class="fa fa-user "></i> <span class="c_number">1</span>
                    <img src="<?php echo Helper::icon('arrow-down-xs.svg')?>">
                </button>
                <div class="dropdown-menu number_of_group_customers_panel"></div>
            </div>
        </div>
    </div>

    <?php
}

/**
 * @var array $parameters
 */
?>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_location"><?php echo bkntc__('Location')?> <span class="required-star">*</span></label>
        <select class="form-control" id="input_location">
            <?php if( $parameters['location'] ):?>
                <option value="<?php echo (int)$parameters['location']->id?>"><?php echo htmlspecialchars($parameters['location']->name)?></option>
            <?php endif;?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label><?php echo bkntc__('Category')?></label>
        <div><select class="form-control input_category"></select></div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="input_service"><?php echo bkntc__('Service')?> <span class="required-star">*</span></label>
        <select class="form-control" id="input_service"></select>
    </div>
    <div class="form-group col-md-6">
        <label for="input_staff"><?php echo bkntc__('Staff')?> <span class="required-star">*</span></label>
        <select class="form-control" id="input_staff"></select>
    </div>
</div>

<div data-service-type="repeatable_weekly">
    <div class="form-row">
        <div class="form-group col-md-12">
            <label><?php echo bkntc__('Days of week')?> <span class="required-star">*</span></label>
            <div class="dashed-border">
                <div class="days_of_week_boxes clearfix">
                    <div class="day_of_week_box">
                        <input type="checkbox" class="day_of_week_checkbox" id="day_of_week_checkbox_1"/>
                        <label for="day_of_week_checkbox_1"><?php echo bkntc__('Mon')?></label>
                    </div>

                    <div class="day_of_week_box">
                        <input type="checkbox" class="day_of_week_checkbox" id="day_of_week_checkbox_2">
                        <label for="day_of_week_checkbox_2"><?php echo bkntc__('Tue')?></label>
                    </div>

                    <div class="day_of_week_box">
                        <input type="checkbox" class="day_of_week_checkbox" id="day_of_week_checkbox_3">
                        <label for="day_of_week_checkbox_3"><?php echo bkntc__('Wed')?></label>
                    </div>

                    <div class="day_of_week_box">
                        <input type="checkbox" class="day_of_week_checkbox" id="day_of_week_checkbox_4">
                        <label for="day_of_week_checkbox_4"><?php echo bkntc__('Thu')?></label>
                    </div>

                    <div class="day_of_week_box">
                        <input type="checkbox" class="day_of_week_checkbox" id="day_of_week_checkbox_5">
                        <label for="day_of_week_checkbox_5"><?php echo bkntc__('Fri')?></label>
                    </div>

                    <div class="day_of_week_box">
                        <input type="checkbox" class="day_of_week_checkbox" id="day_of_week_checkbox_6">
                        <label for="day_of_week_checkbox_6"><?php echo bkntc__('Sat')?></label>
                    </div>

                    <div class="day_of_week_box">
                        <input type="checkbox" class="day_of_week_checkbox" id="day_of_week_checkbox_7">
                        <label for="day_of_week_checkbox_7"><?php echo bkntc__('Sun')?></label>
                    </div>
                </div>
                <div class="times_days_of_week_area">

                    <div class="form-row hidden" data-day="1">
                        <div class="form-group col-md-3">
                            <div class="form-control-plaintext"><?php echo bkntc__('Monday')?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select class="form-control wd_input_time" id="input_time_wd_1"></select>
                            </div>
                        </div>
                        <div class="col-md-1 copy_time_to_all">
                            <i class="far fa-copy" title="<?php echo bkntc__('Copy to all')?>"></i>
                        </div>
                    </div>

                    <div class="form-row hidden" data-day="2">
                        <div class="form-group col-md-3">
                            <div class="form-control-plaintext"><?php echo bkntc__('Tuesday')?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select class="form-control wd_input_time" id="input_time_wd_2"></select>
                            </div>
                        </div>
                        <div class="col-md-1 copy_time_to_all">
                            <i class="far fa-copy" title="<?php echo bkntc__('Copy to all')?>"></i>
                        </div>
                    </div>

                    <div class="form-row hidden" data-day="3">
                        <div class="form-group col-md-3">
                            <div class="form-control-plaintext"><?php echo bkntc__('Wednesday')?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select class="form-control wd_input_time" id="input_time_wd_3"></select>
                            </div>
                        </div>
                        <div class="col-md-1 copy_time_to_all">
                            <i class="far fa-copy" title="<?php echo bkntc__('Copy to all')?>"></i>
                        </div>
                    </div>

                    <div class="form-row hidden" data-day="4">
                        <div class="form-group col-md-3">
                            <div class="form-control-plaintext"><?php echo bkntc__('Thursday')?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select class="form-control wd_input_time" id="input_time_wd_4"></select>
                            </div>
                        </div>
                        <div class="col-md-1 copy_time_to_all">
                            <i class="far fa-copy" title="<?php echo bkntc__('Copy to all')?>"></i>
                        </div>
                    </div>

                    <div class="form-row hidden" data-day="5">
                        <div class="form-group col-md-3">
                            <div class="form-control-plaintext"><?php echo bkntc__('Friday')?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select class="form-control wd_input_time" id="input_time_wd_5"></select>
                            </div>
                        </div>
                        <div class="col-md-1 copy_time_to_all">
                            <i class="far fa-copy" title="<?php echo bkntc__('Copy to all')?>"></i>
                        </div>
                    </div>

                    <div class="form-row hidden" data-day="6">
                        <div class="form-group col-md-3">
                            <div class="form-control-plaintext"><?php echo bkntc__('Saturday')?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select class="form-control wd_input_time" id="input_time_wd_6"></select>
                            </div>
                        </div>
                        <div class="col-md-1 copy_time_to_all">
                            <i class="far fa-copy" title="<?php echo bkntc__('Copy to all')?>"></i>
                        </div>
                    </div>

                    <div class="form-row hidden" data-day="7">
                        <div class="form-group col-md-3">
                            <div class="form-control-plaintext"><?php echo bkntc__('Sunday')?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select class="form-control wd_input_time" id="input_time_wd_7"></select>
                            </div>
                        </div>
                        <div class="col-md-1 copy_time_to_all">
                            <i class="far fa-copy" title="<?php echo bkntc__('Copy to all')?>"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div data-service-type="repeatable_daily">

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input_daily_recurring_frequency"><?php echo bkntc__('Every')?> <span class="required-star">*</span></label>

            <div class="inner-addon right-addon left-addon">
                <i><img src="<?php echo Helper::icon('calendar.svg')?>"/></i>
                <input type="text" class="form-control" id="input_daily_recurring_frequency" value="1">
                <i class="days_txt"><?php echo bkntc__('DAYS')?></i>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label for="input_daily_time"><?php echo bkntc__('Time')?> <span class="required-star">*</span></label>
            <div class="inner-addon left-addon">
                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                <select class="form-control" id="input_daily_time"></select>
            </div>
        </div>
    </div>

</div>

<div data-service-type="repeatable_monthly">

    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="input_monthly_recurring_type"><?php echo bkntc__('On')?> <span class="required-star">*</span></label>
            <select class="form-control" id="input_monthly_recurring_type">
                <option value="specific_day"><?php echo bkntc__('Specific day')?></option>
                <option value="1"><?php echo bkntc__('First')?></option>
                <option value="2"><?php echo bkntc__('Second')?></option>
                <option value="3"><?php echo bkntc__('Third')?></option>
                <option value="4"><?php echo bkntc__('Fourth')?></option>
                <option value="last"><?php echo bkntc__('Last')?></option>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label for="input_monthly_recurring_day_of_week">&nbsp;</label>
            <select class="form-control" id="input_monthly_recurring_day_of_week">
                <option value="1">1. <?php echo bkntc__('Monday')?></option>
                <option value="2">2. <?php echo bkntc__('Tuesday')?></option>
                <option value="3">3. <?php echo bkntc__('Wednesday')?></option>
                <option value="4">4. <?php echo bkntc__('Thursday')?></option>
                <option value="5">5. <?php echo bkntc__('Friday')?></option>
                <option value="6">6. <?php echo bkntc__('Saturday')?></option>
                <option value="7">7. <?php echo bkntc__('Sunday')?></option>
            </select>
            <select class="form-control" id="input_monthly_recurring_day_of_month" multiple>
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
        <div class="form-group col-md-4">
            <label for="input_monthly_time"><?php echo bkntc__('Time')?> <span class="required-star">*</span></label>
            <div class="inner-addon left-addon">
                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                <select class="form-control" id="input_monthly_time"></select>
            </div>
        </div>
    </div>

</div>

<div data-service-type="repeatable">
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="input_recurring_start_date"><?php echo bkntc__('Start date')?> <span class="required-star">*</span></label>
            <div class="inner-addon left-addon">
                <i><img src="<?php echo Helper::icon('calendar.svg')?>"/></i>
                <input type="text" class="form-control" id="input_recurring_start_date" value="<?php echo Date::datee( $parameters['date'] )?>">
            </div>
        </div>
        <div class="form-group col-md-4">
            <label for="input_recurring_end_date"><?php echo bkntc__('End date')?> <span class="required-star">*</span></label>
            <div class="inner-addon left-addon">
                <i><img src="<?php echo Helper::icon('calendar.svg')?>"/></i>
                <input type="text" class="form-control" id="input_recurring_end_date">
            </div>
        </div>
        <div class="form-group col-md-4">
            <label for="input_recurring_times"><?php echo bkntc__('Times')?></label>
            <input type="text" class="form-control" id="input_recurring_times">
        </div>
    </div>
</div>

<?php TabUI::get('appointments_add_new')->item('details')->setAction('duration_after') ?>

<div data-service-type="non_repeatable">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input_date"><?php echo bkntc__('Date')?> <span class="required-star">*</span></label>
            <div class="inner-addon left-addon">
                <i><img src="<?php echo Helper::icon('calendar.svg')?>"/></i>
                <input class="form-control" id="input_date" value="<?php echo Date::format(Helper::getOption('date_format', 'Y-m-d'), $parameters['date'] )?>" placeholder="<?php echo bkntc__('Select...')?>">
            </div>
        </div>
        <div class="form-group col-md-6">
            <label for="input_time"><?php echo bkntc__('Time')?> <span class="required-star">*</span></label>
            <div class="inner-addon left-addon">
                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                <select class="form-control" id="input_time"></select>
            </div>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label><?php echo bkntc__('Customer')?> <span class="required-star">*</span></label>
        <div class="customers_area">
            <?php echo customerTpl( true ) ?>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label><?php echo bkntc__('Note')?> </label>
        <textarea id="note" class="form-control" name="note"  cols="30" rows="10"></textarea>
    </div>
</div>

