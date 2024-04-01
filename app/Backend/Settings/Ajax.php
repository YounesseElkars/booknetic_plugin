<?php

namespace BookneticApp\Backend\Settings;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\Holiday;
use BookneticApp\Models\Timesheet;
use BookneticApp\Backend\Settings\Helpers\BackupService;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceExtra;
use BookneticApp\Backend\Settings\Helpers\LocalizationService;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Translation;
use BookneticApp\Providers\Core\Backend;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\UI\TabUI;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function general_settings ()
	{
		Capabilities::must( 'settings_general' );

		return $this->modalView( 'general_settings', [] );
	}

	public function booking_panel_steps_settings ()
	{
		Capabilities::must( 'settings_booking_panel_steps' );

        TabUI::get('settings_booking_steps')
            ->item('confirm')
            ->addView( Backend::MODULES_DIR . 'Settings/view/tabs/step_confirm.php' , [] , 10 );

		$paramaters = [];

		$getConfirmationNumber = DB::DB()->get_row('SELECT `AUTO_INCREMENT` FROM  `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA`=database() AND `TABLE_NAME`=\''.DB::table(Appointment::getTableName() ).'\'', ARRAY_A);
		$paramaters['confirmation_number'] = (int)$getConfirmationNumber['AUTO_INCREMENT'];
        $paramaters['months'] = [
            'January'        => bkntc__('January'),
            'February'              => bkntc__('February'),
            'March'                 => bkntc__('March'),
            'April'                 => bkntc__('April'),
            'May'                   => bkntc__('May'),
            'June'                  => bkntc__('June'),
            'July'                  => bkntc__('July'),
            'August'                => bkntc__('August'),
            'September'             => bkntc__('September'),
            'October'               => bkntc__('October'),
            'November'              => bkntc__('November'),
            'December'              => bkntc__('December')
        ];
        $paramaters['default_start_month'] = Helper::getOption('booking_panel_default_start_month');

		return $this->modalView( 'booking_panel_steps_settings', $paramaters );
	}

	public function booking_panel_labels_settings ()
	{
		Capabilities::must( 'settings_booking_panel_labels' );

		$paramaters = [];

		$paramaters['locations']        = Location::limit( 5 )->fetchAll();
		$paramaters['staff']            = Staff::limit( 5 )->fetchAll();
		$paramaters['services']         = Service::limit( 5 )->fetchAll();
		$paramaters['service_extras']   = Service::limit( 5 )
            ->leftJoin('service_extras', '', Service::getField('id'), ServiceExtra::getField('service_id') )
            ->where( ServiceExtra::getField('id'), '>', 0 )
            ->fetchAll();

		$gateways_order = Helper::getOption('payment_gateways_order', 'local');
		$gateways_order = explode(',', $gateways_order);

		$paramaters['gateways_order'] = $gateways_order;
		$paramaters['payment_gateways'] = [
			'local'		=>	[
				'title'     =>  bkntc__('Local'),
				'trnslt'    =>  'Local'
			]
		];

		$paramaters['hide_payments']    = Helper::getOption('disable_payment_options', 'off') == 'on';
		$paramaters['other_translates'] = [
			'Any staff'											=> bkntc__('Any staff'),
			'Select an available staff'							=> bkntc__('Select an available staff'),
			// months
			'January'                                           => bkntc__('January'),
			'February'                                          => bkntc__('February'),
			'March'                                             => bkntc__('March'),
			'April'                                             => bkntc__('April'),
			'May'                                               => bkntc__('May'),
			'June'                                              => bkntc__('June'),
			'July'                                              => bkntc__('July'),
			'August'                                            => bkntc__('August'),
			'September'                                         => bkntc__('September'),
			'October'                                           => bkntc__('October'),
			'November'                                          => bkntc__('November'),
			'December'                                          => bkntc__('December'),

			//days of week
			'Mon'                                               => bkntc__('Mon'),
			'Tue'                                               => bkntc__('Tue'),
			'Wed'                                               => bkntc__('Wed'),
			'Thu'                                               => bkntc__('Thu'),
			'Fri'                                               => bkntc__('Fri'),
			'Sat'                                               => bkntc__('Sat'),
			'Sun'                                               => bkntc__('Sun'),

			'Monday'                                            => bkntc__('Monday'),
			'Tuesday'                                           => bkntc__('Tuesday'),
			'Wednesday'                                         => bkntc__('Wednesday'),
			'Thursday'                                          => bkntc__('Thursday'),
			'Friday'                                            => bkntc__('Friday'),
			'Saturday'                                          => bkntc__('Saturday'),
			'Sunday'                                            => bkntc__('Sunday'),

			// select placeholders
			'Select...'                                         => bkntc__('Select...'),

			// messages
			'Please select location.'                           => bkntc__('Please select location.'),
			'Please select staff.'                              => bkntc__('Please select staff.'),
			'Please select service'                             => bkntc__('Please select service'),
			'Please select week day(s)'                         => bkntc__('Please select week day(s)'),
			'Please select week day(s) and time(s) correctly'   => bkntc__('Please select week day(s) and time(s) correctly'),
			'Please select start date'                          => bkntc__('Please select start date'),
			'Please select end date'                            => bkntc__('Please select end date'),
			'Please select date.'                               => bkntc__('Please select date.'),
			'Please select time.'                               => bkntc__('Please select time.'),
			'Please select an available time'                   => bkntc__('Please select an available time'),
			'Please fill in all required fields correctly!'     => bkntc__('Please fill in all required fields correctly!'),
			'Please enter a valid email address!'               => bkntc__('Please enter a valid email address!'),
			'Please enter a valid phone number!'                => bkntc__('Please enter a valid phone number!'),
			'CONFIRM BOOKING'                                   => bkntc__('CONFIRM BOOKING'),

			'Minimum length of "%s" field is %d!'               => bkntc__('Minimum length of "%s" field is %d!'),
			'Maximum length of "%s" field is %d!'               => bkntc__('Maximum length of "%s" field is %d!'),
			'File extension is not allowed!'                    => bkntc__('File extension is not allowed!'),
			'There is no any Location for select.'              => bkntc__('There is no any Location for select.'),
			'Staff not found. Please go back and select a different option.'    => bkntc__('Staff not found. Please go back and select a different option.'),
			'Service not found. Please go back and select a different option.'  => bkntc__('Service not found. Please go back and select a different option.'),
			'There isn\'t any available staff for the selected date/time.'      => bkntc__('There isn\'t any available staff for the selected date/time.'),
			'Extras not found in this service. You can select other service or click the <span class="booknetic_text_primary">"Next step"</span> button.'   => bkntc__('Extras not found in this service. You can select other service or click the <span class="booknetic_text_primary">"Next step"</span> button.'),

			// Recurring Daily
			'Daily'                                             => bkntc__('Daily'),
			'Every'                                             => bkntc__('Every'),
			'DAYS'                                              => bkntc__('DAYS'),
			'Time'                                              => bkntc__('Time'),
			'Start date'                                        => bkntc__('Start date'),
			'End date'                                          => bkntc__('End date'),
			'Times'                                             => bkntc__('Times'),
			// Recurring Monthly
			'Days of week'                                      => bkntc__('Days of week'),
			'On'                                                => bkntc__('On'),
			'Specific day'                                      => bkntc__('Specific day'),
			'First'                                             => bkntc__('First'),
			'Second'                                            => bkntc__('Second'),
			'Third'                                             => bkntc__('Third'),
			'Fourth'                                            => bkntc__('Fourth'),
			'Last'                                              => bkntc__('Last'),

			'DATE'                                              => bkntc__('DATE'),
			'TIME'                                              => bkntc__('TIME'),
			'EDIT'                                              => bkntc__('EDIT'),

			'w'                                              	=> bkntc__('w'),
			'd'                                            		=> bkntc__('d'),
			'h'                                              	=> bkntc__('h'),
			'm'                                              	=> bkntc__('m'),
			's'                                              	=> bkntc__('s'),
		];

        $paramaters = apply_filters('bkntc_labels_settings_translates' , $paramaters );

		return $this->modalView( 'booking_panel_labels_settings', $paramaters );
	}

    public function page_settings ()
    {
        Capabilities::must( 'page_settings' );

        return $this->modalView( 'page_settings', [] );
    }

	public function payments_settings ()
	{
		Capabilities::must( 'settings_payments' );

		$paramaters = [];

		$paramaters['currencies'] = Helper::currencies();
		$paramaters['currency'] = Helper::currencySymbol();

		return $this->modalView( 'payments_settings', $paramaters );
	}

	public function payment_gateways_settings ()
	{
		Capabilities::must( 'settings_payment_gateways' );

        $gateways_order = Helper::getOption('payment_gateways_order', 'local');
        $gateways_order = explode(',', $gateways_order);

        $orderedSubItems = [];
        $unOrderedSubItems = [];

        foreach ( TabUI::get('payment_gateways_settings')->getSubItems() as $subItem )
        {
            if( ( $index = array_search( $subItem->getSlug(),$gateways_order ) ) || ( $index !== false ) )
            {
                $orderedSubItems[$index] = $subItem;
            }else{
                $unOrderedSubItems[] = $subItem;
            }
        }
        ksort($orderedSubItems );
        $orderedSubItems = array_merge( $orderedSubItems, $unOrderedSubItems );

		return $this->modalView( 'payment_gateways_settings',  compact('orderedSubItems'));
	}

	public function company_settings ()
	{
		Capabilities::must( 'settings_company' );

		return $this->modalView( 'company_settings', [] );
	}

	public function business_hours_settings ()
	{
		Capabilities::must( 'settings_business_hours' );

		$paramaters = [];

		$timesheet = Timesheet::where('service_id', 'is', null)->where('staff_id', 'is', null)->fetch();
		$paramaters['timesheet'] = json_decode(isset($timesheet->timesheet) ? $timesheet->timesheet : '', true);

		return $this->modalView( 'business_hours_settings', $paramaters );
	}

	public function holidays_settings ()
	{
		Capabilities::must( 'settings_holidays' );

		$paramaters = [];

		$holidays = Holiday::where('service_id', 'is', null)->where('staff_id', 'is', null)->fetchAll();
		$paramaters['holidays'] = [];

		foreach( $holidays AS $holiday )
		{
			$paramaters['holidays'][ Date::dateSQL( $holiday['date'] ) ] = $holiday['id'];
		}

		$paramaters['holidays'] = json_encode($paramaters['holidays']);

		return $this->modalView( 'holidays_settings', $paramaters );
	}

	public function integrations_facebook_api_settings ()
	{
		Capabilities::must( 'settings_integrations_facebook_api' );

		if ( Helper::isSaaSVersion() )
		{
			return $this->response( false, bkntc__( 'Selected settings not found!' ) );
		}

		return $this->modalView( 'integrations_facebook_api_settings', [] );
	}

	public function integrations_google_login_settings ()
	{
		Capabilities::must( 'settings_integrations_google_login' );

		if ( Helper::isSaaSVersion() )
		{
			return $this->response( false, bkntc__( 'Selected settings not found!' ) );
		}

		return $this->modalView( 'integrations_google_login_settings', [] );
	}

	public function backup_settings ()
	{
		Capabilities::must( 'settings_backup' );

		if ( Helper::isSaaSVersion() )
		{
			return $this->response( false, bkntc__( 'Selected settings not found!' ) );
		}

		return $this->modalView( 'backup_settings', [] );
	}

	/* save action */

	public function save_general_settings ()
	{
		Capabilities::must( 'settings_general' );

        if( ! Capabilities::tenantCan('settings_general'))
            return;

		$timeslot_length					                    = Helper::_post('timeslot_length', '5', 'int');
		$default_appointment_status			                    = Helper::_post('default_appointment_status', '', 'string');
		$slot_length_as_service_duration	                    = Helper::_post('slot_length_as_service_duration', '0', 'int', [0, 1]);
		$min_time_req_prior_booking			                    = Helper::_post('min_time_req_prior_booking', '0', 'int');
		$available_days_for_booking			                    = Helper::_post('available_days_for_booking', '365', 'int');
		$week_starts_on						                    = Helper::_post('week_starts_on', 'sunday', 'string', ['sunday', 'monday']);
		$date_format						                    = Helper::_post('date_format', '', 'string');
		$time_format						                    = Helper::_post('time_format', '', 'string');
		$google_maps_api_key				                    = Helper::_post('google_maps_api_key', '', 'string');
		$client_timezone_enable				                    = Helper::_post('client_timezone_enable', 'off', 'string', ['on', 'off']);
        $google_recaptcha				                        = Helper::_post('google_recaptcha', 'off', 'string', ['on', 'off']);
		$google_recaptcha_site_key			                    = Helper::_post('google_recaptcha_site_key', '', 'string');
        $flexible_timeslot                                      = Helper::_post( 'flexible_timeslot', '1', 'int', [ 0, 1 ] );
        $allow_admins_to_book_outside_working_hours		        = Helper::_post('allow_admins_to_book_outside_working_hours', 'off', 'string', ['on', 'off']);
        $only_registered_users_can_book		                    = Helper::_post('only_registered_users_can_book', 'off', 'string', ['on', 'off']);
        $new_wp_user_on_new_booking		                        = Helper::_post('new_wp_user_on_new_booking', 'off', 'string', ['on', 'off']);
		$google_recaptcha_secret_key		                    = Helper::_post('google_recaptcha_secret_key', '', 'string');
		$remove_branding				                        = Helper::_post('remove_branding', 'off', 'string', ['on', 'off']);
		$timezone		                                        = Helper::_post('timezone', '', 'string');
        $time_restriction_to_change_status                      = Helper::_post('time_restriction_to_change_status', '0', 'int');
        $restriction_type_to_change_status                      = Helper::_post('restriction_type_to_change_appointment_status', 'static', 'string');

		if ( $available_days_for_booking < 0  )
		{
			return $this->response( false, bkntc__( 'Limited booking days cannot be lower than 0 days.' ) );
		}

        if ( ! in_array( $default_appointment_status, array_keys( Helper::getAppointmentStatuses() ) ) )
        {
            return $this->response( false, bkntc__( 'Invalid appointment status' ) );
        }

		Helper::setOption('timeslot_length', $timeslot_length);
		Helper::setOption('allow_admins_to_book_outside_working_hours', $allow_admins_to_book_outside_working_hours);
		Helper::setOption('only_registered_users_can_book', $only_registered_users_can_book);
        Helper::setOption('new_wp_user_on_new_booking', $new_wp_user_on_new_booking);
		Helper::setOption('default_appointment_status', $default_appointment_status);
		Helper::setOption('slot_length_as_service_duration', $slot_length_as_service_duration);
		Helper::setOption('min_time_req_prior_booking', $min_time_req_prior_booking);
		Helper::setOption('available_days_for_booking', $available_days_for_booking );
		Helper::setOption('week_starts_on', $week_starts_on);
		Helper::setOption('date_format', $date_format);
		Helper::setOption('time_format', $time_format);
		Helper::setOption('client_timezone_enable', $client_timezone_enable);
		Helper::setOption('timezone', $timezone);
        Helper::setOption('flexible_timeslot', $flexible_timeslot);

		if( ! Helper::isSaaSVersion() )
		{
			Helper::setOption('google_maps_api_key', $google_maps_api_key);
			Helper::setOption('google_recaptcha', $google_recaptcha);
			Helper::setOption('google_recaptcha_site_key', $google_recaptcha_site_key);
			Helper::setOption('google_recaptcha_secret_key', $google_recaptcha_secret_key);
		}
		else if( Capabilities::tenantCan('remove_branding') )
		{
			Helper::setOption('remove_branding', $remove_branding);
		}

        if( Helper::isSaaSVersion() )
        {
            Helper::setOption('time_restriction_to_change_status', $time_restriction_to_change_status);
            Helper::setOption('restriction_type_to_change_status', $restriction_type_to_change_status);
        }

		return $this->response(true);
	}

	public function save_booking_steps_settings ()
	{
		Capabilities::must( 'settings_booking_panel_steps' );

        if( ! Capabilities::tenantCan('settings_booking_panel_steps'))
            return;

		$hide_address_of_location			= Helper::_post('hide_address_of_location', 'on', 'string', ['on', 'off']);
		$set_email_as_required				= Helper::_post('set_email_as_required', 'on', 'string', ['on', 'off']);
		$set_phone_as_required				= Helper::_post('set_phone_as_required', 'off', 'string', ['on', 'off']);
		$separate_first_and_last_name		= Helper::_post('separate_first_and_last_name', 'off', 'string', ['on', 'off']);
		$redirect_url_after_booking			= Helper::_post('redirect_url_after_booking', '', 'string');
		$time_view_type_in_front			= Helper::_post('time_view_type_in_front', '1', 'string', ['1', '2']);
		$booking_panel_default_start_month	= Helper::_post('booking_panel_default_start_month', '', 'int', [1,2,3,4,5,6,7,8,9,10,11,12]);
		$hide_available_slots				= Helper::_post('hide_available_slots', 'off', 'string', ['on', 'off']);
        $hide_accordion_default             = Helper::_post('hide_accordion_default', 'off', 'string', ['on', 'off']);
		$default_phone_country_code			= Helper::_post('default_phone_country_code', '', 'string');
		$footer_text_staff					= Helper::_post('footer_text_staff', '', 'string', ['1', '2', '3', '4']);
		$any_staff							= Helper::_post('any_staff', 'off', 'string', ['on', 'off']);
		$any_staff_rule						= Helper::_post('any_staff_rule', 'least_assigned_by_day', 'string', ['least_assigned_by_day','most_assigned_by_day','least_assigned_by_week','most_assigned_by_week','least_assigned_by_month','most_assigned_by_month','most_expensive','least_expensive']);
		$skip_extras_step_if_need			= Helper::_post('skip_extras_step_if_need', 'on', 'string', ['on', 'off']);
		$confirm_details_checkbox           = Helper::_post('confirm_details_checkbox', '', 'string' );
        $collapse_service_extras            = Helper::_post('collapse_service_extras', 'off', 'string', [ 'on', 'off' ]);
        $show_all_service_extras            = Helper::_post('show_all_service_extras', 'off', 'string', [ 'on', 'off' ]);

        $hide_gift_discount_row				= Helper::_post('hide_gift_discount_row', 'off', 'string', ['on', 'off']);
        $hide_add_to_google_calendar_btn	= Helper::_post('hide_add_to_google_calendar_btn', 'off', 'string', ['on', 'off']);
        $hide_add_to_icalendar_btn      	= Helper::_post('hide_add_to_icalendar_btn', 'off', 'string', ['on', 'off']);
		$hide_start_new_booking_btn			= Helper::_post('hide_start_new_booking_btn', 'off', 'string', ['on', 'off']);

		$show_step_location					= Helper::_post('show_step_location', 'on', 'string', ['on', 'off']);
		$show_step_staff					= Helper::_post('show_step_staff', 'on', 'string', ['on', 'off']);
		$show_step_service					= Helper::_post('show_step_service', 'on', 'string', ['on', 'off']);
		$show_step_service_extras			= Helper::_post('show_step_service_extras', 'on', 'string', ['on', 'off']);
		$show_step_information				= Helper::_post('show_step_information', 'on', 'string', ['on', 'off']);
		$show_step_cart			            = Helper::_post('show_step_cart', 'on', 'string', ['on', 'off']);
		$show_step_confirm_details			= Helper::_post('show_step_confirm_details', 'on', 'string', ['on', 'off']);
		$hide_confirmation_number			= Helper::_post('hide_confirmation_number', 'off', 'string', ['on', 'off']);
		$confirmation_number				= Helper::_post('confirmation_number', '', 'int');
        $redirect_users_on_confirm_url      = Helper::_post( 'redirect_users_on_confirm_url', '', 'string' );

		$steps_arr							= Helper::_post('steps', '', 'string');

		$steps = [];
		$steps_arr = json_decode( $steps_arr, true );
		if( !is_array( $steps_arr ) )
		{
			return $this->response( false );
		}

		$steps_by_order = [];
		$allowed_steps = ['location', 'staff', 'service', 'service_extras', 'information', 'date_time'];
		foreach ($steps_arr AS $ordr => $step)
		{
			if( is_string( $step ) && in_array( $step, ['location', 'staff', 'service', 'service_extras', 'information', 'date_time'] ) )
			{
				if( isset($steps_by_order[$step]) )
				{
					return $this->response( false );
				}

				$steps[] = $step;
				$steps_by_order[$step] = $ordr;
			}
			else
			{
				return $this->response( false );
			}
		}

		if( count( $steps ) != count( $allowed_steps ) )
		{
			return $this->response( false );
		}

		if( $steps_by_order['service_extras'] < $steps_by_order['service'] )
		{
			return $this->response( false, bkntc__('The Extra step cannot be ordered before the Service step.'));
		}

		if( $steps_by_order['date_time'] < $steps_by_order['service'] )
		{
			return $this->response( false, bkntc__('The Date & Time step cannot be ordered before the Service step.'));
		}

		if( $show_step_location == 'off' && Location::where('is_active', 1)->count() == 0 )
		{
			return $this->response( false, bkntc__('You must add at least one Location to hide the Location step.') );
		}

		if( $show_step_staff == 'off' && Staff::where('is_active', 1)->count() == 0 )
		{
			return $this->response( false, bkntc__('You must add at least one Staff to hide the Staff step.') );
		}

		if( $show_step_service == 'off' && Service::where('is_active', 1)->count() == 0 )
		{
			return $this->response( false, bkntc__('You must add at least one Service to hide the Service step.') );
		}

		if( ! Helper::isSaaSVersion() )
		{
			if( $confirmation_number > 10000000 )
			{
				return $this->response( false, bkntc__('Confirmation number is invalid!') );
			}
			else if( $confirmation_number > 0 )
			{
				$getConfirmationNumber = DB::DB()->get_row('SELECT `AUTO_INCREMENT` FROM  `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA`=database() AND `TABLE_NAME`=\''.DB::table( Appointment::getTableName() ).'\'', ARRAY_A);

				if( (int)$getConfirmationNumber['AUTO_INCREMENT'] > $confirmation_number )
				{
					return $this->response( false, bkntc__('Confirmation number is invalid!') );
				}

				DB::DB()->query("ALTER TABLE `". DB::table( Appointment::getTableName() ) ."` AUTO_INCREMENT=" . (int)$confirmation_number);
			}
		}

        $confirm_details_checkbox = json_decode( $confirm_details_checkbox , true );

		if( ! is_array( $confirm_details_checkbox ) )
        {
            return $this->response( false );
        }

		foreach ( $confirm_details_checkbox as $slug => $value )
        {
            if ( in_array( $value , ['on' , 'off' ] ) )
            {
                Helper::setOption( $slug , $value );
            }
        }


		Helper::setOption('hide_address_of_location', $hide_address_of_location);
		Helper::setOption('set_email_as_required', $set_email_as_required);
		Helper::setOption('set_phone_as_required', $set_phone_as_required);
		Helper::setOption('redirect_url_after_booking', $redirect_url_after_booking);
		Helper::setOption('time_view_type_in_front', $time_view_type_in_front);
		Helper::setOption('booking_panel_default_start_month', $booking_panel_default_start_month);
		Helper::setOption('hide_available_slots', $hide_available_slots);
        Helper::setOption('hide_accordion_default', $hide_accordion_default);
		Helper::setOption('default_phone_country_code', $default_phone_country_code);
		Helper::setOption('footer_text_staff', $footer_text_staff);
		Helper::setOption('any_staff', $any_staff);
		Helper::setOption('any_staff_rule', $any_staff_rule);
		Helper::setOption('skip_extras_step_if_need', $skip_extras_step_if_need);
		Helper::setOption('separate_first_and_last_name', $separate_first_and_last_name);
		Helper::setOption('hide_gift_discount_row', $hide_gift_discount_row);
		Helper::setOption('hide_add_to_google_calendar_btn', $hide_add_to_google_calendar_btn);
		Helper::setOption('hide_add_to_icalendar_btn', $hide_add_to_icalendar_btn);
		Helper::setOption('hide_start_new_booking_btn', $hide_start_new_booking_btn);
        Helper::setOption('collapse_service_extras', $collapse_service_extras);
        Helper::setOption('show_all_service_extras', $show_all_service_extras);

		Helper::setOption('show_step_location', $show_step_location);
		Helper::setOption('show_step_staff', $show_step_staff);
		Helper::setOption('show_step_service', $show_step_service);
		Helper::setOption('show_step_service_extras', $show_step_service_extras);
		Helper::setOption('show_step_information', $show_step_information);
		Helper::setOption('show_step_cart', $show_step_cart);
		Helper::setOption('show_step_confirm_details', $show_step_confirm_details);
		Helper::setOption('hide_confirmation_number', $hide_confirmation_number);

        Helper::setOption( 'redirect_users_on_confirm_url', $redirect_users_on_confirm_url );

		Helper::setOption('steps_order', implode(',', $steps));

        $translations = Helper::_post( 'translations', '', 'string' );
        Helper::setTranslatedOption( $translations, [ 'redirect_url_after_booking' ] );

		return $this->response(true );
	}

	public function save_booking_labels_settings ()
	{
		Capabilities::must( 'settings_booking_panel_labels' );

        if( ! Capabilities::tenantCan('settings_booking_panel_labels'))
            return;

		if( Permission::isDemoVersion() )
		{
			return $this->response(false, "You can't made any changes in the settings because it is a demo version.");
		}

		$language	    = Helper::_post('language', '', 'string');
		$translates	    = Helper::_post('translates', '', 'string');

		if( !$language )
		{
			return $this->response( false );
		}

		$translates = json_decode( $translates, true );

		if( !is_array( $translates ) || empty( $translates ) )
		{
			return $this->response( false );
		}

		if( !LocalizationService::isLngCorrect( $language ) )
		{
			return $this->response( false );
		}

        $translates = apply_filters( 'bkntc_save_booking_labels_settings' , $translates ,$language );

		LocalizationService::saveFiles( $language, $translates );

		// Backup changes to restore them after Update
		$tenant = '';
		if( Helper::isSaaSVersion() )
			$tenant = DIRECTORY_SEPARATOR . Permission::tenantId();

		file_put_contents( Helper::uploadedFile( 'booknetic_' . basename($language) . '.lng', 'languages' . $tenant ), base64_encode( json_encode( $translates ) ) );

		return $this->response(true);
	}

    public function save_page_settings ()
    {
        Capabilities::must( 'page_settings' );

        if( ! Capabilities::tenantCan('page_settings'))
            return;

        $change_status_page_id                                  = Helper::_post('change_status_page_id', '', 'int');
        $time_restriction_to_change_status                      = Helper::_post('time_restriction_to_change_status', '0', 'int');
        $restriction_type_to_change_status                      = Helper::_post('restriction_type_to_change_appointment_status', 'static', 'string');
        $booknetic_signin_page_id                               = Helper::_post('booknetic_signin_page_id', '', 'int');
        $booknetic_signup_page_id                               = Helper::_post('booknetic_signup_page_id', '', 'int');
        $booknetic_forgot_password_page                         = Helper::_post('booknetic_forgot_password_page_id', '', 'int');

        Helper::setOption('time_restriction_to_change_status', $time_restriction_to_change_status);
        Helper::setOption('restriction_type_to_change_status', $restriction_type_to_change_status);

        if( ! Helper::isSaaSVersion() )
        {
            Helper::setOption('change_status_page_id', $change_status_page_id);
            Helper::setOption('regular_sing_in_page', $booknetic_signin_page_id);
            Helper::setOption('regular_sign_up_page', $booknetic_signup_page_id);
            Helper::setOption('regular_forgot_password_page', $booknetic_forgot_password_page);
        }

        return $this->response(true);
    }

	public function save_payments_settings ()
	{
		Capabilities::must( 'settings_payments' );

        if( ! Capabilities::tenantCan('settings_payments') )
            return;

		$currency							= Helper::_post('currency', 'USD', 'string');
		$currency_format					= Helper::_post('currency_format', '1', 'int');
		$currency_symbol					= Helper::_post('currency_symbol', '', 'string');
		$price_number_format				= Helper::_post('price_number_format', '1', 'int');
		$price_number_of_decimals			= Helper::_post('price_number_of_decimals', '2', 'int');
		$max_time_limit_for_payment			= Helper::_post('max_time_limit_for_payment', '10', 'int');
		$deposit_can_pay_full_amount		= Helper::_post('deposit_can_pay_full_amount', 'on', 'string', ['on', 'off']);
        $successful_payment_status		    = Helper::_post('successful_payment_status', '', 'string');
        $failed_payment_status		        = Helper::_post('failed_payment_status', '', 'string');

		$currencyInf = Helper::currencies( $currency );
		if( !$currencyInf )
			$currency = 'USD';

		if( empty( $currency_symbol ) )
			$currency_symbol = '$';

		Helper::setOption('currency', $currency);
		Helper::setOption('currency_format', $currency_format);
		Helper::setOption('currency_symbol', $currency_symbol);
		Helper::setOption('price_number_format', $price_number_format);
		Helper::setOption('price_number_of_decimals', $price_number_of_decimals);
		Helper::setOption('max_time_limit_for_payment', $max_time_limit_for_payment);
		Helper::setOption('deposit_can_pay_full_amount', $deposit_can_pay_full_amount);
        Helper::setOption('successful_payment_status', $successful_payment_status);
        Helper::setOption('failed_payment_status', $failed_payment_status);

		return $this->response(true);
	}

	public function save_payment_gateways_settings ()
	{
		Capabilities::must( 'settings_payment_gateways' );

        if( ! Capabilities::tenantCan('settings_payment_gateways') )
            return;

        $payment_gateways_arr = Helper::_post( 'payment_gateways_order', '', 'string' );
        $gateway_statuses     = Helper::_post( 'gateways_statuses' );
        $labels               = Helper::_post( 'labels' );
        $icon_resets          = Helper::_post( 'icon_resets', [], 'arr' );

		$payment_gateways_arr 			= json_decode( $payment_gateways_arr, true );
		
		if ( ! is_array( $payment_gateways_arr ) )
		{
			return $this->response( false );
		}

		$allowed_gateways = PaymentGatewayService::getInstalledGatewayNames();

		if ( $gateway_statuses )
		{
			if ( ! in_array( 'on', $gateway_statuses ) )
			{
				$gateway_statuses[ 'local' ] = 'on';
			}

			foreach( $gateway_statuses as $slug => $status )
			{
				if( in_array( $slug, $allowed_gateways ) )
				{
					Helper::setOption( $slug . '_payment_enabled', $status);
				}
			}
		}

        if ( $labels )
        {
            foreach ( $labels as $slug => $label )
            {
                if ( in_array( $slug, $allowed_gateways ) )
                {
                    Helper::setOption( $slug . '_label', $label );
                }
            }
        }

        if ( !empty( $_FILES['icons'] ) )
        {
            foreach ( $_FILES['icons']['tmp_name'] as $slug => $tmp_name )
            {
                if ( !array_key_exists( $slug, $icon_resets ) )
                {
                    $extension = strtolower( pathinfo( $_FILES['icons']['name'][ $slug ] )['extension'] );

                    if ( !in_array( $extension, [ 'jpg', 'jpeg', 'png' ] ) )
                    {
                        return $this->response( false, bkntc__( 'Only JPG and PNG images allowed!' ) );
                    }

                    $icon        = md5( base64_encode( rand( 1, 9999999 ) . microtime( true ) ) ) . '.' . $extension;
                    $file_name   = Helper::uploadedFile( $icon, 'Settings' );
                    $oldFileName = Helper::getOption( $slug . '_icon' );

                    if ( !empty( $oldFileName ) )
                    {
                        $oldFileFullPath = Helper::uploadedFile( $oldFileName, 'Settings' );

                        if ( is_file( $oldFileFullPath ) && is_writable( $oldFileFullPath ) )
                            unlink( $oldFileFullPath );
                    }

                    move_uploaded_file( $tmp_name, $file_name );

                    Helper::setOption( $slug . '_icon', $icon );
                }
            }
        }

        foreach ( $icon_resets as $slug => $val )
        {
            Helper::deleteOption( $slug . '_icon' );
        }

        Helper::setOption( 'payment_gateways_order', implode( ',', $payment_gateways_arr ) );

        $translations = Helper::_post( 'translations', '', 'string' );
        if ( ! empty( $translations ) )
        {
            Helper::setTranslatedOption( $translations, [ 'woocommerce_order_details' ] );
        }

		return $this->response(true);
	}

	public function save_business_hours_settings ()
	{
		Capabilities::must( 'settings_business_hours' );

        if( ! Capabilities::tenantCan('settings_business_hours'))
            return;

		$weekly_schedule	= Helper::_post('business_hours', '[]', 'string');

		// check weekly schedule array
		if( empty( $weekly_schedule ) )
		{
			return $this->response(false, bkntc__('Please fill the weekly schedule correctly!'));
		}

		$weekly_schedule = json_decode( $weekly_schedule, true );
		if( !is_array( $weekly_schedule ) || count( $weekly_schedule ) !== 7 )
		{
			return $this->response(false, bkntc__('Please fill the weekly schedule correctly!') );
		}

		$newWeeklySchedule = [];
		foreach( $weekly_schedule AS $dayInfo )
		{
			if(
				(
					isset($dayInfo['start']) && is_string($dayInfo['start'])
					&& isset($dayInfo['end']) && is_string($dayInfo['end'])
					&& isset($dayInfo['day_off']) && is_numeric($dayInfo['day_off'])
					&& isset($dayInfo['breaks']) && is_array($dayInfo['breaks'])
				) === false
			)
			{
				return $this->response(false, bkntc__('Please fill the weekly schedule correctly!') );
			}

			$ws_day_off	= $dayInfo['day_off'];
			$ws_start	= $ws_day_off ? '' : Date::timeSQL( $dayInfo['start'] );
			$ws_end		= $ws_day_off ? '' : ( $dayInfo['end'] == "24:00" ? "24:00": Date::timeSQL( $dayInfo['end'] ) );
			$ws_breaks	= $ws_day_off ? [] : $dayInfo['breaks'];

			$ws_breaks_new = [];
			foreach ( $ws_breaks AS $ws_break )
			{
				if( is_array( $ws_break )
					&& isset( $ws_break[0] ) && is_string( $ws_break[0] )
					&& isset( $ws_break[1] ) && is_string( $ws_break[1] )
				)
				{
				    if( Date::epoch( $ws_break[1] ) <= Date::epoch( $ws_break[0] ) )
                    {
                        return $this->response(false, bkntc__('Please fill the breaks correctly!') );
                    }
					$ws_breaks_new[] = [ Date::timeSQL( $ws_break[0] ) , Date::timeSQL( $ws_break[1] ) ];
				}
			}

			$newWeeklySchedule[ ] = [
				'day_off'	=> $ws_day_off,
				'start'		=> $ws_start,
				'end'		=> $ws_end,
				'breaks'	=> $ws_breaks_new,
			];
		}

		Timesheet::where('service_id', 'is', null)->where('staff_id', 'is', null)->delete();
		Timesheet::insert([ 'timesheet' => json_encode( $newWeeklySchedule ) ]);

		return $this->response(true);
	}

	public function save_holidays_settings ()
	{
		Capabilities::must( 'settings_holidays' );

        if( ! Capabilities::tenantCan('settings_holidays'))
            return;

		$holidays =	Helper::_post('holidays', '', 'string');

		$holidays = json_decode( $holidays, true );
		$holidays = is_array( $holidays ) ? $holidays : [];

		$saveHolidaysId = [];
		foreach ( $holidays AS $holidayInf )
		{
			if(
			!(
				isset( $holidayInf['id'] ) && is_numeric($holidayInf['id'])
				&& isset( $holidayInf['date'] ) && is_string( $holidayInf['date'] ) && !empty( $holidayInf['date'] )
			)
			)
			{
				continue;
			}

			$holidayId = (int)$holidayInf['id'];
			$holidayDate = Date::dateSQL( $holidayInf['date'] );

			if( $holidayId == 0 )
			{
				Holiday::insert([ 'date' => $holidayDate ]);

				$saveHolidaysId[] = DB::lastInsertedId();
			}
			else
			{
				$saveHolidaysId[] = $holidayId;
			}
		}

        $holiday = Holiday::where('staff_id','is',null)
            ->where('service_id','is',null);

        if(!empty( $saveHolidaysId ))
        {
            $holiday->where('id','NOT IN',$saveHolidaysId);
        }

        $holiday->delete();

		return $this->response(true);
	}

	public function save_company_settings ()
	{
		Capabilities::must( 'settings_company' );

        if( ! Capabilities::tenantCan('settings_company'))
            return;

		$company_name		            = Helper::_post('company_name', '', 'string');
		$company_address	            = Helper::_post('company_address', '', 'string');
		$company_phone		            = Helper::_post('company_phone', '', 'string');
		$company_website	            = Helper::_post('company_website', '', 'string');
		$display_logo_on_booking_panel	= Helper::_post('display_logo_on_booking_panel', 'off', 'string', ['on', 'off']);

		$company_image = '';

		if( isset($_FILES['company_image']) && is_string($_FILES['company_image']['tmp_name']) )
		{
			$path_info = pathinfo($_FILES["company_image"]["name"]);
			$extension = strtolower( $path_info['extension'] );

			if( !in_array( $extension, ['jpg', 'jpeg', 'png'] ) )
			{
				return $this->response(false, bkntc__('Only JPG and PNG images allowed!'));
			}

			$company_image = md5( base64_encode(rand(1, 9999999) . microtime(true)) ) . '.' . $extension;
			$file_name = Helper::uploadedFile( $company_image, 'Settings' );

			$oldFileName = Helper::getOption('company_image');
			if( !empty( $oldFileName ) )
			{
				$oldFileFullPath = Helper::uploadedFile( $oldFileName, 'Settings' );

				if( is_file( $oldFileFullPath ) && is_writable( $oldFileFullPath ) )
					unlink( $oldFileFullPath );
			}

			move_uploaded_file( $_FILES['company_image']['tmp_name'], $file_name );
		}

		Helper::setOption('company_name', $company_name);
		Helper::setOption('company_address', $company_address);
		Helper::setOption('company_phone', $company_phone);
		Helper::setOption('company_website', $company_website);
		Helper::setOption('display_logo_on_booking_panel', $display_logo_on_booking_panel);

		if( $company_image != '' )
		{
			Helper::setOption('company_image', $company_image);
		}

        $translations = Helper::_post( 'translations', '', 'string' );
        Helper::setTranslatedOption( $translations, [ 'company_name', 'company_address' ] );

		return $this->response(true);
	}

	public function save_integrations_facebook_api_settings ()
	{
		Capabilities::must( 'settings_integrations_facebook_api' );

        if( ! Capabilities::tenantCan('settings_integrations_facebook_api'))
            return;

		if( Helper::isSaaSVersion() )
		{
			return $this->response( false );
		}

		$facebook_login_enable  = Helper::_post('facebook_login_enable', 'off', 'string', ['on', 'off']);
		$facebook_app_id	    = Helper::_post('facebook_app_id', '', 'string');
		$facebook_app_secret	= Helper::_post('facebook_app_secret', '', 'string');

		if( $facebook_login_enable == 'on' && ( empty($facebook_app_id) || empty($facebook_app_secret) ) )
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

		Helper::setOption('facebook_login_enable', $facebook_login_enable);
		Helper::setOption('facebook_app_id', $facebook_app_id);
		Helper::setOption('facebook_app_secret', $facebook_app_secret);

		return $this->response( true );
	}

	public function save_integrations_google_login_settings ()
	{
		Capabilities::must( 'settings_integrations_google_login' );

        if( ! Capabilities::tenantCan('settings_integrations_google_login'))
            return;

		if( Helper::isSaaSVersion() )
		{
			return $this->response( false );
		}

		$google_login_enable  = Helper::_post('google_login_enable', 'off', 'string', ['on', 'off']);
		$google_login_app_id	    = Helper::_post('google_login_app_id', '', 'string');
		$google_login_app_secret	= Helper::_post('google_login_app_secret', '', 'string');

		if( $google_login_enable == 'on' && ( empty($google_login_app_id) || empty($google_login_app_secret) ) )
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

		Helper::setOption('google_login_enable', $google_login_enable);
		Helper::setOption('google_login_app_id', $google_login_app_id);
		Helper::setOption('google_login_app_secret', $google_login_app_secret);

		return $this->response( true );
	}

	public function get_translation ()
	{
		Capabilities::must( 'settings_booking_panel_labels' );

		$language       = Helper::_post('language', '', 'string');
		$translations   = Helper::_post('transaltions', '[]', 'string');

		if( !$language )
		{
			return $this->response( false );
		}

		$translations = json_decode( $translations, true );

		if( !is_array( $translations ) || empty( $translations ) )
		{
			return $this->response( false );
		}

		if( !LocalizationService::isLngCorrect( $language ) )
		{
			return $this->response( false );
		}

		LocalizationService::setLanguage( $language );

		$result = [];

		foreach ( $translations AS $translation )
		{
			if( is_string( $translation ) && !empty( $translation ) )
			{
				$result[ addslashes( $translation ) ] = html_entity_decode( bkntc__( $translation, [], false), ENT_QUOTES | ENT_XML1, 'UTF-8' );
			}
		}

        $result = apply_filters('settings_booking_panel_labels_load' , $result );



		return $this->response( true, [
			'translations'  =>  $result
		] );
	}

	public function export_data ()
	{
		Capabilities::must( 'settings_backup' );

		if( Permission::isDemoVersion() )
		{
			return $this->response(false, "You can't made any changes in the settings because it is a demo version.");
		}

		if( Helper::isSaaSVersion() )
		{
			return $this->response( false );
		}

		try
		{
			BackupService::export();
		}
		catch ( \Exception $e )
		{
			return $this->response( false, $e->getMessage() );
		}

		return $this->response( true );
	}

	public function import_data ()
	{
		Capabilities::must( 'settings_backup' );

		if( Permission::isDemoVersion() )
		{
			return $this->response(false, "You can't made any changes in the settings because it is a demo version.");
		}

		if( !( isset( $_FILES['file'] ) && is_string( $_FILES['file']['name'] ) && $_FILES['file']['size'] > 0 ) )
		{
			return $this->response( false );
		}

		$backup_file = $_FILES['file']['tmp_name'];

		try
		{
			BackupService::restore( $backup_file );
		}
		catch ( \Exception $e )
		{
			return $this->response( false, $e->getMessage() );
		}

		return $this->response( true );
	}

	public function set_default_language ()
	{
		Capabilities::must( 'settings_booking_panel_labels' );

		if( Permission::isDemoVersion() )
		{
			return $this->response(false, "You can't made any changes in the settings because it is a demo version.");
		}

		$lng = Helper::_post('lng', '', 'string');

		if( !LocalizationService::isLngCorrect( $lng ) )
		{
			return $this->response( false );
		}

		Helper::setOption('default_language', $lng);

		return $this->response( true );
	}

    public function get_available_times_all()
    {
        $search		    = Helper::_post('q', '', 'string');

        $timeslotLength = Helper::getOption('timeslot_length', 5);

        $tEnd = Date::epoch('00:00:00', '+1 days');
        $timeCursor = Date::epoch('00:00:00');
        $data = [];
        while( $timeCursor <= $tEnd )
        {
            $timeId = Date::timeSQL( $timeCursor );
            $timeText = Date::time( $timeCursor );

            if( $timeCursor == $tEnd && $timeId = "00:00" )
            {
                $timeText = "24:00";
                $timeId = "24:00";
            }

            $timeCursor += $timeslotLength * 60;

            // search...
            if( !empty( $search ) && strpos( $timeText, $search ) === false )
            {
                continue;
            }

            $data[] = [
                'id'	=>	$timeId,
                'text'	=>	$timeText
            ];
        }

        return $this->response(true, [ 'results' => $data ]);
    }

}
