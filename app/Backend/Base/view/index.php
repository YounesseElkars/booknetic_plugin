<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\UI\MenuUI;
use BookneticApp\Models\Timesheet;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Providers\UI\Abstracts\AbstractMenuUI;
use BookneticApp\Backend\Settings\Helpers\LocalizationService;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\Helpers\Session;

$localization = [
	// Appearance
	'are_you_sure'					=> bkntc__('Are you sure?'),

	// Appointments
	'select'						=> bkntc__('Select...'),
	'searching'						=> bkntc__('Searching...'),
	'firstly_select_service'		=> bkntc__('Please firstly choose a service!'),
	'fill_all_required'				=> bkntc__('Please fill in all required fields correctly!'),
	'timeslot_is_not_available'		=> bkntc__('This time slot is not available!'),
    'link_copied'                   => bkntc__('Link copied!'),

    // Customers
    'Deleted'                       => bkntc__('Deleted'),

	// Base
	'are_you_sure_want_to_delete'	=> bkntc__('Are you sure you want to delete?'),
	'rows_deleted'					=> bkntc__('Rows deleted!'),
	'delete'                        => bkntc__('DELETE'),
	'cancel'                        => bkntc__('CANCEL'),
	'dear_user'                     => bkntc__('Dear user'),
	'fill_form_correctly'			=> bkntc__('Fill the form correctly!'),
	'saved_successfully'			=> bkntc__('Saved succesfully!'),
	'type_email'   					=> bkntc__('Please type email!'),

	// calendar
	'group_appointment'				=> bkntc__('Group appointment'),
	'new_appointment'				=> bkntc__('NEW APPOINTMENT'),

	// Dashboard
	'loading'					    => bkntc__('Loading...'),
    'bookings_on'                   => bkntc__('bookings on'),
    'Apply'					        => bkntc__('Apply'),
	'Cancel'					    => bkntc__('Cancel'),
	'From'					        => bkntc__('From'),
	'To'					        => bkntc__('To'),

	// Services
	'delete_service_extra'			=> bkntc__('Are you sure that you want to delete this service extra?'),
	'no_more_staff_exist'			=> bkntc__('No more Staff exists for select!'),
	'choose_staff_first'			=> bkntc__('Please choose the staff first'),
	'staff_empty'					=> bkntc__('Staff field cannot be empty'),
	'select_staff'					=> bkntc__('Choose the staff to add'),
	'delete_special_day'			=> bkntc__('Are you sure to delete this special day?'),
	'times_per_month'				=> bkntc__('time(s) per month'),
	'times_per_week'				=> bkntc__('time(s) per week'),
	'every_n_day'					=> bkntc__('Every n day(s)'),
	'delete_service'				=> bkntc__('Are you sure you want to delete this service?'),
	'delete_category'				=> bkntc__('Are you sure you want to delete this category?'),
	'category_name'					=> bkntc__('Category name'),
    'add_category'			        => bkntc__('ADD CATEGORY'),
    'save'			                => bkntc__('SAVE'),
    'no_service_to_show'            => bkntc__('No service to show'),
    'edit_order'                    => bkntc__('EDIT ORDER'),
    'choose_staff'                  => bkntc__('Please choose at least one staff!'),

    //Extra Services
    'service_name'			        => bkntc__('Service name'),
    'min_quantity'			        => bkntc__('Min. quantity'),
    'max_quantity'			        => bkntc__('Max. quantity'),
    'category'			            => bkntc__('Category'),
    'price'			                => bkntc__('Price'),
    'hide_price_booking_panel'      => bkntc__('Hide price in booking panel:'),
    'hide_duration_booking_panel'	=> bkntc__('Hide duration in booking panel:'),
    'duration'			            => bkntc__('Duration'),
    'note'			                => bkntc__('Note'),
    'save_extra'			        => bkntc__('SAVE EXTRA'),
    'default_zero_means_there_is_no_minimum_requirement'	=> bkntc__('Default 0 means there is no minimum requirement.'),
	'to_add_a_category_enter_name_and_press_enter'	=> bkntc__("To create a category, simply enter your desired category name in the field and press 'Enter'."),
    'sure_to_delete_extra_category' => bkntc__( 'Are you sure that you want to delete this category?' ),

    // months
	'January'               		=> bkntc__('January'),
	'February'              		=> bkntc__('February'),
	'March'                 		=> bkntc__('March'),
	'April'                 		=> bkntc__('April'),
	'May'                   		=> bkntc__('May'),
	'June'                  		=> bkntc__('June'),
	'July'                  		=> bkntc__('July'),
	'August'                		=> bkntc__('August'),
	'September'             		=> bkntc__('September'),
	'October'               		=> bkntc__('October'),
	'November'              		=> bkntc__('November'),
	'December'              		=> bkntc__('December'),

	//days of week
	'Mon'                   		=> bkntc__('Mon'),
	'Tue'                   		=> bkntc__('Tue'),
	'Wed'                   		=> bkntc__('Wed'),
	'Thu'                   		=> bkntc__('Thu'),
	'Fri'                   		=> bkntc__('Fri'),
	'Sat'                   		=> bkntc__('Sat'),
	'Sun'                   		=> bkntc__('Sun'),

	'session_has_expired'           => bkntc__('Your session has expired. Please refresh the page and try again.'),
	'graphic_view'                  => bkntc__('Graphic view'),
    'keywords'                      => bkntc__('Keywords'),

    'update_appointment_prices'     => bkntc__('Appointment prices are different from the service price, do you want to update appointment prices?'),
    'update'                        => bkntc__('Update'),
    'dont'                          => bkntc__('Don\'t'),
	'reschedule'					=> bkntc__('Reschedule'),
    'rescheduled_successfully'      => bkntc__('Appointment has been successfully rescheduled!'),
    'reschedule_appointment_confirm'=> bkntc__('Would you like to reschedule the appointment?'),
    'run_workflow_reschedule'       => bkntc__('Run workflows on reschedule'),
    'something_went_wrong'          => bkntc__('Something went wrong...'),
	'copied_to_clipboard'			=> bkntc__( 'Copied to clipboard' ),
	'really_want_to_delete'			=> bkntc__( 'Are you really want to delete?' ),
];
$localization = apply_filters('bkntc_localization' , $localization );

