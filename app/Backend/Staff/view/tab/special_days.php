<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

function specialDayTpl( $id = 0, $date = '', $timesheet = '' )
{
    $date = $date == '' ? '' : ( Date::datee( $date ) );

    $dateFormat = htmlspecialchars(Helper::getOption('date_format', 'Y-m-d'));

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
    <div class="special-day-row<?php echo !$id ? ' hidden' : ''?>"<?php echo $id > 0 ? ' data-id="' . $id . '"' : ''?>>
        <div class="form-row">
            <div class="form-group col-md-6">
                <div class="inner-addon left-addon">
                    <i class="far fa-calendar-alt"></i>
                    <input type="text" class="form-control input_special_day_date" data-date-format="<?php echo $dateFormat; ?>" placeholder="<?php echo bkntc__('Date')?>" value="<?php echo $date?>">
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

        <div class="sd_break_footer">
            <div class="special-day-add-break-btn"><i class="fas fa-plus-circle"></i> <?php echo bkntc__('Add break')?></div>
            <div class="remove-special-day-btn"><i class="fas fa-trash"></i> <?php echo bkntc__('Remove special day')?></div>
        </div>
    </div>
    <?php
}

/**
 * @var mixed $parameters
 */
?>

<div class="special-days-area">
    <?php
    foreach ( $parameters['special_days'] AS $special_day )
    {
        specialDayTpl( $special_day['id'], $special_day['date'], $special_day['timesheet'] );
    }
    ?>
</div>

<button type="button" class="btn btn-lg btn-primary add-special-day-btn"><?php echo bkntc__('ADD SPECIAL DAY')?></button>

<?php
echo specialDayTpl();
?>