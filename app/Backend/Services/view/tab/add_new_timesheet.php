<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

function specialDayTpl( $id = 0, $date = '', $timesheet = '' )
{
    $date = $date == '' ? '' : Date::dateSQL( $date );

    $timesheet = json_decode( $timesheet, true );
    $timesheet = is_array( $timesheet ) ? $timesheet : [];

    if( empty( $timesheet ) )
    {
        $startTime = '';
        $endTime = '';

        $breaks = [];
    }
    else
    {
        $startTime = isset($timesheet['start']) ? $timesheet['start'] : '';
        $endTime = isset($timesheet['end']) ? $timesheet['end'] : '';

        $breaks = isset( $timesheet['breaks'] ) ? $timesheet['breaks'] : [];
    }
    ?>
    <div class="special-day-row<?php echo !$id ? ' hidden' : '' ?>"<?php echo $id > 0 ? ' data-id="' . $id . '"' : ''?>>
        <div class="form-row">
            <div class="form-group col-md-6">
                <div class="inner-addon left-addon">
                    <i class="far fa-calendar-alt"></i>
                    <input data-date-format="<?php echo (htmlspecialchars(Helper::getOption('date_format', 'Y-m-d')))?>" type="text" class="form-control input_special_day_date" placeholder="<?php echo bkntc__('Date')?>" value="<?php echo ( empty($date) ? '' : Date::convertDateFormat( $date ) )?>">
                </div>
            </div>
            <div class="form-group col-md-6">
                <div class="input-group">
                    <div class="col-md-6 p-0 m-0">
                        <select class="form-control input_special_day_start" placeholder="<?php echo bkntc__('Start time')?>"><option selected><?php echo ! empty( $startTime ) ? Date::time( $startTime ) : ''; ?></option></select>
                    </div>
                    <div class="col-md-6 p-0 m-0">
                        <select class="form-control input_special_day_end" placeholder="<?php echo bkntc__('End time')?>"><option selected><?php echo empty( $endTime ) ?  '' : ( $endTime == "24:00" ? "24:00" : Date::time( $endTime ) ); ?></option></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="special_day_breaks_area">
            <?php
            foreach ( $breaks AS $break )
            {
                breakTpl( $break[0], $break[1], true );
            }
            ?>
        </div>

        <div class="sd2_break_footer">
            <div class="special-day-add-break-btn"><i class="fas fa-plus-circle"></i> <?php echo bkntc__('Add break')?></div>
            <div class="remove-special-day-btn"><?php echo bkntc__('Remove special day')?> <img src="<?php echo Helper::icon('trash_mini.svg')?>"></div>
        </div>
    </div>
<?php
}

function breakTpl( $start = '', $end = '', $display = false )
{
    ?>
    <div class="form-row break_line<?php echo $display?'':' hidden'?>">
        <div class="form-group col-md-12">
            <label for="input_duration" class="breaks-label"><?php echo bkntc__('Breaks')?></label>
            <div class="input-group">
                <div>
                    <div class="inner-addon left-addon">
                        <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                        <select class="form-control break_start" placeholder="Break start"><option selected><?php echo ! empty( $start ) ? Date::time( $start ) : ''; ?></option></select>
                    </div>
                </div>
                <div>
                    <div class="inner-addon left-addon">
                        <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                        <select class="form-control break_end" placeholder="Break end"><option selected><?php echo empty( $end ) ?  '' : ( $end == "24:00" ? "24:00" : Date::time( $end ) ); ?></option></select>
                    </div>
                </div>
                <div class="delete-break-btn"><img src="<?php echo Helper::icon('trash_mini.svg')?>"></div>
            </div>
        </div>
    </div>
    <?php
}

?>




<div class="timesheet_tabs d-flex">
    <div class="selected-tab" data-type="weekly-schedule"><?php echo bkntc__('WEEKLY SCHEDULE')?></div>
    <div data-type="special-days"><?php echo bkntc__('SPECIAL DAYS')?></div>
</div>

