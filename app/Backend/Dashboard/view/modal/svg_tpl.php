<?php

use BookneticApp\Providers\Helpers\Date;

$startDate = $parameters['start_day'];
$endDay = $parameters['end_day'];
$startOriginal = $startDate;
$days = $parameters['days'];
$maxCount = $parameters['max_count'];
$x = 16;
$monthsArr = [];

$months = [
    'Jan'              => bkntc__('Jan'),
    'Feb'              => bkntc__('Feb'),
    'Mar'              => bkntc__('Mar'),
    'Apr'              => bkntc__('Apr'),
    'May'              => bkntc__('May'),
    'Jun'              => bkntc__('Jun'),
    'Jul'              => bkntc__('Jul'),
    'Aug'              => bkntc__('Aug'),
    'Sep'              => bkntc__('Sep'),
    'Oct'              => bkntc__('Oct'),
    'Nov'              => bkntc__('Nov'),
    'Dec'              => bkntc__('Dec'),
];

function bkntc_dashboard_graph_rgba( $level )
{
    if( $level === 0 )
    {
        return "rgba(230, 230 ,230 , 1)";
    }else{
        return "rgba(0, 119 ,255 , " . $level / 4  . ")";
    }
}

?>
<svg width="850" height="128" class="js-calendar-graph-svg">
    <g transform="translate(10, 20)">
        <?php for ($i = 0 ; $i < 53 ; $i++ ): ?>
            <g transform="translate(<?php echo ($i + 2) * 16 ?>, 0)">
                <?php
                for ($j = ( $i==0 ?  date('N', strtotime( $startOriginal )) - 1 : 0 ) ; $j < 7 ; $j++ ):

                    if( !array_key_exists(date('Y-m' , strtotime($startDate)) , $monthsArr ))
                    {
                        $monthsArr[date('Y-m' , strtotime($startDate))] = [ 'name' => date("M" , strtotime($startDate)) , 'x' => $i * 16 ];
                    }

                    if ( $i == 0 && date('t', strtotime( $startDate ) ) - date('d', strtotime( $startDate ) ) < 14 )
                        $monthsArr[date('Y-m' , strtotime($startDate))]['x'] = -1000;

                    $count = 0;

                    if( array_key_exists( $startDate , $days ) )
                    {
                        $count = $days[ $startDate ][ 'count' ];
                    }

                    $level = $count == 0 ? 0 : ceil( $count / ceil($maxCount / 4) );
                    ?>
                    <rect class="graph_day" fill="<?php echo bkntc_dashboard_graph_rgba( $level )?>" width="11" height="11" x="<?php echo $x ?>" y="<?php echo $j * 15?>" rx="2" ry="2" data-count="<?php echo $count; ?>" data-date="<?php echo Date::convertDateFormat($startDate) ?>" data-level="0"></rect>
                    <?php
                    $startDate = date('Y-m-d', strtotime($startDate . ' +1 day'));
                    if( $endDay < $startDate ) break;
                endfor;
                $x-- ;
                ?>
            </g>
        <?php endfor; ?>
        <?php foreach ( $monthsArr as $month): ?>
            <text x="<?php echo $month['x'] + 33; ?>" y="-8" class="month_name" ><?php echo $months[ $month['name'] ]; ?></text>
        <?php endforeach; ?>
        <text text-anchor="start" class="week_name" dx="16" dy="8"><?php echo bkntc__('Mon')?></text>
        <text text-anchor="start" class="week_name" dx="16" dy="23" style="display: none;"><?php echo bkntc__('Tue')?></text>
        <text text-anchor="start" class="week_name" dx="16" dy="38"><?php echo bkntc__('Wed')?></text>
        <text text-anchor="start" class="week_name" dx="16" dy="53" style="display: none;"><?php echo bkntc__('Thu')?></text>
        <text text-anchor="start" class="week_name" dx="16" dy="68" ><?php echo bkntc__('Fri')?></text>
        <text text-anchor="start" class="week_name" dx="16" dy="83" style="display: none;"><?php echo bkntc__('Sat')?></text>
        <text text-anchor="start" class="week_name" dx="16" dy="98"><?php echo bkntc__('Sun')?></text>

    </g>
</svg>

<span class="graph_info_popup"></span>