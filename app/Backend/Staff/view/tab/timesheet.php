<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */
?>

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
                <label for="input_duration" class="timesheet-label"><?php echo ($dayNum+1) . '. ' . $weekDay . ( $dayNum == 0 ? '<span class="copy_time_to_all"  data-toggle="tooltip" data-placement="top" title="' . bkntc__('Copy to all') . '"><i class="far fa-copy"></i></span>' : '' ) ?></label>
                <div class="input-group">
                    <div class="col-md-6 p-0 m-0">
                        <select id="input_timesheet_<?php echo ($dayNum+1)?>_start" class="form-control" placeholder="<?php echo bkntc__('Start time')?>"><option selected><?php echo ! empty( $editInfo['start'] ) ? Date::time( $editInfo['start'] ) : ''; ?></option></select>
                    </div>
                    <div class="col-md-6 p-0 m-0">
                        <select id="input_timesheet_<?php echo ($dayNum+1)?>_end" class="form-control" placeholder="<?php echo bkntc__('End time')?>"><option selected><?php echo empty( $editInfo['end'] ) ?  '' : ( $editInfo['end'] == "24:00" ? "24:00" : Date::time( $editInfo['end'] ) ); ?></option></select>
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
            <div class="days_divider2"></div>
            <?php
        }
        ?>

        <?php
    }
    ?>
</div>