<div data-tstab="weekly-schedule">

    <div class="form-row">
        <div class="form-group col-md-12">
            <div class="form-control-checkbox">
                <label for="set_specific_timesheet_checkbox"><?php echo bkntc__('Configure specific timesheet')?>:</label>
                <div class="fs_onoffswitch">
                    <input type="checkbox" class="fs_onoffswitch-checkbox" id="set_specific_timesheet_checkbox"<?php echo $parameters['has_specific_timesheet']?' checked':''?>>
                    <label class="fs_onoffswitch-label" for="set_specific_timesheet_checkbox"></label>
                </div>
            </div>
        </div>
    </div>

    <div id="set_specific_timesheet">
        <?php
        $weekDays = [ bkntc__('Monday'), bkntc__('Tuesday'), bkntc__('Wednesday'), bkntc__('Thursday'), bkntc__('Friday'), bkntc__('Saturday'), bkntc__('Sunday') ];
        $ts_editInfo = $parameters['timesheet'];

        foreach ( $weekDays AS $dayNum => $weekDay )
        {
            $editInfo = isset($ts_editInfo[ $dayNum ]) ? $ts_editInfo[ $dayNum ] : false;

            ?>
            <div class="form-row">
                <div class="form-group col-md-9">
                    <label for="input_timesheet_<?php echo ($dayNum+1)?>_start" class="timesheet-label"><?php echo ($dayNum+1) . '. ' . $weekDay . ( $dayNum == 0 ? '<span class="copy_time_to_all"  data-toggle="tooltip" data-placement="top" title="' . bkntc__('Copy to all') . '"><i class="far fa-copy"></i></span>' : '' ) ?></label>
                    <div class="input-group">
                        <div class="col-md-6 m-0 p-0">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select id="input_timesheet_<?php echo ($dayNum+1)?>_start" class="form-control" placeholder="<?php echo bkntc__('Start time')?>"><option selected><?php echo ! empty( $editInfo['start'] ) ? Date::time( $editInfo['start'] ) : ''; ?></option></select>
                            </div>
                        </div>
                        <div class="col-md-6 m-0 p-0">
                            <div class="inner-addon left-addon">
                                <i><img src="<?php echo Helper::icon('time.svg')?>"/></i>
                                <select id="input_timesheet_<?php echo ($dayNum+1)?>_end" class="form-control" placeholder="<?php echo bkntc__('End time')?>"><option selected><?php echo empty( $editInfo['end'] ) ?  '' : ( $editInfo['end'] == "24:00" ? "24:00" : Date::time( $editInfo['end'] ) ); ?></option></select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-3">
                    <div class="day_off_checkbox">
                        <input type="checkbox" class="dayy_off_checkbox" id="dayy_off_checkbox_<?php echo ($dayNum+1)?>"<?php echo ($editInfo['day_off']? ' checked' : '')?>>
                        <label for="dayy_off_checkbox_<?php echo ($dayNum+1)?>"><?php echo bkntc__('Add day off')?></label>
                    </div>
                </div>
            </div>

            <div class="breaks_area" data-day="<?php echo ($dayNum+1)?>">
                <?php
                if( is_array( $editInfo['breaks'] ) )
                {
                    foreach ( $editInfo['breaks'] AS $breakInf )
                    {
                        breakTpl( $breakInf[0], $breakInf[1], true );
                    }
                }
                ?>
            </div>

            <div class="add-break-btn"><i class="fas fa-plus-circle"></i> <?php echo bkntc__('Add break')?></div>

            <?php
            if( $dayNum < 6 )
            {
                ?>
                <div class="days_divider3"></div>
                <?php
            }
            ?>

            <?php
        }
        ?>
    </div>

</div>
<div data-tstab="special-days" class="hidden">

    <div class="special-days-area">
        <?php
        foreach ( $parameters['special_days'] AS $special_day )
        {
            specialDayTpl( $special_day['id'], $special_day['date'], $special_day['timesheet'] );
        }
        ?>
    </div>

    <button type="button" class="btn btn-lg btn-primary add-special-day-btn"><?php echo bkntc__('ADD SPECIAL DAY')?></button>

</div>

<?php
echo breakTpl();
echo specialDayTpl();
?>