$servicesIsOk       = Service::count() > 0;
$businessHoursIsOk  = Timesheet::where('service_id', 'is', null)->where('staff_id', 'is', null)->count() > 0;
$companyDetailsIsOk = Helper::getOption('company_name', '') != '';
$guidePanelDisabled = isset( $_COOKIE[ 'guide_panel_hidden' ] ) ? $_COOKIE[ 'guide_panel_hidden' ] : 0;

$isRtl = Helper::isRTLLanguage(0, true, Session::get('active_language', get_locale()));

?>
<html <?php echo $isRtl?'dir="rtl"':''; ?>>
<head>
	<title><?php echo htmlspecialchars(Helper::getOption('backend_title', 'Booknetic', false))?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css?ver=5.0.2" type="text/css">

	<link rel="stylesheet" href="<?php echo Helper::assets('css/bootstrap.min.css')?>" type="text/css">

	<link rel="stylesheet" href="<?php echo Helper::assets('css/main.css')?>" type="text/css">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/animate.css')?>" type="text/css">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/select2.min.css')?>" type="text/css">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/select2-bootstrap.css')?>" type="text/css">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/bootstrap-datepicker.css')?>" type="text/css">

	<script type="application/javascript" src="<?php echo Helper::assets('js/jquery-3.3.1.min.js')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/popper.min.js')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/bootstrap.min.js')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/select2.min.js')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/jquery-ui.js')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/jquery.ui.touch-punch.min.js')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/bootstrap-datepicker.min.js')?>"></script>
	<script type="application/javascript" src="<?php echo Helper::assets('js/jquery.nicescroll.min.js')?>"></script>

	<link rel="shortcut icon" href="<?php echo Helper::profileImage( Helper::getOption('whitelabel_logo_sm', 'logo-sm', false), 'Base')?>">

	<script>
		const BACKEND_SLUG = '<?php echo Helper::getSlugName(); ?>';
		const TENANT_CAN_DYNAMIC_TRANSLATIONS = <?php echo json_encode(\BookneticApp\Providers\Core\Capabilities::tenantCan('dynamic_translations')); ?>;
	</script>

	<script src="<?php echo Helper::assets('js/booknetic.js')?>"></script>

	<script>
		var ajaxurl			    =	'?page=<?php echo \BookneticApp\Providers\Core\Backend::getSlugName()?>&ajax=1',
			currentModule	    =	"<?php echo htmlspecialchars( Route::getCurrentModule() )?>",
			assetsUrl		    =	"<?php echo Helper::assets('')?>",
			frontendAssetsUrl	=	"<?php echo Helper::assets('', 'front-end')?>",
			weekStartsOn	    =	"<?php echo Helper::getOption('week_starts_on', 'sunday') == 'monday' ? 'monday' : 'sunday'?>",
			dateFormat  	    =	"<?php echo htmlspecialchars(Helper::getOption('date_format', 'Y-m-d'))?>",
			timeFormat  	    =	"<?php echo htmlspecialchars(Helper::getOption('time_format', 'H:i'))?>",
			localization	    =   <?php echo json_encode($localization)?>,
			isSaaSVersion	    =   <?php echo json_encode(Helper::isSaaSVersion()) ?>,
			fcLocale			=	"<?php echo strtolower(str_replace('_', '-', Helper::getLocaleForTenant())) ?>";
	</script>

	<?php do_action( 'bkntc_enqueue_assets', $currentModule, $currentAction, $fullViewPath );?>

    <?php if ( Helper::canShowTemplates() ): ?>
        <script src="<?php echo Helper::assets( 'js/load-template-selection-popup.js' )?>"></script>
    <?php endif; ?>

