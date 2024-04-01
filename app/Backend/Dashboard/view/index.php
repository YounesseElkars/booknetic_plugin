<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

?>
<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/dashboard.css', 'Dashboard')?>" />
<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/daterangepicker.css', 'Dashboard')?>" />
<link rel="stylesheet" href="<?php echo Helper::assets('css/info.css', 'Customers')?>">

<script type="application/javascript" src="<?php echo Helper::assets('js/moment.min.js', 'Dashboard')?>"></script>
<script type="application/javascript" src="<?php echo Helper::assets('js/daterangepicker.min.js', 'Dashboard')?>"></script>
<script type="application/javascript" src="<?php echo Helper::assets('js/dashboard.js', 'Dashboard')?>"></script>

<div class="m_header clearfix">
	<div class="m_head_title float-left"><?php echo bkntc__('Dashboard')?></div>
</div>

<div id="date_buttons">

	<span class="date_buttons_span">
		<button type="button" class="date_button active_btn" data-type="today"><?php echo bkntc__('Today')?></button>
		<button type="button" class="date_button" data-type="yesterday"><?php echo bkntc__('Yesterday')?></button>
		<button type="button" class="date_button" data-type="tomorrow"><?php echo bkntc__('Tomorrow')?></button>
		<button type="button" class="date_button" data-type="this_week"><?php echo bkntc__('This week')?></button>
		<button type="button" class="date_button" data-type="last_week"><?php echo bkntc__('Last week')?></button>
		<button type="button" class="date_button" data-type="this_month"><?php echo bkntc__('This month')?></button>
		<button type="button" class="date_button" data-type="this_year"><?php echo bkntc__('This year')?></button>
		<button type="button" class="date_button" data-type="custom"><?php echo bkntc__('Custom')?></button>
	</span>

	<div class="inner-addon left-addon date_custom_picker_d">
		<i><img src="<?php echo Helper::icon('calendar.svg')?>"/></i>
		<input type="text" class="form-control custom_date_range">
	</div>

</div>

<div id="statistic-boxes-area">
    <div class="row m-0">
        <div class="col-xl-3 col-lg-6 p-0 pr-lg-3 mb-4 mb-xl-0">
            <div class="statistic-boxes">
                <div class="box-icon-div"><img src="<?php echo Helper::icon('1.svg', 'Dashboard')?>"></div>
                <div class="box-number-div" data-stat="appointments">...</div>
                <div class="box-title-div"><?php echo bkntc__('Appointments')?></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 p-0 pr-xl-3 mb-4 mb-xl-0">
            <div class="statistic-boxes">
                <div class="box-icon-div"><img src="<?php echo Helper::icon('2.svg', 'Dashboard')?>"></div>
                <div class="box-number-div" data-stat="duration">...</div>
                <div class="box-title-div"><?php echo bkntc__('Durations')?></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 p-0 pr-lg-3 mb-4 mb-lg-0">
            <div class="statistic-boxes">
                <div class="box-icon-div"><img src="<?php echo Helper::icon('3.svg', 'Dashboard')?>"></div>
                <div class="box-number-div" data-stat="revenue">...</div>
                <div class="box-title-div"><?php echo bkntc__('Revenue')?></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 p-0 pr-lg-3 mb-4 mb-lg-0">
            <div class="statistic-boxes">
                <div class="box-icon-div"><img src="<?php echo Helper::icon('1.svg', 'Dashboard')?>"></div>
                <div class="box-number-div" data-stat="customers">...</div>
                <div class="box-title-div"><?php echo bkntc__('New Customers')?></div>
            </div>
        </div>
    </div>
</div>


<div class="card_list">
    <div class="row m-0">
        <div class="col-xl-3 col-lg-6 p-0 pr-lg-3 mb-4 mb-xl-0">
            <div class="dashboard-card">
                <div class="dashboard-card-title">
                    <?php echo bkntc__( 'APPOINTMENT\'S QUICK STATS' ); ?>
                </div>
                <div class="dashboard-card-body">
                    <?php foreach ( Helper::getAppointmentStatuses() as $statuses ): ?>
                        <div class="dashboard-appointments">
                            <div class="appointment-status">
                                <div class="appointment-status-icon" style="background-color: <?php echo htmlspecialchars( $statuses[ 'color' ] ); ?>2b">
                                    <i style="color: <?php echo htmlspecialchars( $statuses[ 'color' ] ); ?>" class="<?php echo htmlspecialchars( $statuses[ 'icon' ] ); ?>"></i>
                                </div>
                                <div class="appointment-status-title">
                                    <?php echo htmlspecialchars( $statuses[ 'title' ] ); ?>
                                </div>
                            </div>
                            <div class="appointment-stats" data-stat="status-<?php echo htmlspecialchars( $statuses[ 'slug' ] ); ?>">
                                0
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-9 col-lg-6 p-0 pr-lg-3 mb-4 mb-xl-0">
            <div class="dashboard-card">
                <div class="dashboard-card-title graph-title d-flex">
                    <div class="title-content">
                        <?php echo bkntc__( 'Graph' ); ?>
                    </div>
                    <div class="graph-btns">
                        <span class="date_buttons_span">
                            <button type="button" class="date_button active" data-type="last_year"><?php echo bkntc__('Last 1 year') ?></button>
                            <?php for ( $i = 4 ; $i >= 0 ; $i-- ):
                                $date = (new DateTime('now'))->modify("-$i years")->format('Y');
                                ?>
                                <button type="button" class="date_button" data-type="<?php echo $date ?>"><?php echo $date ?></button>
                            <?php endfor; ?>
                        </span>
                    </div>
                </div>


                <div class="dashboard-card-body graph-body">

                    <div id="graph">
                        <?php
                            \BookneticApp\Backend\Dashboard\Helpers\UIHelper::renderGraph( date('Y-m-d' , strtotime(date("Y-m-d") . '-1 year')) , date('Y-m-d') );
                        ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


