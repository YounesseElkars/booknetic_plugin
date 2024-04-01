<?php

namespace BookneticApp\Backend\Services;

use BookneticApp\Models\ExtraCategory;
use BookneticApp\Models\Translation;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\Collection;
use BookneticApp\Models\AppointmentExtra;
use BookneticApp\Models\SpecialDay;
use BookneticApp\Models\Timesheet;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceExtra;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\Common\PaymentGatewayService;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function add_new ()
	{
		$sid		= Helper::_post('id', '0', 'integer');
		$categoryId	= Helper::_post('category_id', '0', 'integer');

		$serviceStuff = [];

		if ( $sid > 0 )
		{
			Capabilities::must( 'services_edit' );

			$serviceInfo	= Service::get( $sid );

			if( !$serviceInfo )
			{
				return $this->response(false, 'Selected Service not found!');
			}

			$categoryId		= $serviceInfo['category_id'];

			$getServiceStuff = ServiceStaff::where('service_id', $sid)->fetchAll();
			foreach( $getServiceStuff AS $staffInf )
			{
				$serviceStuff[(string)$staffInf['staff_id']] = $staffInf;
			}

			$specialDays        = SpecialDay::where('service_id', $sid)->fetchAll();
			$extras             = ServiceExtra::where('service_id', $sid)->fetchAll();

            $customPaymentMethods           = $serviceInfo->getData( 'custom_payment_methods' );
            $customPaymentMethods           = json_decode( $customPaymentMethods );
            $customPaymentMethodsEnabled    = true;
            $onlyVisibleToStaff             = $serviceInfo->getData( 'only_visible_to_staff' );
		}
		else
		{
			Capabilities::must( 'services_add' );
			$allowedLimit = Capabilities::getLimit( 'services_allowed_max_number' );

			if( $allowedLimit > -1 && Service::count() >= $allowedLimit )
			{
				$view = Helper::renderView('Base.view.modal.permission_denied', [
					'text' => bkntc__('You can\'t add more than %d Service. Please upgrade your plan to add more Service.', [ $allowedLimit ] )
				]);

				return $this->response( true, [ 'html' => $view ] );
			}

			$serviceInfo	    = new Collection();
			$specialDays	    = [];
			$extras			    = [];
			$onlyVisibleToStaff = FALSE;
		}

		$timesheet = DB::DB()->get_row(
			DB::DB()->prepare( 'SELECT service_id, timesheet FROM '.DB::table('timesheet').' WHERE ((service_id IS NULL AND staff_id IS NULL) OR (service_id=%d)) '.DB::tenantFilter().' ORDER BY service_id DESC LIMIT 0,1', [ $sid ] ),
			ARRAY_A
		);

		$categories	= ServiceCategory::fetchAll();
		$staff		= Staff::fetchAll();
		$services	= Service::fetchAll();
        $extraCategories    = ExtraCategory::select([ 'id', 'name' ])->fetchAll();

        TabUI::get('services_add')
            ->item( 'details' )
            ->setTitle( bkntc__( 'SERVICE DETAILS' ) )
            ->addView(__DIR__ . '/view/tab/add_new_service_details.php' , [] , 1)
            ->setPriority( 1 );

        if (Capabilities::tenantCan('staff'))
        {
            TabUI::get('services_add')
                ->item( 'staff' )
                ->setTitle( bkntc__( 'STAFF' ) )
                ->addView( __DIR__ . '/view/tab/add_new_staff.php' )
                ->setPriority( 2 );
        }

        TabUI::get('services_add')
            ->item( 'timesheet' )
            ->setTitle( bkntc__( 'TIME SHEET' ) )
            ->addView( __DIR__ . '/view/tab/add_new_timesheet.php' )
            ->setPriority( 3 );

        TabUI::get('services_add')
            ->item( 'extras' )
            ->setTitle( bkntc__( 'EXTRAS' ) )
            ->addView( __DIR__ . '/view/tab/add_new_extras.php' )
            ->setPriority( 4 );

        TabUI::get('services_add')
             ->item( 'settings' )
             ->setTitle( bkntc__( 'SETTINGS' ) )
             ->addView( __DIR__ . '/view/tab/add_new_settings.php' )
             ->setPriority( 5 );

        $timeS = empty($timesheet['timesheet']) ? [
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]] ] : json_decode($timesheet['timesheet'], true);

        if ( empty( $customPaymentMethods ) )
        {
            $customPaymentMethods           = PaymentGatewayService::getEnabledGatewayNames();
            $customPaymentMethodsEnabled    = false;
        }

		return $this->modalView('add_new', [
            'id'                                =>  $sid,
            'only_visible_to_staff'             =>  $onlyVisibleToStaff,
            'custom_payment_methods_enabled'    =>  $customPaymentMethodsEnabled,
            'custom_payment_methods'            =>  $customPaymentMethods,
			'service'					        =>	$serviceInfo,
			'categories'				        =>	$categories,
			'staff'						        =>	$staff,
			'service_staff'				        =>	$serviceStuff,
			'services'					        =>	$services,
			'category'					        =>	$categoryId,
			'extra_categories'					=>	$extraCategories,
			'special_days'				        =>	$specialDays,
			'extras'					        =>	$extras,
			'timesheet'					        =>	$timeS,
			'has_specific_timesheet'	        =>	isset($timesheet['timesheet']) && $timesheet['service_id'] > 0,
            'bring_people'                      =>  Service::getData( $sid, "bring_people", 1 )
		]);
	}

	public function save_service()
	{
		$id						=	Helper::_post('id', '0', 'int');

		if( $id > 0 )
		{
			Capabilities::must( 'services_edit' );
		}
		else
		{
			Capabilities::must( 'services_add' );
		}

		$name					=	Helper::_post('name', '', 'string');
		$category				=	Helper::_post('category', '', 'int');
		$duration				=	Helper::_post('duration', '0', 'int');
		$hide_duration			=	Helper::_post('hide_duration', '0', 'int', ['1']);
		$timeslot_length		=	Helper::_post('timeslot_length', '0', 'int');

		$price					=	Helper::_post('price', null, 'price');
        $deposit_enabled		=	Helper::_post('deposit_enabled', '0', 'int', [ 0, 1 ]);
		$deposit				=	Helper::_post('deposit', null, 'float');
		$deposit_type			=	Helper::_post('deposit_type', null, 'string', ['percent', 'price']);
		$hide_price			    =	Helper::_post('hide_price', '0', 'int', ['1']);

		$buffer_before			=	Helper::_post('buffer_before', '0', 'int');
		$buffer_after			=	Helper::_post('buffer_after', '0', 'int');

		$repeatable				=	Helper::_post('repeatable', '0', 'int', ['0', '1']);

		$fixed_full_period		=	Helper::_post('fixed_full_period', '0', 'int', ['0', '1']);
		$full_period_type		=	Helper::_post('full_period_type', '', 'string', ['month', 'week', 'day', 'time']);
		$full_period_value		=	Helper::_post('full_period_value', '0', 'int');

		$repeat_type			=	Helper::_post('repeat_type', '', 'string', ['monthly', 'weekly', 'daily']);
		$recurring_payment_type	=	Helper::_post('recurring_payment_type', 'first_month', 'string', ['first_month', 'full']);

		$fixed_frequency		=	Helper::_post('fixed_frequency', '0', 'int', ['0', '1']);
		$repeat_frequency		=	Helper::_post('repeat_frequency', '0', 'int');

		$max_capacity			=	Helper::_post('max_capacity', '0', 'int');
		$employees				=	Helper::_post('employees', '', 'string');
		$note					=	Helper::_post('note', '', 'string');
		$color					=	Helper::_post('color', '', 'string');

		$weekly_schedule		=	Helper::_post('weekly_schedule', '', 'string');
		$special_days			=	Helper::_post('special_days', '', 'string');
		$extras					=	Helper::_post('extras', '', 'string');

        $custom_payment_methods_enabled = Helper::_post( 'custom_payment_methods_enabled', 0, 'int', [ 1 ] ) === 1;
        $custom_payment_methods         = explode( ',', Helper::_post( 'custom_payment_methods', '', 'string' ) );
        $only_visible_to_staff          = Helper::_post( 'only_visible_to_staff', 0, 'int', [0, 1] ) === 1;

        $bring_people = Helper::_post( 'bring_people', '1', 'int', [ 0, 1 ] );

        $minimum_time_required_prior_booking = Helper::_post( 'minimum_time_required_prior_booking', '0', 'int' );

        $available_days_for_booking = Helper::_post( 'available_days_for_booking', '0', 'int' );

		if( $id <= 0 )
		{
			$allowedLimit = Capabilities::getLimit( 'services_allowed_max_number' );

			if( $allowedLimit > -1 && Service::count() >= $allowedLimit )
			{
				return $this->response( false, bkntc__('You can\'t add more than %d Service. Please upgrade your plan to add more Service.', [ $allowedLimit ] ) );
			}
		}

		if( empty($name) || empty($category) || is_null( $price ) || is_null( $deposit ) || is_null( $deposit_type ) || !( $duration > 0 ) )
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

		if( ($deposit_type == 'percent' && $deposit > 100) || ($deposit_type == 'price' && $deposit > $price) )
		{
			return $this->response(false, bkntc__('Deposit can not exceed the price!'));
		}

		if( $repeatable )
		{
			if( $fixed_full_period && ( empty( $full_period_type ) || empty( $full_period_value ) ) )
			{
				return $this->response(false, bkntc__('Please fill "Full period" field!'));
			}

			if( empty( $repeat_type ) )
			{
				return $this->response(false, bkntc__('Please fill "Repeat" field!'));
			}

			if( $fixed_frequency && empty( $repeat_frequency ) )
			{
				return $this->response(false, bkntc__('Please fill "Frequency" field!'));
			}

		}
		else
		{
			$fixed_full_period		= 0;
			$repeat_type			= null;
			$fixed_frequency		= 0;
			$recurring_payment_type = null;
		}

		if( $max_capacity < 0 )
		{
			return $this->response(false, bkntc__('Capacity field is wrong!'));
		}

		// check weekly schedule array
		if( empty( $weekly_schedule ) )
		{
			return $this->response(false, bkntc__('Please fill the weekly schedule correctly!'));
		}

		$weekly_schedule = json_decode( $weekly_schedule, true );

		if( !empty($weekly_schedule) && is_array( $weekly_schedule ) && count( $weekly_schedule ) == 7 )
		{
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

                $time_end = $dayInfo['end'] == "24:00" ? "24:00" : Date::timeSQL( $dayInfo['end'] );

				$ws_day_off	= $dayInfo['day_off'];
				$ws_start	= $ws_day_off ? '' : Date::timeSQL( $dayInfo['start'] );
				$ws_end		= $ws_day_off ? '' : $time_end;
				$ws_breaks	= $ws_day_off ? [] : $dayInfo['breaks'];

				$ws_breaks_new = [];
				foreach ( $ws_breaks AS $ws_break )
				{
					if( is_array( $ws_break )
					    && isset( $ws_break[0] ) && is_string( $ws_break[0] )
					    && isset( $ws_break[1] ) && is_string( $ws_break[1] )
					    && Date::epoch( $ws_break[1] ) > Date::epoch( $ws_break[0] )
					)
					{
						$ws_breaks_new[] = [ Date::timeSQL( $ws_break[0] ) , Date::timeSQL( $ws_break[1] ) ];
					}
				}

				$newWeeklySchedule[] = [
					'day_off'	=> $ws_day_off,
					'start'		=> $ws_start,
					'end'		=> $ws_end,
					'breaks'	=> $ws_breaks_new,
				];
			}
		}

		$checkIfNameExist = Service::where('name', $name)->where('category_id', $category)->where('id', '!=', $id)->fetch();

		if( $checkIfNameExist )
		{
			return $this->response(false, bkntc__('This service name is already exist! Please choose an other name.'));
		}

		$employees = json_decode( $employees, true );
		$employees = is_array($employees) ? $employees : [];

		$newEmployeesList = [];

		foreach( $employees AS $employeeInf )
		{
			if(
				isset( $employeeInf[0] ) && is_numeric($employeeInf[0]) && $employeeInf[0] > 0
				&& isset( $employeeInf[1] ) && is_numeric($employeeInf[1]) && $employeeInf[1] >= -1
				&& isset( $employeeInf[2] ) && is_numeric($employeeInf[2]) && $employeeInf[2] >= -1
				&& isset( $employeeInf[3] ) && is_string($employeeInf[3]) && in_array( $employeeInf[3], ['percent', 'price'] )
			)
			{
				if( isset( $newEmployeesList[(int)$employeeInf[0]] ) )
				{
					return $this->response(false, bkntc__('Duplicate Staff selected!'));
				}

				if( $employeeInf[1] != -1 && ( ($employeeInf[3] == 'percent' && $employeeInf[2] > 100) || ($employeeInf[3] == 'price' && $employeeInf[2] > $employeeInf[1]) ) )
				{
					return $this->response(false, bkntc__('Deposit can not exceed the price!'));
				}

				$newEmployeesList[(int)$employeeInf[0]] = [
					Math::floor( $employeeInf[1] ),
					Math::floor( $employeeInf[2] ),
					$employeeInf[3]
				];
			}
		}

		$price = Math::floor( $price );
		$deposit = $deposit_enabled === 1 ? Math::floor( $deposit ) : Math::floor( 0 );

		$image = '';

		if( isset($_FILES['image']) && is_string($_FILES['image']['tmp_name']) )
		{
			$path_info = pathinfo($_FILES["image"]["name"]);
			$extension = strtolower( $path_info['extension'] );

			if( !in_array( $extension, ['jpg', 'jpeg', 'png'] ) )
			{
				return $this->response(false, bkntc__('Only JPG and PNG images allowed!'));
			}

			$image = md5( base64_encode(rand(1, 9999999) . microtime(true)) ) . '.' . $extension;
			$file_name = Helper::uploadedFile( $image, 'Services' );

			move_uploaded_file( $_FILES['image']['tmp_name'], $file_name );
		}

        $custom_payment_methods = array_intersect( $custom_payment_methods, PaymentGatewayService::getInstalledGatewayNames() );

        if ( $custom_payment_methods_enabled && empty( $custom_payment_methods ) )
        {
            return $this->response(false, bkntc__('At least one payment method should be selected!'));
        }

		$sqlData = [
			'name'						=>	$name,

			'price'						=>	$price,
			'deposit'					=>	$deposit,
			'deposit_type'				=>	$deposit_type,
			'hide_price'				=>	$hide_price,
			'hide_duration'				=>	$hide_duration,

			'category_id'				=>	$category,
			'duration'					=>	$duration,
			'timeslot_length'			=>	$timeslot_length,
			'buffer_before'				=>	$buffer_before,
			'buffer_after'				=>	$buffer_after,

			'is_recurring'				=>	$repeatable,

			'full_period_type'			=>	$fixed_full_period ? $full_period_type : null,
			'full_period_value'			=>	$fixed_full_period ? $full_period_value : 0,

			'repeat_type'				=>	$repeat_type,
			'recurring_payment_type'	=>	$recurring_payment_type,

			'repeat_frequency'			=>	$fixed_frequency ? $repeat_frequency : 0,

			'max_capacity'				=>	$max_capacity,

			'notes'						=>	$note,
			'color'						=>	$color,
			'image'						=>	$image,

			'is_visible'				=>	1
		];

		$sqlData = apply_filters('service_sql_data', $sqlData);

		if( $id > 0 )
		{
			$isEdit = true;

			if( empty( $image ) )
			{
				unset( $sqlData['image'] );
			}
			else
			{
				$getOldInf = Service::get( $id );

				if( !empty( $getOldInf['image'] ) )
				{
					$filePath = Helper::uploadedFile( $getOldInf['image'], 'Services' );

					if( is_file( $filePath ) && is_writable( $filePath ) )
					{
						unlink( $filePath );
					}
				}
			}

			Service::where('id', $id)->update( $sqlData );
			ServiceStaff::where('service_id', $id)->delete();
			Timesheet::where('service_id', $id)->delete();
		}
		else
		{
			$isEdit = false;

			$sqlData['is_active'] = 1;

			Service::insert( $sqlData );
			$id = DB::lastInsertedId();
		}

        if ( $isEdit && ! $custom_payment_methods_enabled )
        {
            Service::deleteData( $id, 'custom_payment_methods' );
        }
        else if ( $custom_payment_methods_enabled )
        {
            Service::setData( $id, 'custom_payment_methods', json_encode( $custom_payment_methods ) );
        }

        Service::setData( $id, 'bring_people', $bring_people );
        Service::setData( $id, 'only_visible_to_staff', $only_visible_to_staff );
        Service::setData( $id, 'minimum_time_required_prior_booking',
            (int)Helper::getMinTimeRequiredPriorBooking() === (int)$minimum_time_required_prior_booking
                ? -1 // it should always be equal to default settings until it is manually changed
                : $minimum_time_required_prior_booking
        );
        Service::setData( $id, 'available_days_for_booking', $available_days_for_booking );

		if( isset( $newWeeklySchedule ) )
		{
			Timesheet::insert([
				'timesheet'		=>	json_encode( $newWeeklySchedule ),
				'service_id'	=>	$id
			]);
		}

		// write special days data
		$special_days = json_decode($special_days, true);
		$special_days = is_array( $special_days ) ? $special_days : [];

		$saveSpecialDays = [];
		foreach ( $special_days AS $special_day )
		{
			if(
				(
					isset($special_day['date']) && is_string($special_day['date'])
					&& isset($special_day['start']) && is_string($special_day['start'])
					&& isset($special_day['end']) && is_string($special_day['end'])
					&& isset($special_day['breaks']) && is_array($special_day['breaks'])
				) === false
			)
			{
				continue;
			}

			$sp_id		= isset($special_day['id']) ? (int)$special_day['id'] : 0;
			$sp_date	= Date::dateSQL( Date::reformatDateFromCustomFormat( $special_day['date'] ) );
			$sp_start	= Date::time( $special_day['start'] );
			$sp_end		= ( $special_day['end'] == "24:00" ? "24:00": Date::timeSQL( $special_day['end'] ) ) ;
			$sp_breaks	= $special_day['breaks'];

			$sp_breaks_new = [];
			foreach ( $sp_breaks AS $sp_break )
			{
				if( is_array( $sp_break )
				    && isset( $sp_break[0] ) && is_string( $sp_break[0] )
				    && isset( $sp_break[1] ) && is_string( $sp_break[1] )
				    && Date::epoch( $sp_break[1] ) > Date::epoch( $sp_break[0] )
				)
				{
					$sp_breaks_new[] = [ Date::timeSQL( $sp_break[0] ) , $sp_break[1] == "24:00" ? "24:00" : Date::timeSQL( $sp_break[1] ) ];
				}
			}

			$spJsonData = json_encode([
				'day_off'	=> 0,
				'start'		=> $sp_start,
				'end'		=> $sp_end,
				'breaks'	=> $sp_breaks_new,
			]);

			if( $sp_id > 0 )
			{
				SpecialDay::where('id', $sp_id)->where('service_id', $id)->update([
					'timesheet' =>	$spJsonData,
					'date'		=>	$sp_date
				]);

				$saveSpecialDays[] = $sp_id;
			}
			else
			{
				SpecialDay::insert([
					'timesheet'		=>	$spJsonData ,
					'date'			=>	$sp_date,
					'service_id'	=>	$id
				]);

				$saveSpecialDays[] = DB::lastInsertedId();
			}
		}

		if( $isEdit )
		{
			$queryWhere = '';
			if( !empty( $saveSpecialDays ) )
			{
				$queryWhere = " AND id NOT IN ('" . implode( "', '", $saveSpecialDays ) . "')";
			}

			DB::DB()->query( DB::DB()->prepare( 'DELETE FROM `' . DB::table('special_days') . '` WHERE service_id=%d ' . $queryWhere, [$id] ) );
		}

        if (!Capabilities::tenantCan('staff'))
        {
            $newEmployeesList = [
                Staff::limit(1)->fetch()->id => [ -1, -1, 'percent' ]
            ];
        }

		// insert new joined employee IDs
		foreach( $newEmployeesList AS $employeeId => $_price )
		{
			ServiceStaff::insert([
				'service_id'	=>	$id,
				'staff_id'		=>	$employeeId,
				'price'			=>	$_price[0],
				'deposit'		=>	$_price[1],
				'deposit_type'	=>	$_price[2]
			]);
		}

		// join new created extra IDs
		if( !$isEdit )
		{
			$extras = json_decode($extras, true);
			$extrasArr = [];

			foreach ( $extras as $extraId)
			{
				if( is_numeric( $extraId ) && $extraId > 0 )
				{
					$extrasArr[] = (int)$extraId;
				}
			}

			if( ! empty( $extrasArr ) )
			{
                ServiceExtra::where( 'id', $extrasArr )->update( [ 'service_id'  => $id ] );
			}
		}

        $servicesOrderOption = json_decode( Helper::getOption( "services_order" ), TRUE );
        if ( ! empty( $servicesOrderOption ) && is_array( $servicesOrderOption ) )
        {
            $savedCategory = isset( $servicesOrderOption[ $category ] ) ? $servicesOrderOption[ $category ] : [];

            if ( $isEdit )
            {
                /* Eger edit edirikse onda baxiriqki servisin kateqoriyasi deyisib ya yox
                ** Eger kateqoriya deyismeyibse onda hecne etmirik */
                if ( ! in_array( $id, $savedCategory ) )
                {
                    // First find service's previous category
                    $previousCategory = null;
                    foreach ( $servicesOrderOption AS $key => $serviceIDS )
                    {
                        if ( in_array( $id, $serviceIDS ) )
                        {
                            $previousCategory = $key;
                        }
                    }

                    // Eger kateqoriya idsi tapilirsa, servisin kateqoriyasini deyisdiyimiz ucun, serivisin id-ni array dan silirik
                    if ( ! is_null( $previousCategory ) && isset( $servicesOrderOption[ $previousCategory ] ) )
                    {
                        $previousCategoryData = $servicesOrderOption[ $previousCategory ];
                        if ( ( $key = array_search( $id , $previousCategoryData ) ) !== false ) {
                            unset( $previousCategoryData[ $key ] );
                        }
                        $servicesOrderOption[ $previousCategory ] = $previousCategoryData;
                    }

                    $savedCategory[]                  = $id;
                    $servicesOrderOption[ $category ] = $savedCategory;
                }

            } else
            {
                // If not editing just insert new id
                $savedCategory[]                  = $id;
                $servicesOrderOption[ $category ] = $savedCategory;
            }

            Helper::setOption( "services_order", json_encode( $servicesOrderOption ) );
        }

        Service::handleTranslation( $id );

		return $this->response(true, [ 'id' => $id ] );
	}

	public function service_delete()
	{
		Capabilities::must( 'services_delete' );

		$id = Helper::_post('id', '0', 'integer');

		if( !( $id > 0 ) )
		{
			return $this->response(false);
		}

		Controller::_delete( [ $id ] );

		Service::where('id' , $id)->delete();
        Service::deleteData( $id );

		return $this->response(true);
	}

	public function hide_service()
	{
		Capabilities::must( 'services_edit' );

		$service_id	= Helper::_post('service_id', '', 'int');

		if( !( $service_id > 0 ) )
		{
			return $this->response(false);
		}

		$service = Service::get( $service_id );

		if( !$service )
		{
			return $this->response( false );
		}

		$new_status = $service['is_active'] == 1 ? 0 : 1;

		Service::where('id', $service_id)->update([ 'is_active' => $new_status ]);

		return $this->response( true );
	}


    public function add_new_category()
    {
	    Capabilities::must( 'services_add_category' );

	    $categories	= ServiceCategory::fetchAll();

        return $this->modalView('add_new_category', [
            'categories' =>	$categories,
        ]);
    }

	public function category_delete()
	{
		Capabilities::must( 'services_delete_category' );

		$id = Helper::_post('id', '0', 'integer');

		if( !( $id > 0 ) )
		{
			return $this->response(false);
		}

		// fetch all categories
		$allCategories = ServiceCategory::fetchAll();
		$categoriesTree = [];
		foreach ( $allCategories AS $category )
		{
			$parentId	= $category['parent_id'];
			$categId	= $category['id'];

			$categoriesTree[ $parentId ][ $categId ] = $category['name'];
		}

		// collect all sub categories
		$subCategories = [ ];

		self::getSubCategs( $id, $categoriesTree, $subCategories );

		if( !empty($subCategories) )
		{
			return $this->response( false, bkntc__( 'Firstly remove sub categories!' ) );
		}

        // raw query used to prevent user role manager ( or other addons/functionalities ) filter allowing to delete categories
        // even though other staffs have services in given category
        $servicesTable = DB::table( 'services' );
        $services = DB::DB()->get_results( DB::DB()->prepare( "SELECT * FROM {$servicesTable} WHERE category_id = %d", $id ), ARRAY_A );

		if( $services )
		{
			return $this->response( false, bkntc__( 'There are some services in this category.' ) );
		}

		ServiceCategory::where('id', $id)->delete();

		return $this->response(true);
	}

	public function category_save()
	{
		$id		= Helper::_post('id', 0, 'integer');

		if( $id > 0 )
		{
			Capabilities::must( 'services_edit_category' );
		}
		else
		{
			Capabilities::must( 'services_add_category' );
		}

		$name	= Helper::_post('name', '', 'string');
		$parent	= Helper::_post('parent_id', '0', 'integer');

		if( empty( $name ) || ( $id == 0 && is_null($parent) ) )
		{
			return $this->response(false);
		}

		if( $id > 0 )
		{
			ServiceCategory::where('id', $id)->update(['name' => $name]);
		}
		else
		{
			$checkIfNameExist = ServiceCategory::where( 'name', $name )->where( 'parent_id', $parent )->where( 'id', '!=', $id )->fetch();

			if( $checkIfNameExist )
			{
				return $this->response(false, bkntc__('This category is already exist! Please choose an other name.'));
			}

			ServiceCategory::insert([
				'name'		=>	$name,
				'parent_id'	=>	$parent
			]);

			$id = DB::lastInsertedId();

            $servicesOrder = json_decode( Helper::getOption( "services_order" ), TRUE );
            if ( ! empty ( $servicesOrder ) && is_array( $servicesOrder ) ) {

                if ( $parent == 0 ) {
                    $servicesOrder[ $id ] = [];
                } else {
                    $newServiceOrder = [];
                    foreach ( $servicesOrder as $k => $items ) {
                        $newServiceOrder[ $k ] = $items;
                        if ( $k == $parent ) {
                            $newServiceOrder[ $id ] = [];
                        }
                    }
                    $servicesOrder = $newServiceOrder;
                }
                Helper::setOption( "services_order", json_encode( $servicesOrder ) );
            }
		}

        ServiceCategory::handleTranslation( $id );

		return $this->response(true, ['id' => $id]);
	}


	public function save_extra()
	{
		$id				=	Helper::_post('id', '0', 'int');

		if( $id > 0 )
		{
			Capabilities::must( 'services_edit_extra' );
		}
		else
		{
			Capabilities::must( 'services_add_extra' );
		}

		$service_id		=	Helper::_post('service_id', '0', 'int');
		$name			=	Helper::_post('name', '', 'string');
		$duration		=	Helper::_post('duration', '0', 'int');
		$hide_duration	=	Helper::_post('hide_duration', '0', 'int', ['1', '0']);
		$price			=	Helper::_post('price', null, 'price');
		$hide_price		=	Helper::_post('hide_price', '0', 'int', ['1', '0']);
		$min_quantity	=	Helper::_post('min_quantity', '0', 'int');
		$max_quantity	=	Helper::_post('max_quantity', '0', 'int');
        $category_id    =   Helper::_post('category_id', null, 'int');
        $extra_notes    =   Helper::_post('extra_notes', null, 'string');

        $allowedLimit = Capabilities::getLimit( 'service_extras_allowed_max_number' );

        if( $allowedLimit > -1 && ServiceExtra::count() >= $allowedLimit )
        {
            return $this->response( false, bkntc__('You can\'t add more than %d Service Extras. Please upgrade your plan to add more Service Extra.', [ $allowedLimit ] ) );
        }

		if( empty($name) || is_null( $price ) )
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

        if( !( $min_quantity >= 0 ) )
        {
            return $this->response(false, bkntc__('Min quantity can not be less than zero!'));
        }

		if( !( $max_quantity > 0 ) )
		{
			return $this->response(false, bkntc__('Max quantity can not be zero!'));
		}

        if ( !( $max_quantity >= $min_quantity ) )
        {
            return $this->response(false, bkntc__('Max quantity can not be less than Min quantity!'));
        }

		$price = Math::floor( $price );

		$image = '';

		if( isset($_FILES['image']) && is_string($_FILES['image']['tmp_name']) )
		{
			$path_info = pathinfo($_FILES["image"]["name"]);
			$extension = strtolower( $path_info['extension'] );

			if( !in_array( $extension, ['jpg', 'jpeg', 'png'] ) )
			{
				return $this->response(false, bkntc__('Only JPG and PNG images allowed!'));
			}

			$image = md5( base64_encode(rand(1, 9999999) . microtime(true)) ) . '.' . $extension;
			$file_name = Helper::uploadedFile( $image, 'Services' );

			move_uploaded_file( $_FILES['image']['tmp_name'], $file_name );
		}

        if ( empty( $extra_notes ) )
        {
            $extra_notes = NULL;
        }

		$sqlData = [
			'name'				=>	$name,
			'price'				=>	$price,
			'hide_price'		=>	$hide_price,
			'duration'			=>	$duration,
			'hide_duration'		=>	$hide_duration,
            'min_quantity'      =>  $min_quantity,
			'max_quantity'		=>	$max_quantity,
			'image'				=>	$image,
			'service_id'		=>	$service_id > 0 ? $service_id : null,
            'category_id'       =>  $category_id,
            'notes'             =>  $extra_notes,
		];

		if( $id > 0 )
		{
			if( empty( $image ) )
			{
				unset( $sqlData['image'] );
			}
			else
			{
				$getOldInf = ServiceExtra::get( $id );

				if( !empty( $getOldInf['image'] ) )
				{
					$filePath = Helper::uploadedFile( $getOldInf['image'], 'Services' );

					if( is_file( $filePath ) && is_writable( $filePath ) )
					{
						unlink( $filePath );
					}
				}
			}

			ServiceExtra::where('id', $id)->update( $sqlData );
		}
		else
		{
			$sqlData['is_active'] = 1;

			ServiceExtra::insert( $sqlData );
			$id = DB::lastInsertedId();
		}

        ServiceExtra::handleTranslation( $id );

		return $this->response(true, [
			'id'		=>	$id,
			'price'		=>	Helper::price( $price ),
			'duration'	=>	!$duration ? '-' : Helper::secFormat( $duration * 60 )
		]);
	}

	public function delete_extra()
	{
		Capabilities::must( 'services_delete_extra' );

		$id		=	Helper::_post('id', '0', 'int');

		if( !( $id > 0 ) )
		{
			return $this->response(false);
		}

		// check if appointment exist
		$checkAppointments = AppointmentExtra::where('extra_id', $id)->fetch();
		if( $checkAppointments )
		{
			return $this->response(false, bkntc__('This extra is using some Appointments (ID: %d). Firstly remove them!' , [ (int)$checkAppointments['appointment_id'] ]));
		}

		ServiceExtra::where('id', $id)->delete();

        Translation::where([ 'table_name' => 'service_extras', 'row_id' => $id ])->delete(); // TODO: Umumi Translator traitine cixmalidir

		return $this->response(true);
	}

	public function copy_extras()
	{
		Capabilities::must( 'services_add' );

		$val 		= Helper::_post('val', '', 'int');
		$extraId 	= Helper::_post('extraId', '', 'int');

		$extra      = ServiceExtra::where('id', $extraId)->fetch();

        $extraTranslations = Translation::where( [
            'row_id'      => $extraId,
            'table_name'  => 'service_extras',
            'column_name' => 'name'
        ] )->fetchAll();

        //todo: our orWhere() function needs to be compatible with array insertions, first combinator can be 'OR' but our QueryBuilder works without parentheses so this can create unexpected results.
        //polyfill
        $extraTranslations = array_merge( $extraTranslations, Translation::where([
            'row_id'      => $extraId,
            'table_name'  => 'service_extras',
            'column_name' => 'notes'
        ])->fetchAll() );

		$sqlData = [
			'name'				=>	$extra['name'],
			'price'				=>	$extra['price'],
			'hide_price'		=>	$extra['hide_price'],
			'duration'			=>	$extra['duration'],
			'hide_duration'		=>	$extra['hide_duration'],
			'is_active'			=>	$extra['is_active'],
			'min_quantity'		=>	$extra['min_quantity'],
			'max_quantity'		=>	$extra['max_quantity'],
			'image'				=>	$extra['image'],
            'notes'             =>  $extra['notes']
		];

		if( $val == 1 )
		{
			$category = Service::select(['category_id'])->where('id', $extra['service_id'])->fetch();
			$services = Service::select(['id'])->where('category_id', $category['category_id'])->fetchAll();

			if(	is_null($category) || is_null($services))
			{
				return $this->response(false, bkntc__('There is no category or service attached with this extra!'));
			}

        }
		else
		{
			$services = Service::fetchAll();
        }

        foreach ( $services as $service)
        {
            $inserted = ServiceExtra::where('service_id', $service['id'])->where('name', $sqlData['name'])->fetchAll();

            if( empty( $inserted ) )
            {
                $sqlData['service_id'] = $service['id'];
                ServiceExtra::insert($sqlData);
                $id = ServiceExtra::lastId();
                foreach ( $extraTranslations as $translation )
                {
                    Translation::insert( [
                        'table_name'  => 'service_extras',
                        'column_name' => $translation[ 'column_name' ],
                        'row_id'      => $id,
                        'locale'      => $translation[ 'locale' ],
                        'value'       => $translation[ 'value' ]
                    ] );
                }
            }
        }

        return $this->response(true, ['msg' => bkntc__('Success!')]);
	}

	public function hide_extra()
	{
		Capabilities::must( 'services_edit_extra' );

		$id		=	Helper::_post('id', '0', 'int');
		$status	=	Helper::_post('status', '1', 'string', ['0', '1']);

		if( !( $id > 0 ) )
		{
			return $this->response(false);
		}

		ServiceExtra::where('id', $id)->update([ 'is_active' => $status ]);

		return $this->response(true);
	}

	public function get_extra_data()
	{
		Capabilities::must( 'services' );

		$id	= Helper::_post('id', '0', 'int');

		if( !( $id > 0 ) )
		{
			return $this->response(false);
		}

		$extraInf = ServiceExtra::get( $id );

		if( !$extraInf )
		{
			return $this->response(false, bkntc__('Requested Service Extra not found!'));
		}

		return $this->response(true, [
            'id'            =>  $id,
			'name'			=>	htmlspecialchars($extraInf['name']),
			'price'			=>	Helper::price(Math::floor( $extraInf['price'] ),false),
			'hide_price'	=>	(int)$extraInf['hide_price'],
			'duration'		=>	(int)$extraInf['duration'],
			'duration_txt'	=>	Helper::secFormat( (int)$extraInf['duration'] * 60 ),
			'hide_duration'	=>	(int)$extraInf['hide_duration'],
			'image'			=>	Helper::profileImage( $extraInf['image'], 'Services' ),
			'min_quantity'	=>	null ? 0 : (int)$extraInf['min_quantity'],
			'max_quantity'	=>	(int)$extraInf['max_quantity'],
            'category_id'   => (int)$extraInf['category_id'],
            'notes'         => htmlspecialchars($extraInf['notes']),
		]);
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

	public function get_times_with_format()
	{
		$search			= Helper::_post('q', '', 'string');
		$exclude_zero	= Helper::_post('exclude_zero', '', 'string');

        $include_defaults = Helper::_post( 'include_defaults', false, 'boolean' );

		$timeslotLength = Helper::getOption('timeslot_length', 5);

		// $tEnd = 7 * 24 * 3600;
		$tEnd = 31 * 24 * 3600;
		$timeCursor = 0;
		$data = [];

        if( $include_defaults )
        {
            $data[] = [
                'id' => 0,
                'text' => bkntc__( 'Default' )
            ];

            $data[] = [
                'id' => -1,
                'text' => bkntc__( 'Slot length as service duration' )
            ];
        }

		while( $timeCursor <= $tEnd )
		{
			if( $exclude_zero == 'true' && $timeCursor <= 0 )
			{
				$timeCursor += $timeslotLength * 60;
				continue;
			}

			$timeText = Helper::secFormat( $timeCursor );

			// search...
			if( !( !empty( $search ) && strpos( $timeText, $search ) === false ) )
			{
				$data[] = [
					'id'	=>	$timeCursor / 60,
					'text'	=>	$timeText
				];
			}

			if( $timeCursor >= 24 * 3600 )
			{
				$timeCursor += 24 * 3600;
			}
			else
			{
				$timeCursor += $timeslotLength * 60;
			}
		}

		return $this->response(true, [ 'results' => $data ]);
	}

	private static function getSubCategs( $id, &$categories, &$subCategories )
	{
		if( isset( $categories[ $id ] ) )
		{
			foreach( $categories[ $id ] AS $cId => $cName )
			{
				$subCategories[] = (int)$cId;
				self::getSubCategs( $cId, $categories, $subCategories );
			}
		}
	}

    public function save_services_order() {
        $activeCategory = Helper::_post( "active_category", "0", "int" );
        $servicesOrder  = Helper::_post( "service_order", "", "string" );
        $categoryOrder  = Helper::_post( "category_order", "", "string" );

        $servicesOrder = json_decode( $servicesOrder );
        $categoryOrder = json_decode( $categoryOrder );
        if ( ! is_array( $servicesOrder ) || ! is_array( $categoryOrder ) || $activeCategory === 0 ) {
            return $this->response(false);
        }

        $prev_option = json_decode( Helper::getOption( "services_order", "[]" ), true );
        $new_data = [];

        foreach ( $categoryOrder as $category_id )
        {
            if ( (int)$category_id === $activeCategory )
            {
                $new_data[ $category_id ] = $servicesOrder;
            } else if ( isset( $prev_option[ $category_id ] ) )
            {
                $new_data[ $category_id ] = $prev_option[ $category_id ];
            } else
            {
                $services = Service::select( ['id'] )->where( "category_id", $category_id )->fetchAll();
                $serviceIds = array_map( function ( $item )
                {
                    return $item->id;
                } , $services );

                $new_data[ $category_id ] = $serviceIds;
            }
        }

        Helper::setOption("services_order", json_encode( $new_data ));

        return $this->response(true);
    }

    public function get_services_order() {
        $serviceCategory = Helper::_post( "id", "0", "int" );
        $servicesOrder   = json_decode( Helper::getOption( "services_order" ), true );
        $services        = [];

        if ( ! empty( $servicesOrder ) && is_array( $servicesOrder ) )
        {
            $allServices     = Helper::assocByKey( Service::where( "category_id", $serviceCategory )->fetchAll(), 'id' );
            $currentOrder = isset( $servicesOrder[ $serviceCategory ] ) ? $servicesOrder[ $serviceCategory ] : [];
            foreach ( $currentOrder as $value )
            {
                if ( isset( $allServices[ $value ] ) )
                {
                    $services[] = $allServices[ $value ];
                }
            }
        } else
        {
            $services = Service::where( "category_id", $serviceCategory )->fetchAll();
        }

        return $this->response( true, [
            "services" => $services
        ] );
    }

    public function reset_order()
    {
        Helper::deleteOption( 'services_order' );

        return $this->response( true, [
            'message' => bkntc__( 'Success' ),
        ] );
    }

    public function add_new_extra_category()
    {
        $name	= Helper::_post('name', '', 'string');

        if( empty( $name ) )
        {
            return $this->response(false);
        }

        $checkIfNameExist = ExtraCategory::where( 'name', $name )->fetch();

        if( $checkIfNameExist )
        {
            return $this->response(false, bkntc__('This category is already exist! Please choose an other name.'));
        }

        ExtraCategory::insert([
            'name'		=>	$name,
        ]);

        $id = DB::lastInsertedId();

        return $this->response(true, ['id' => $id]);
    }

    public function delete_extra_category()
    {
        $id = Helper::_post( 'id', 0, 'integer' );

        $extraCategory = ExtraCategory::where( 'id', $id ) -> fetch();

        if( ! $extraCategory ) return $this -> response( false, bkntc__( 'Extra category not found.' ) );

        $services = ServiceExtra::where( 'category_id', $id ) -> fetch();

        if( $services ) return $this -> response( false, bkntc__( 'Firstly delete extra services.' ) );

        ExtraCategory::where( 'id', $id ) -> delete();

        return $this -> response( true );
    }

    public function get_extra_for_create_modal()
    {
        return $this->response(true, [
            'image' => Helper::profileImage('', 'Services'),
            'extra_categories' => ExtraCategory::select([ 'id', 'name' ])->fetchAll()
        ]);
    }

    public function delete_extras()
    {
        $extrasStr = Helper::_post( 'extras', '', 'string' );

        if ( ! $extrasStr )
            return $this->response( true );

        $extrasArr = json_decode( $extrasStr, true );

        if ( ! $extrasArr )
            return $this->response( true );

        ServiceExtra::where( 'id', $extrasArr )
            ->where( 'service_id', 'is', null )
            ->delete();

        return $this->response( true );
    }
}
