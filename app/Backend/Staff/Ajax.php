<?php

namespace BookneticApp\Backend\Staff;

use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\Collection;
use BookneticApp\Models\Holiday;
use BookneticApp\Models\SpecialDay;
use BookneticApp\Models\Timesheet;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\Core\Permission;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function add_new()
	{
		$cid = Helper::_post('id', '0', 'integer');

		$selectedServices = [];
		if( $cid > 0 )
		{
			Capabilities::must( 'staff_edit' );

			$staffInfo = Staff::get( $cid );
			if( !$staffInfo )
			{
				return $this->response(false, bkntc__('Staff not found!'));
			}

			$getSelectedServices = ServiceStaff::where('staff_id', $cid)->fetchAll();
			foreach ( $getSelectedServices AS $selected_service )
			{
				$selectedServices[] = (string)$selected_service->service_id;
			}
		}
		else
		{
			Capabilities::must( 'staff_add' );
			$allowedLimit = Capabilities::getLimit( 'staff_allowed_max_number' );

			if( $allowedLimit > -1 && Staff::count() >= $allowedLimit )
			{
				$view = Helper::renderView('Base.view.modal.permission_denied', [
					'text' => bkntc__('You can\'t add more than %d Staff. Please upgrade your plan to add more Staff.', [ $allowedLimit ] )
				]);

				return $this->response( true, [ 'html' => $view ] );
			}

			$staffInfo = new Collection();
		}

		if( !( Permission::isAdministrator() || Capabilities::userCan('staff_add') ) && ($cid == 0 || !in_array( $cid, Permission::myStaffId() )) )
		{
			return $this->response(false, bkntc__('You do not have sufficient permissions to perform this action'));
		}

		$timesheet = DB::DB()->get_row(
			DB::DB()->prepare( 'SELECT staff_id, timesheet FROM '.DB::table('timesheet').' WHERE ((service_id IS NULL AND staff_id IS NULL) OR (staff_id=%d)) '.DB::tenantFilter().' ORDER BY staff_id DESC LIMIT 0,1', [ $cid ] ),
			ARRAY_A
		);

		$specialDays = SpecialDay::where('staff_id', $cid)->fetchAll();
		$holidays = Holiday::where('staff_id', $cid)->fetchAll();

		$holidaysArr = [];
		foreach( $holidays AS $holiday )
		{
			$holidaysArr[ Date::dateSQL( $holiday['date'] ) ] = $holiday['id'];
		}

		$locations  = Location::fetchAll();
		$services   = Service::fetchAll();

		$users = DB::DB()->get_results('SELECT * FROM `'.DB::DB()->base_prefix.'users`', ARRAY_A);

        TabUI::get( 'staff_add' )
             ->item( 'details' )
             ->setTitle( bkntc__( 'STAFF DETAILS' ) )
             ->addView( __DIR__ . '/view/tab/details.php', [], 1 )
             ->setPriority( 1 );

        TabUI::get( 'staff_add' )
             ->item( 'timesheet' )
             ->setTitle( bkntc__( 'WEEKLY SCHEDULE' ) )
             ->addView( __DIR__ . '/view/tab/timesheet.php', [], 1 )
             ->setPriority( 2 );

        TabUI::get( 'staff_add' )
             ->item( 'special_days' )
             ->setTitle( bkntc__( 'SPECIAL DAYS' ) )
             ->addView( __DIR__ . '/view/tab/special_days.php', [], 1 )
             ->setPriority( 3 );

        TabUI::get( 'staff_add' )
             ->item( 'holidays' )
             ->setTitle( bkntc__( 'HOLIDAYS' ) )
             ->addView( __DIR__ . '/view/tab/holidays.php', [], 1 )
             ->setPriority( 4 );

        $timeS = empty($timesheet['timesheet']) ? [
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]],
            ["day_off" => 0, "start" => "00:00", "end" => "24:00", "breaks" =>[]] ] : json_decode($timesheet['timesheet'], true);

        $data = [
            'users'                     => $users,
            'locations'                 => $locations,
            'services'                  => $services,
            'selected_services'         => $selectedServices,
            'id'                        => $cid,
            'staff'                     => $staffInfo,
            'special_days'              => $specialDays,
            'timesheet'                 => $timeS,
            'has_specific_timesheet'    => ! empty($timesheet['staff_id']) && $timesheet['staff_id'] > 0,
            'holidays'                  => json_encode( $holidaysArr ),
        ];

		return $this->modalView( 'add_new', $data );
	}

	public function save_staff()
	{
		$id						= Helper::_post('id', '0', 'integer');

		if( $id > 0 )
		{
			Capabilities::must( 'staff_edit' );
		}
		else
		{
			Capabilities::must( 'staff_add' );
		}

		$wp_user				= Helper::_post('wp_user', '0', 'integer');
		$name					= Helper::_post('name', '', 'string');
		$profession				= Helper::_post('profession', '', 'string');
		$phone					= Helper::_post('phone', '', 'string');
		$email					= Helper::_post('email', '', 'email');
		$allow_staff_to_login	= Helper::_post('allow_staff_to_login', '0', 'int', ['0', '1']);
		$wp_user_use_existing	= Helper::_post('wp_user_use_existing', 'yes', 'string', ['yes', 'no']);
		$wp_user_password		= Helper::_post('wp_user_password', '', 'string');
        $update_wp_user         = Helper::_post( 'update_wp_user', '0', 'int', ['0', '1']);
		$note					= Helper::_post('note', '', 'string');
		$locations				= Helper::_post('locations', '', 'string');
		$services				= Helper::_post('services', '', 'string');

		$weekly_schedule	=	Helper::_post('weekly_schedule', '', 'string');
		$special_days		=	Helper::_post('special_days', '', 'string');
		$holidays			=	Helper::_post('holidays', '', 'string');

		if( empty($name) || empty($email) )
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

		$isEdit = $id > 0;

		if( $isEdit )
		{
			$getOldInf = Staff::get( $id );
			if( !$getOldInf )
			{
				return $this->response(false, bkntc__('Staff not found or permission denied!'));
			}
		}
		else if( !( Permission::isAdministrator() || Capabilities::userCan('staff_add') ) && !in_array( $id, Permission::myStaffId() ) )
		{
			return $this->response(false, bkntc__('You do not have sufficient permissions to perform this action'));
		}

		if( !$isEdit )
		{
			$allowedLimit = Capabilities::getLimit( 'staff_allowed_max_number' );

			if( $allowedLimit > -1 && Staff::count() >= $allowedLimit )
			{
				return $this->response( false, bkntc__('You can\'t add more than %d Staff. Please upgrade your plan to add more Staff.', [ $allowedLimit ] ) );
			}
		}

        if (!Capabilities::tenantCan('locations'))
        {
            $locations = $id > 0 ? Staff::get($id)->locations : Location::limit(1)->fetch()->id;
        }

		$locations = explode(',', $locations);
		$locationsArr = [];
		foreach ( $locations AS $location )
		{
			if( is_numeric($location) && $location > 0 )
			{
				$locationsArr[] = (int)$location;
			}
		}

		if( empty($locationsArr) )
		{
			return $this->response(false, bkntc__('Please select location!'));
		}

		$services = explode(',', $services);
		$servicesArr = [];
		foreach ( $services AS $service )
		{
			if( is_numeric($service) && $service > 0 )
			{
				$servicesArr[] = (int)$service;
			}
		}

		// check weekly schedule array
		if( empty( $weekly_schedule ) )
		{
			return $this->response(false, bkntc__('Please fill the weekly schedule correctly!'));
		}
		$weekly_schedule = json_decode( $weekly_schedule, true );

		// check weekly schedule array
		if( !empty( $weekly_schedule ) && is_array( $weekly_schedule ) && count( $weekly_schedule ) == 7 )
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

				$ws_day_off	= $dayInfo['day_off'];
                $time_end = $dayInfo['end'] == "24:00" ? "24:00" : Date::timeSQL( $dayInfo['end'] );
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

		if( $allow_staff_to_login == 1 )
		{
			if( $wp_user_use_existing == 'yes' )
			{
                if ( !( $wp_user > 0 )  )
                {
                    return $this->response( false, bkntc__('Please select WordPress user!') );
                }

                if ( $isEdit && $update_wp_user )
                {
                    $user_data = wp_update_user( [
                        'user_email'   => $email,
                        'display_name' => $name,
                        'first_name'   => $name,
                        'ID'           => $wp_user
                    ] );

                    if( is_wp_error( $user_data ) )
                    {
                        return $this->response( false, $user_data->get_error_message() );
                    }

                    DB::DB()->update( DB::DB()->users, ['user_login' => $email], ['ID' => $wp_user] );
                }
			}
			else if( $wp_user_use_existing == 'no' )
			{
				$emailExists = email_exists( $email );
				$userNameExists = username_exists( $email );
				$wpUserExists = $emailExists !== false || $userNameExists !== false;

				if( !($isEdit && $getOldInf->user_id > 0) && empty( $wp_user_password ) )
				{
					return $this->response( false, bkntc__('Please type the password of the WordPress user!') );
				}
				else if( (!$isEdit || $email != $getOldInf->email) && $wpUserExists )
				{
					return $this->response( false, bkntc__('The WordPress user with the same email address already exists!') );
				}

				if( $wpUserExists )
				{
					$wp_user = empty( $emailExists ) ? $userNameExists : $emailExists;
					$userToBeUpdated = get_userdata( $wp_user );
					$isUserLoginEmail = filter_var( $userToBeUpdated->user_login, FILTER_VALIDATE_EMAIL );

					$userUpdateInfo = [
						'ID'            =>  $wp_user,
						'user_email'	=>	$email,
						'display_name'	=>	$name,
						'first_name'	=>	$name
					];

					if( $isUserLoginEmail )
						$userUpdateInfo[ 'user_login' ] = $email;

					if( !empty( $wp_user_password ) )
					{
						$userUpdateInfo[ 'user_pass' ] = $wp_user_password;
					}

					$wp_user = wp_update_user( $userUpdateInfo );
				}
				else
				{
					$wp_user = wp_insert_user( [
						'user_login'	=>	$email,
						'user_email'	=>	$email,
						'display_name'	=>	$name,
						'first_name'	=>	$name,
						'last_name'		=>	'',
						'role'			=>	'booknetic_staff',
						'user_pass'		=>	$wp_user_password
					] );
				}

                if( is_wp_error( $wp_user ) )
                {
                    return $this->response( false, $wp_user->get_error_message() );
                }
			}
		}
		else
		{
			if( $isEdit && $getOldInf->user_id > 0 )
			{
				$userData = get_userdata( $getOldInf->user_id );
				if( $userData && in_array( 'booknetic_staff', $userData->roles ) )
				{
					require_once ABSPATH.'wp-admin/includes/user.php';
					wp_delete_user( $getOldInf->user_id );
				}
			}

			$wp_user = 0;
		}

		$profile_image = '';

		if( isset($_FILES['image']) && is_string($_FILES['image']['tmp_name']) )
		{
			$path_info = pathinfo($_FILES["image"]["name"]);
			$extension = strtolower( $path_info['extension'] );

			if( !in_array( $extension, ['jpg', 'jpeg', 'png'] ) )
			{
				return $this->response(false, bkntc__('Only JPG and PNG images allowed!'));
			}

			$profile_image = md5( base64_encode(rand(1,9999999) . microtime(true)) ) . '.' . $extension;
			$file_name = Helper::uploadedFile( $profile_image, 'Staff' );

			move_uploaded_file( $_FILES['image']['tmp_name'], $file_name );
		}

		$sqlData = [
			'user_id'		=>	$wp_user,
			'name'			=>	$name,
			'profession'	=>	$profession,
			'phone_number'	=>	$phone,
			'email'			=>	$isEdit && $getOldInf->user_id > 0 && !Permission::isAdministrator() ? $getOldInf->email : $email,
			'about'			=>	$note,
			'profile_image'	=>	$profile_image,
			'locations'		=>	implode(',', $locationsArr)
		];

		$sqlData = apply_filters( 'staff_sql_data', $sqlData );

		if( $isEdit )
		{
			if( empty( $profile_image ) )
			{
				unset( $sqlData['profile_image'] );
			}
			else
			{
				if( !empty( $getOldInf['profile_image'] ) )
				{
					$filePath = Helper::uploadedFile( $getOldInf['profile_image'], 'Staff' );

					if( is_file( $filePath ) && is_writable( $filePath ) )
					{
						unlink( $filePath );
					}
				}
			}


			Staff::where('id', $id)->update( $sqlData );

			Timesheet::where('staff_id', $id)->delete();
			$serviceStaff = ServiceStaff::where('staff_id', $id)->fetchAll();
            ServiceStaff::where('staff_id',$id)->delete();
		}
		else
		{
			$sqlData['is_active'] = 1;

			Staff::insert( $sqlData );
			$id = DB::lastInsertedId();
		}

		foreach ( $servicesArr AS $serviceId )
		{
            $serviceStaffData = [
                'staff_id'      =>  $id,
                'service_id'    =>  $serviceId,
                'price'			=>	Math::floor(-1),
                'deposit'		=>	Math::floor(-1),
                'deposit_type'	=>	'percent'
            ];

            if( isset($serviceStaff) )
            {
                foreach ($serviceStaff as $row)
                {
                    if($row->service_id == $serviceId && $row->staff_id==$id)
                    {
                        unset($row['id']);
                        $serviceStaffData = $row->toArray();
                    }
                }
            }

			ServiceStaff::insert($serviceStaffData);
		}

		if( isset( $newWeeklySchedule ) )
		{
			Timesheet::insert([
				'timesheet'		=>	json_encode( $newWeeklySchedule ),
				'staff_id'		=>	$id
			]);
		}

		$special_days = json_decode( $special_days, true );
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
			$sp_start	= Date::timeSQL( $special_day['start'] );
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
					$sp_breaks_new[] = [ Date::timeSQL( $sp_break[0] ) , $sp_break[1] == '24:00' ? '24:00' : Date::timeSQL( $sp_break[1] ) ];
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
				SpecialDay::where('id', $sp_id)->where('staff_id', $id)->update([
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
					'staff_id'		=>	$id
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

			DB::DB()->query( DB::DB()->prepare( 'DELETE FROM `' . DB::table('special_days') . '` WHERE staff_id=%d ' . $queryWhere, [$id] ) );
		}

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
				Holiday::insert([
					'date'		=>	$holidayDate,
					'staff_id'	=>	$id
				]);

				$saveHolidaysId[] = DB::lastInsertedId();
			}
			else
			{
				$saveHolidaysId[] = $holidayId;
			}
		}

		if( $isEdit )
		{
			$queryWhere = '';
			if( !empty( $saveHolidaysId ) )
			{
				$queryWhere = " AND id NOT IN ('" . implode( "', '", $saveHolidaysId ) . "')";
			}

			DB::DB()->query( DB::DB()->prepare( 'DELETE FROM `' . DB::table('holidays') . '` WHERE staff_id=%d ' . $queryWhere, [$id] ) );
		}

        Staff::handleTranslation( $id );

		return $this->response(true, [
            'is_edit' => $isEdit,
            'staff_id' => $id
        ] );
	}

	public function hide_staff()
	{
		Capabilities::must( 'staff_edit' );

		$staff_id	= Helper::_post('staff_id', '', 'int');

		if( !( $staff_id > 0 ) )
		{
			return $this->response(false);
		}

		$staff = Staff::get( $staff_id );

		if( !$staff )
		{
			return $this->response( false );
		}

		$new_status = $staff['is_active'] == 1 ? 0 : 1;

		Staff::where('id', $staff_id)->update([ 'is_active' => $new_status ]);

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
