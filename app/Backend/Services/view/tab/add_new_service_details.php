<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\UI\TabUI;

/**
 * @var mixed $parameters
 */

?>
<div class="tab-pane active" id="tab_service_details">

    <div class="service_picture_div">
        <div class="service_picture">
            <input type="file" id="input_image" class="d-none">
            <div class="img-circle1"><img class="d-none" src="<?php echo Helper::profileImage($parameters['service']['image'], 'Services')?>"></div>
            <span style="background: <?php echo !empty($parameters['service']['color'])?htmlspecialchars($parameters['service']['color']):'#53d56c'?>;" data-color="<?php echo !empty($parameters['service']['color'])?htmlspecialchars($parameters['service']['color']):'#53d56c'?>" class="service_color"></span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input_name"><?php echo bkntc__('Service name')?> <span class="required-star">*</span></label>
            <input type="text" data-multilang="true" data-multilang-fk="<?php echo isset( $parameters[ 'service' ] ) ? $parameters[ 'service' ]['id'] : 0 ?>" class="form-control required" id="input_name" value="<?php echo htmlspecialchars($parameters['service']['name'])?>">
        </div>
        <div class="form-group col-md-6">
            <label for="input_category"><?php echo bkntc__('Category')?> <span class="required-star">*</span></label>
            <select id="input_category" class="form-control required">
                <option></option>
                <?php
                foreach( $parameters['categories'] AS $category )
                {
                    echo '<option value="' . (int)$category['id'] . '"' . ( $parameters['category'] == $category['id'] ? ' selected' : '' ) . '>' . htmlspecialchars($category['name']) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class="form-row">

        <div class="form-group col-md-6">
            <label for="input_price"><?php echo bkntc__('Price')?> ( <?php echo htmlspecialchars( Helper::currencySymbol() )?> ) <span class="required-star">*</span></label>
            <input id="input_price" class="form-control required" placeholder="0.00" value="<?php echo empty($parameters['service']['price']) ? '' : Helper::price(Math::floor( $parameters['service']['price'], Helper::getOption('price_number_of_decimals', '2') ),false)?>">
        </div>

        <?php

        $deposit = empty($parameters['service']['deposit']) ? '0' : Math::floor( $parameters['service']['deposit'], Helper::getOption('price_number_of_decimals', '2') );

        ?>
        <div class="form-group col-md-6">
            <label for="deposit_checkbox"><span class="required-star"> &nbsp;</span></label>
            <div class="form-control-checkbox">

                <span class="enable_deposit_text">
                    <i class="fa fa-info-circle help-icon do_tooltip" data-content="<?php echo bkntc__( 'Let customers make an appointment by paying the particular part of the amount' ); ?>"></i>
                    <label for="deposit_checkbox"><?php echo bkntc__('Enable Deposit')?></label>
                </span>

                <div class="fs_onoffswitch">
                    <input type="checkbox" id="deposit_checkbox" class="fs_onoffswitch-checkbox" <?php echo $deposit > 0 ?' checked':''?>>
                    <label for="deposit_checkbox" class="fs_onoffswitch-label"></label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-row" data-for="deposit">
        <div class="form-group col-md-6">
            <label for="input_deposit"><?php echo bkntc__('Deposit')?> <span class="required-star">*</span></label>
            <div class="input-group">
                <input id="input_deposit" class="form-control required" placeholder="0.00" value="<?php echo $deposit ?>">
                <select id="input_deposit_type" class="form-control">
                    <option value="percent"<?php echo $parameters['service']['deposit_type']=='percent'?' selected':''?>>%</option>
                    <option value="price"<?php echo $parameters['service']['deposit_type']=='price'?' selected':''?>><?php echo htmlspecialchars( Helper::currencySymbol() )?></option>
                </select>
            </div>
        </div>
    </div>


    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="input_duration"><?php echo bkntc__('Duration')?> <span class="required-star">*</span></label>
            <select id="input_duration" class="form-control required">
                <option value="<?php echo $parameters['service']['duration']?>" selected><?php echo Helper::secFormat($parameters['service']['duration']*60)?></option>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="input_time_slot_length"><?php echo bkntc__('Time slot length')?> <span class="required-star">*</span></label>
            <select id="input_time_slot_length" class="form-control required"> <?php
                if( $parameters[ 'service' ][ 'timeslot_length' ] == '0' ): ?>
                    <option value="0" selected><?php echo bkntc__( 'Default' ) ?></option> <?php
                elseif( $parameters[ 'service' ][ 'timeslot_length' ] == '-1' ): ?>
                    <option value="-1" selected><?php echo bkntc__( 'Slot length as service duration' ) ?></option> <?php
                elseif( $parameters[ 'service' ][ 'timeslot_length' ] ): ?>
                    <option value="<?php echo $parameters[ 'service' ][ 'timeslot_length' ] ?>" selected><?php echo Helper::secFormat( $parameters[ 'service' ][ 'timeslot_length' ] * 60 ) ?></option> <?php
                endif ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="input_buffer_before"><?php echo bkntc__('Buffer Time Before')?></label>
            <select id="input_buffer_before" class="form-control">
                <option value="<?php echo $parameters['service']['buffer_before']?>" selected><?php echo Helper::secFormat($parameters['service']['buffer_before']*60)?></option>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="input_buffer_after"><?php echo bkntc__('Buffer Time After')?></label>
            <select id="input_buffer_after" class="form-control">
                <option value="<?php echo $parameters['service']['buffer_after']?>" selected><?php echo Helper::secFormat($parameters['service']['buffer_after']*60)?></option>
            </select>
        </div>
    </div>

    <?php TabUI::get('services_add')->item('details')->setAction('duration_after') ?>

    <div class="form-row">

        <div class="form-group col-md-6">
            <label>&nbsp;</label>
            <div class="form-control-checkbox">
                <label for="input_hide_price"><?php echo bkntc__('Hide price in booking panel:')?></label>
                <div class="fs_onoffswitch">
                    <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_price"<?php echo $parameters['service']['hide_price']==1?' checked':''?>>
                    <label class="fs_onoffswitch-label" for="input_hide_price"></label>
                </div>
            </div>
        </div>

        <div class="form-group col-md-6">
            <label>&nbsp;</label>
            <div class="form-control-checkbox">
                <label for="input_hide_duration"><?php echo bkntc__('Hide duration in booking panel:')?></label>
                <div class="fs_onoffswitch">
                    <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_hide_duration"<?php echo $parameters['service']['hide_duration']==1?' checked':''?>>
                    <label class="fs_onoffswitch-label" for="input_hide_duration"></label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <div class="form-control-checkbox">

                <span class="repeatable_text">
                    <i class="fa fa-info-circle help-icon do_tooltip" data-content="<?php echo bkntc__( 'Simplify scheduling by allowing customers to book multiple appointments in a single booking session.' ); ?>"></i>
                    <label for="repeatable_checkbox"><?php echo bkntc__('Recurring')?></label>
                </span>

                <div class="fs_onoffswitch">
                    <input type="checkbox" id="repeatable_checkbox" class="fs_onoffswitch-checkbox" <?php echo $parameters['service']['is_recurring']?' checked':''?>>
                    <label for="repeatable_checkbox" class="fs_onoffswitch-label"></label>
                </div>
            </div>
        </div>
    </div>

    <div class="recurring_form_fields" data-for="repeat">

        <div class="form-row">

            <div class="form-group col-md-6">
                <label for="input_recurring_type"><?php echo bkntc__('Repeat')?></label>
                <select id="input_recurring_type" class="form-control">
                    <option value="monthly"<?php echo $parameters['service']['repeat_type']=='monthly'?' selected':''?>><?php echo bkntc__('Monthly')?></option>
                    <option value="weekly"<?php echo $parameters['service']['repeat_type']=='weekly'?' selected':''?>><?php echo bkntc__('Weekly')?></option>
                    <option value="daily"<?php echo $parameters['service']['repeat_type']=='daily'?' selected':''?>><?php echo bkntc__('Daily')?></option>
                </select>
            </div>
        </div>

        <div class="form-row">

            <div class="form-group col-md-6">
                <div class="recurring_fixed_period">
                    <input type="checkbox" id="recurring_fixed_full_period"<?php echo $parameters['service']['full_period_value']>0?' checked':''?>>
                    <label for="recurring_fixed_full_period"><?php echo bkntc__('Fixed full period')?> <i class="fa fa-info-circle help-icon do_tooltip" data-content="<?php echo bkntc__( 'Select how many times the timeslot can be repeated' ); ?>"></i></label>
                </div>
            </div>

            <div class="form-group col-md-6">
                <div class="input-group">
                    <input type="text" id="input_full_period" class="form-control text-center col-md-6 m-0" placeholder="0" value="<?php echo htmlspecialchars( $parameters['service']['full_period_value'] )?>">
                    <select id="input_full_period_type" class="form-control col-md-6 m-0">
                        <option value="month"<?php echo $parameters['service']['full_period_type']=='month'?' selected':''?>><?php echo bkntc__('month')?></option>
                        <option value="week"<?php echo $parameters['service']['full_period_type']=='week'?' selected':''?>><?php echo bkntc__('week')?></option>
                        <option value="day"<?php echo $parameters['service']['full_period_type']=='day'?' selected':''?>><?php echo bkntc__('day')?></option>
                        <option value="time"<?php echo $parameters['service']['full_period_type']=='time'?' selected':''?>><?php echo bkntc__('time(s)')?></option>
                    </select>
                </div>
            </div>

        </div>

        <div class="form-row">

            <div class="form-group col-md-6">
                <div class="recurring_fixed_period">
                    <input type="checkbox" id="recurring_fixed_frequency"<?php echo $parameters['service']['repeat_frequency']>0?' checked':''?>>
                    <label for="recurring_fixed_frequency"><?php echo bkntc__('Fixed frequency')?> <i class="fa fa-info-circle  help-icon do_tooltip" data-content="<?php echo bkntc__( 'Select the frequency of the repeated timeslot' ); ?>"></i></label>
                </div>
            </div>

            <div class="form-group col-md-6">
                <div class="input-group">
                    <input type="text" id="input_repeat_frequency" class="form-control col-md-6 m-0 text-center" placeholder="0" value="<?php echo !$parameters['service']['repeat_frequency']?'':(int)$parameters['service']['repeat_frequency']?>">
                    <div class="form-control repeat_frequency_txt col-md-6 m-0"><?php echo bkntc__('time per week')?></div>
                </div>
            </div>

        </div>

        <div class="form-row">

            <div class="form-group col-md-12">
                <label for="input_recurring_payment_type"><?php echo bkntc__('Payment')?></label>
                <select id="input_recurring_payment_type" class="form-control">
                    <option value="first_month"<?php echo $parameters['service']['recurring_payment_type']=='first_month'?' selected':''?>><?php echo bkntc__('The Customer pays separately for each appointment')?></option>
                    <option value="full"<?php echo $parameters['service']['recurring_payment_type']=='full'?' selected':''?>><?php echo bkntc__('The Customer pays full amount of recurring appointments')?></option>
                </select>
            </div>
        </div>

    </div>

    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="select_capacity"><?php echo bkntc__('Capacity')?></label>
            <select id="select_capacity" class="form-control">
                <option value="0"><?php echo bkntc__('Alone')?></option>
                <option value="1"<?php echo ((int)$parameters['service']['max_capacity']>1?' selected':'')?>><?php echo bkntc__('Group')?></option>
            </select>
        </div>

        <div class="form-group col-md-3" id="max_capacity_form_group">
            <label for="input_max_capacity"><?php echo bkntc__('Max. capacity')?></label>
            <input type="text" id="input_max_capacity" class="form-control" value="<?php echo ((int)$parameters['service']['max_capacity']>0?(int)$parameters['service']['max_capacity']:1)?>">
        </div>
        <div class="form-group col-md-6 pt-5" id="bring_people_form_group">
            <input type="checkbox" id="bring_people" <?php echo $parameters["bring_people"] > 0 ?' checked':''?>>
            <label for="bring_people"><?php echo bkntc__('Enable the "Bring people with you" option')?></label>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-12">
            <label for="input_note"><?php echo bkntc__('Description')?></label>
            <textarea data-multilang="true" data-multilang-fk="<?php echo isset( $parameters[ 'service' ] ) ? $parameters[ 'service' ]['id'] : 0 ?>" maxlength="1000" id="input_note" class="form-control"><?php echo htmlspecialchars($parameters['service']['notes'])?></textarea>
        </div>
    </div>

</div>