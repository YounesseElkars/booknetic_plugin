<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @var array $parameters
*/

?>

<link href="<?php echo Helper::assets('css/calendar.css', 'Calendar')?>" rel="stylesheet" />
<link href="<?php echo Helper::assets('plugins/fullcalendar/packages/core/main.css', 'Calendar')?>" rel="stylesheet" />
<link href="<?php echo Helper::assets('plugins/fullcalendar/packages/daygrid/main.css', 'Calendar')?>" rel="stylesheet" />
<link href="<?php echo Helper::assets('plugins/fullcalendar/packages/timegrid/main.css', 'Calendar')?>" rel="stylesheet" />
<link href="<?php echo Helper::assets('plugins/fullcalendar/packages/list/main.css', 'Calendar')?>" rel="stylesheet" />

<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/core/main.js', 'Calendar')?>"></script>
<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/interaction/main.js', 'Calendar')?>"></script>
<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/daygrid/main.js', 'Calendar')?>"></script>
<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/timegrid/main.js', 'Calendar')?>"></script>
<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/list/main.js', 'Calendar')?>"></script>
<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/resource-common/main.js', 'Calendar')?>"></script>
<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/resource-daygrid/main.js', 'Calendar')?>"></script>
<script src="<?php echo Helper::assets('plugins/fullcalendar/packages/resource-timegrid/main.js', 'Calendar')?>"></script>

<script src="<?php echo Helper::assets('js/calendar.js', 'Calendar')?>"></script>

<div class="m_header ">
	<div class="m_head_title"><?php echo bkntc__('Calendar')?></div>
	<div class="m_head_actions flex-column flex-md-row">
        <div class="d-flex">
            <div class="advanced_filters">
                <div class="advanced_filters_btn action_btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M5 10H15M2.5 5H17.5M7.5 15H12.5" stroke="#ADBFC7" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?php echo \bkntc__( 'Advanced filter' ) ?></span>
                    <div class="filter_status">
                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 9 9" fill="none">
                            <circle cx="4.5" cy="4.5" r="4.5" fill="#F33666"/>
                        </svg>
                    </div>
                </div>
                <div class="advanced_filters_popover">
                    <div class="advanced_filters_popover_head">
                        <span><?php echo \bkntc__( 'Advanced filter' ) ?></span>
                        <div class="close_btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M11.3332 4.6665L4.6665 11.3332M4.6665 4.6665L11.3332 11.3332" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="advanced_filters_popover_body">
                        <div class="filters">
                            <div class="filter">
                                <div class="filter_title"><?php echo \bkntc__( 'Staff' ) ?></div>
                                <div>
                                    <select class="form-control" multiple="multiple" data-placeholder="<?php echo bkntc__('Select')?>" id="calendar_staff_filter">
	                                    <?php foreach ( $parameters[ 'staff' ] as $staff ): ?>
                                            <option value="<?php echo (int) $staff[ 'id' ] ?>">
			                                    <?php echo htmlspecialchars( $staff[ 'name' ] ) ?>
                                            </option>
	                                    <?php endforeach; ?>
                                    </select>
                                    <div class="clear_select">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3332 4.6665L4.6665 11.3332M4.6665 4.6665L11.3332 11.3332" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="filter">
                                <div class="filter_title"><?php echo \bkntc__( 'Location' ) ?></div>
                                <div>
                                    <select class="form-control" multiple="multiple" data-placeholder="<?php echo bkntc__('Select')?>" id="calendar_location_filter">
	                                    <?php foreach ( $parameters[ 'locations' ] as $location ): ?>
                                            <option value="<?php echo (int) $location[ 'id' ] ?>">
			                                    <?php echo htmlspecialchars( $location[ 'name' ] ) ?>
                                            </option>
	                                    <?php endforeach; ?>
                                    </select>
                                    <div class="clear_select">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3332 4.6665L4.6665 11.3332M4.6665 4.6665L11.3332 11.3332" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="filter">
                                <div class="filter_title"><?php echo \bkntc__( 'Service' ) ?></div>
                                <div>
                                    <select class="form-control" multiple="multiple" data-placeholder="<?php echo bkntc__('Select')?>" id="calendar_service_filter">
	                                    <?php foreach ( $parameters[ 'services' ] as $service ): ?>
                                            <option value="<?php echo (int) $service[ 'id' ] ?>">
			                                    <?php echo htmlspecialchars( $service[ 'name' ] ) ?>
                                            </option>
	                                    <?php endforeach; ?>
                                    </select>
                                    <div class="clear_select">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3332 4.6665L4.6665 11.3332M4.6665 4.6665L11.3332 11.3332" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="filter">
                                <div class="filter_title"><?php echo \bkntc__( 'Status' ) ?></div>
                                <div>
                                    <select class="form-control" multiple="multiple" data-placeholder="<?php echo bkntc__('Select')?>" id="calendar_status_filter">
                                        <?php foreach ( $parameters[ 'statuses' ] as $status ): ?>
                                            <option value="<?php echo $status[ 'slug' ] ?>">
                                                <?php echo htmlspecialchars( $status[ 'title' ] ) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="clear_select">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3332 4.6665L4.6665 11.3332M4.6665 4.6665L11.3332 11.3332" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="filter">
                                <div class="filter_title"><?php echo \bkntc__( 'Payment' ) ?></div>
                                <div>
                                    <select class="form-control" multiple="multiple" data-placeholder="<?php echo bkntc__('Select')?>" id="calendar_payment_filter">
	                                    <?php foreach ( $parameters[ 'payments' ] as $payment ): ?>
                                            <option value="<?php echo $payment[ 'slug' ] ?>">
			                                    <?php echo htmlspecialchars( $payment[ 'title' ] ) ?>
                                            </option>
	                                    <?php endforeach; ?>
                                    </select>
                                    <div class="clear_select">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3332 4.6665L4.6665 11.3332M4.6665 4.6665L11.3332 11.3332" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="filter_actions">
                            <button class="clear_filters_btn"><?php echo \bkntc__( 'Clear' ) ?></button>
                            <button class="save_filters_btn"><?php echo \bkntc__( 'Save' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="create_new_appointment">
                <div class="create_new_appointment_btn action_btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M12 5V19M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?php echo \bkntc__( 'New Appointment' ) ?></span>
                </div>
            </div>
        </div>
	</div>

</div>

<div class="fs-calendar-container">
	<div id='fs-calendar'></div>
</div>

<script type="application/javascript">
	localization['TODAY']   = "<?php echo bkntc__('TODAY');?>";
	localization['month']   = "<?php echo bkntc__('month');?>";
	localization['week']    = "<?php echo bkntc__('week');?>";
	localization['day']     = "<?php echo bkntc__('day');?>";
	localization['list']    = "<?php echo bkntc__('list');?>";
    localization['all-day'] = "<?php echo bkntc__('all-day');?>";
    localization['more']    = "<?php echo bkntc__('more');?>";
</script>