</head>
<body style="overflow: auto" class="nice-scrollbar-primary <?php echo $isRtl ? 'rtl ' : ''; ?>minimized_left_menu-">

    <?php $url = Helper::showChangelogs(); if ( ! empty( $url ) ): ?>
        <!-- Changelogs popup after plugin updated -->
        <link rel="stylesheet" href="<?php echo Helper::assets('css/changelogs_popup.css')?>">
        <script type="application/javascript" src="<?php echo Helper::assets( 'js/changelogs_popup.js' ); ?>"></script>
        <div id="changelogsPopup" class="changelogs-popup-container">
            <div class="changelogs-popup">
                <div id="changelogsPopupClose" class="changelogs-popup-close">
                    <i class="fas fa-times"></i>
                </div>
                <iframe src="<?php echo $url; ?>"></iframe>
            </div>
        </div>
    <?php endif; ?>

	<div id="booknetic_progress" class="booknetic_progress_waiting booknetic_progress_done"><dt></dt><dd></dd></div>

	<div class="left_side_menu">

		<div class="l_m_head">
			<img src="<?php echo Helper::profileImage( Helper::getOption('whitelabel_logo', 'logo', false), 'Base')?>" class="head_logo_xl">
			<img src="<?php echo Helper::profileImage( Helper::getOption('whitelabel_logo_sm', 'logo-sm', false), 'Base')?>" class="head_logo_sm">
		</div>

		<div class="d-md-none language-chooser-bar-in-menu">
			<?php if(
				Helper::isSaaSVersion() &&
				Helper::getOption('enable_language_switcher', 'off', false) == 'on' &&
				count( Helper::getOption('active_languages', [], false) ) > 1
			):?>
				<div class="language-chooser-bar">
					<div class="language-chooser" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
						<span><?php echo htmlspecialchars(LocalizationService::getLanguageName( Session::get('active_language', get_locale()) ))?></span>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="dropdown-menu dropdown-menu-right row-actions-area language-switcher-select">
						<?php foreach ( Helper::getOption('active_languages', [], false) AS $active_language ):?>
							<div data-language-key="<?php echo htmlspecialchars($active_language)?>" class="dropdown-item info_action_btn"><?php echo htmlspecialchars(LocalizationService::getLanguageName( $active_language ))?></div>
						<?php endforeach;?>
					</div>
				</div>
			<?php endif;?>
		</div>

		<ul class="l_m_nav">
            <?php foreach ( MenuUI::getItems( MenuUI::MENU_TYPE_LEFT ) AS $menu ) { ?>
                <li class="l_m_nav_item <?php echo $menu->isActive() ? 'active_menu' : ''; ?><?php echo ( ! empty( $menu->getSubItems() ) ? ' is_parent" data-id="' . $menu->getSlug() : '' ); ?>">
                    <a href="<?php echo $menu->getLink(); ?>" class="l_m_nav_item_link">
                        <i class="l_m_nav_item_icon <?php echo $menu->getIcon(); ?>"></i>
                        <span class="l_m_nav_item_text"><?php echo $menu->getTitle(); ?></span>
                        <?php if ( ! empty( $menu->getSubItems() ) ): ?>
                            <i class="l_m_nav_item_icon is_collapse_icon fa fa-chevron-down"></i>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ( ! empty( $menu->getSubItems() ) ): ?>
                    <?php foreach ( $menu->getSubItems() as $submenu ): ?>
                        <li class="l_m_nav_item <?php echo $submenu->isActive() ? 'active_menu' : ''; ?> is_sub" data-parent-id="<?php echo $menu->getSlug(); ?>">
                            <a href="<?php echo $submenu->getLink(); ?>" class="l_m_nav_item_link">
                                <span class="l_m_nav_item_icon_dot"></span>
                                <span class="l_m_nav_item_text"><?php echo $submenu->getTitle(); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php } ?>

            <?php if( !Helper::isSaaSVersion() && \BookneticApp\Providers\Core\Capabilities::userCan('boostore')): ?>
                <li class="l_m_nav_item d-md-none">
                    <a href="admin.php?page=booknetic&module=boostore" class="l_m_nav_item_link">
                        <i class="l_m_nav_item_icon fa fa-puzzle-piece"></i>
                        <span class="l_m_nav_item_text"><?php echo bkntc__('Boostore')?></span>
                    </a>
                </li>
            <?php endif; ?>

			<li class="l_m_nav_item d-md-none">
				<?php
				if( !Helper::isSaaSVersion() )
				{
					?>
					<a href="index.php" class="l_m_nav_item_link">
						<i class="l_m_nav_item_icon fab fa-wordpress"></i>
						<span class="l_m_nav_item_text"><?php echo bkntc__('Back to WordPress')?></span>
					</a>
					<?php
				}
				else
				{
					?>
					<a href="#" class="l_m_nav_item_link share_your_page_btn">
						<i class="l_m_nav_item_icon fa fa-share"></i>
						<span class="l_m_nav_item_text"><?php echo bkntc__('Share your page ')?></span>
					</a>
					<?php
				}
				?>
			</li>

		</ul>

	</div>

	<div class="top_side_menu">
        <div class="t_m_left">
            <?php if ( Helper::isSaaSVersion() ) { ?>
                <button class="btn btn-default btn-lg d-md-inline-block d-none share_your_page_btn" type="button">
                    <i class="fa fa-share mr-2"></i> <span><?php echo bkntc__( 'Share your page' ) ?></span></button>
            <?php } ?>

            <?php foreach ( MenuUI::getItems( AbstractMenuUI::MENU_TYPE_TOP_LEFT ) as $menu ) { ?>
                <a class="btn btn-default btn-lg d-md-inline-block d-none" href="<?php echo $menu->getLink(); ?>"><i class="<?php echo $menu->getIcon(); ?> pr-2"></i>
                    <span><?php echo $menu->getTitle(); ?></span>
                </a>
            <?php } ?>

            <button class="btn btn-default btn-lg d-md-none" type="button" id="open_menu_bar"><i class="fa fa-bars"></i>
            </button>
        </div>
		<div class="t_m_right">

			<div class="user_visit_card">
				<div class="circle_image">
					<img src="<?php echo get_avatar_url(get_current_user_id())?>">
				</div>
				<div class="user_visit_details" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
					<span><?php echo bkntc__('Hello %s', [ wp_get_current_user()->display_name ]) ?> <i class="fa fa-angle-down"></i></span>
				</div>
				<div class="dropdown-menu dropdown-menu-right row-actions-area">
					<?php foreach ( MenuUI::getItems( AbstractMenuUI::MENU_TYPE_TOP_RIGHT ) AS $menu ) { ?>
						<a href="<?php echo $menu->getLink(); ?>" class="dropdown-item info_action_btn"><i class="<?php echo $menu->getIcon(); ?>"></i> <?php echo $menu->getTitle(); ?></a>
					<?php } ?>

					<?php if( Helper::isSaaSVersion() ): ?>
					<a href="#" class="dropdown-item share_your_page_btn"><i class="fa fa-share"></i> <?php echo bkntc__('Share your page')?></a>
					<?php endif; ?>

					<hr class="mt-2 mb-2"/>
					<a href="<?php echo wp_logout_url( home_url() ); ?>" class="dropdown-item "><i class="fa fa-sign-out-alt"></i> <?php echo bkntc__('Log out')?></a>
				</div>
			</div>
		</div>
		<?php if(
			Helper::isSaaSVersion() &&
			Helper::getOption('enable_language_switcher', 'off', false) == 'on' &&
			count( Helper::getOption('active_languages', [], false) ) > 1
		):?>
			<div class="language-chooser-bar d-md-flex d-none">
				<div class="language-chooser" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
					<span><?php echo htmlspecialchars(LocalizationService::getLanguageName( Session::get('active_language', get_locale()) ))?></span>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="dropdown-menu dropdown-menu-right row-actions-area language-switcher-select">
					<?php foreach ( Helper::getOption('active_languages', [], false) AS $active_language ):?>
						<div data-language-key="<?php echo htmlspecialchars($active_language)?>" class="dropdown-item info_action_btn"><?php echo htmlspecialchars(LocalizationService::getLanguageName( $active_language ))?></div>
					<?php endforeach;?>
				</div>
			</div>
		<?php endif;?>
	</div>

	<div class="main_wrapper">
		<?php
		if( isset($childViewFile) && file_exists( $childViewFile ) )
			require_once $childViewFile;
		?>
	</div>

    <?php if ( ! in_array( 'booknetic_staff', Permission::userInfo()->roles ) ): ?>

        <?php if ( $guidePanelDisabled != 1 ): ?>
        <div class="starting_guide_icon" data-actions="0">
            <img src="<?php echo Helper::icon('starting_guide.svg')?>">
        </div>
        <?php endif; ?>

        <div class="starting_guide_panel">
            <div class="starting_guide_head">
                <div class="starting_guide_title">
                    <i class="fa fa-rocket"></i>
                    <?php echo bkntc__('Starting guide')?>
                    <div class="close_starting_guide close-btn" style="float: right; cursor: pointer"><i class="fa fa-times" style=""></i></div>
                </div>
                <div class="starting_guide_progress_bar">
                    <div class="starting_guide_progress_bar_stick"><div class="starting_guide_progress_bar_stick_color"></div></div>
                    <div class="starting_guide_progress_bar_text"><span>01</span><span> / 03</span></div>
                </div>
            </div>
            <div class="starting_guide_body">
                <a href="?page=<?php echo Helper::getSlugName() ?>&module=settings&setting=company" class="starting_guide_steps<?php echo ($companyDetailsIsOk ? ' starting_guide_steps_completed' : '')?>"><?php echo bkntc__('Company details')?></a>
                <a href="?page=<?php echo Helper::getSlugName() ?>&module=settings&setting=business_hours" class="starting_guide_steps<?php echo ($businessHoursIsOk ? ' starting_guide_steps_completed' : '')?>"><?php echo bkntc__('Business hours')?></a>
                <?php
                    $locationsIsOk   = Location::count() > 0;
                    $staffIsOk       = Staff::count() > 0;
                ?>
                <a href="?page=<?php echo Helper::getSlugName() ?>&module=locations" class="starting_guide_steps<?php echo ($locationsIsOk ? ' starting_guide_steps_completed' : '')?>"><?php echo bkntc__('Create location')?></a>
                <a href="?page=<?php echo Helper::getSlugName() ?>&module=staff" class="starting_guide_steps<?php echo ($staffIsOk ? ' starting_guide_steps_completed' : '')?>"><?php echo bkntc__('Create staff')?></a>
                <a href="?page=<?php echo Helper::getSlugName() ?>&module=services" class="starting_guide_steps<?php echo ($servicesIsOk ? ' starting_guide_steps_completed' : '')?>"><?php echo bkntc__('Create service')?></a>
            </div>
        </div>

    <?php endif; ?>

</body>
</html>